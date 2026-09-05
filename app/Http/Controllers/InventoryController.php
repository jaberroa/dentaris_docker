<?php

namespace App\Http\Controllers;

use App\Data\InventoryMovementData;
use App\Exports\InventoryExport;
use App\Http\Requests\Inventory\CreateInventoryAdjustmentRequest;
use App\Http\Requests\Inventory\ExportInventoryRequest;
use App\Http\Requests\Inventory\TransferInventoryRequest;
use App\Models\Inventory;
use App\Models\InventoryLocation;
use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\Supplier;
use App\Modules\Clinics\Data\ClinicContext;
use App\Repositories\InventoryExportRepository;
use App\Repositories\InventoryMovementRepository;
use App\Services\InventoryMovementService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;
use Maatwebsite\Excel\Facades\Excel;
use RuntimeException;

class InventoryController extends Controller
{
    public function __construct()
    {
        // Middleware se aplica en las rutas, no en el constructor
    }

    public function index(Request $request)
    {
        $context = $this->clinicContext($request);
        $query = Inventory::forClinic($context)
            ->whereHas('product', fn ($query) => $query->forClinic($context))
            ->with([
                'product' => fn ($query) => $query->forClinic($context),
                'product.primarySupplier' => fn ($query) => $query->forClinic($context),
                'inventoryLocation',
            ]);

        // Filtros
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('product', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('product_code', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category')) {
            $query->whereHas('product', function ($q) use ($request) {
                $q->where('category', $request->category);
            });
        }

        if ($request->filled('stock_level')) {
            switch ($request->stock_level) {
                case 'low':
                    $query->whereRaw('available_stock <= (SELECT minimum_stock FROM products WHERE products.id = inventory.product_id)');
                    break;
                case 'out':
                    $query->where('available_stock', 0);
                    break;
                case 'normal':
                    $query->whereRaw('available_stock > (SELECT minimum_stock FROM products WHERE products.id = inventory.product_id)')
                        ->where('available_stock', '>', 0);
                    break;
            }
        }

        $sortBy = $request->get('sort_by', 'product');
        $sortOrder = $request->get('sort_order', 'asc');
        $sortOrder = in_array($sortOrder, ['asc', 'desc'], true) ? $sortOrder : 'asc';
        $sortBy = in_array($sortBy, ['product', 'category', 'location', 'stock', 'value'], true) ? $sortBy : 'product';
        $query->select('inventory.*')->join('products', 'inventory.product_id', '=', 'products.id')->leftJoin('inventory_locations', 'inventory.inventory_location_id', '=', 'inventory_locations.id');
        match ($sortBy) {
            'category' => $query->orderBy('products.category', $sortOrder),
            'location' => $query->orderByRaw('COALESCE(inventory_locations.name, inventory.location) '.$sortOrder),
            'stock' => $query->orderBy('inventory.available_stock', $sortOrder),
            'value' => $query->orderByRaw('inventory.current_stock * inventory.average_cost '.$sortOrder),
            default => $query->orderBy('products.name', $sortOrder),
        };
        $perPage = $request->get('per_page', 10);
        $perPage = in_array((string) $perPage, ['10', '25', '50', '100'], true) ? (int) $perPage : 10;
        $inventories = $query->paginate($perPage)->withQueryString();

        // Datos para filtros
        $categories = Product::query()
            ->forClinic($context)
            ->whereHas('inventories', fn ($query) => $query->forClinic($context))
            ->select('category')
            ->distinct()
            ->pluck('category');
        $suppliers = Supplier::forClinic($context)->where('is_active', true)->get();
        $transferDestinationsByProduct = Inventory::query()
            ->forClinic($context)
            ->with(['product:id,name', 'inventoryLocation:id,name'])
            ->orderBy('product_id')
            ->orderBy('location')
            ->get()
            ->groupBy('product_id');
        $activeInventoryLocations = InventoryLocation::query()
            ->forClinic($context)
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'code']);
        $exportLocations = InventoryLocation::query()->forClinic($context)->orderBy('name')->get(['id', 'name', 'code']);

        return view('inventory.index', compact('inventories', 'categories', 'suppliers', 'transferDestinationsByProduct', 'activeInventoryLocations', 'exportLocations', 'perPage', 'sortBy', 'sortOrder'));
    }

    public function show(Request $request, Inventory $inventory)
    {
        $context = $this->clinicContext($request);
        $inventory = $this->inventoryForContext($inventory, $context);
        $inventory->load([
            'product' => fn ($query) => $query->forClinic($context),
            'product.primarySupplier' => fn ($query) => $query->forClinic($context),
        ]);
        $movements = $inventory->movements()
            ->forClinic($context)
            ->with('user')
            ->latest()
            ->paginate(15);

        $reversedMovementIds = InventoryMovement::query()
            ->forClinic($context)
            ->where('reference_type', InventoryMovement::class)
            ->whereIn('reference_id', $movements->pluck('id'))
            ->pluck('reference_id')
            ->all();

        return view('inventory.show', compact('inventory', 'movements', 'reversedMovementIds'));
    }

    public function movements(Request $request, InventoryMovementRepository $movementRepository)
    {
        $context = $this->clinicContext($request);
        $query = $movementRepository->query($context)->latest();

        if (request()->filled('type')) {
            $query->where('type', request('type'));
        }

        $movements = $query->paginate(20)->withQueryString();
        $reversedMovementIds = InventoryMovement::query()
            ->forClinic($context)
            ->where('reference_type', InventoryMovement::class)
            ->whereIn('reference_id', $movements->pluck('id'))
            ->pluck('reference_id')
            ->all();

        return view('inventory.movements', compact('movements', 'reversedMovementIds'));
    }

    public function update(Request $request, Inventory $inventory)
    {
        $context = $this->clinicContext($request);
        $inventory = $this->inventoryForContext($inventory, $context);
        $request->validate([
            'current_stock' => 'required|integer|min:0',
            'reserved_stock' => 'required|integer|min:0',
            'location' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $inventory->update([
            'current_stock' => $request->current_stock,
            'reserved_stock' => $request->reserved_stock,
            'available_stock' => $request->current_stock - $request->reserved_stock,
            'location' => $request->location,
            'notes' => $request->notes,
        ]);

        $inventory->updateAlerts();

        return redirect()->route('inventory.show', $inventory)
            ->with('success', 'Inventario actualizado correctamente');
    }

    public function adjust(
        CreateInventoryAdjustmentRequest $request,
        Inventory $inventory,
        InventoryMovementService $movementService,
    ) {
        $context = $request->clinicContext();
        abort_unless($context instanceof ClinicContext, 403, 'El contexto clínico no está disponible.');
        $inventory = $this->inventoryForContext($inventory, $context);
        $data = $request->validated();
        $movement = $movementService->adjust(
            new InventoryMovementData(
                $inventory->id,
                $inventory->product_id,
                $data['type'],
                (int) $data['quantity'],
                $data['reason'],
            ),
            $request->user(),
            $context,
        );

        if ($request->expectsJson()) {
            return response()->json(['data' => $movement], 201);
        }

        return redirect()->route('inventory.show', $movement->inventory_id)
            ->with('success', 'Movimiento de inventario registrado correctamente');
    }

    public function reverseMovement(InventoryMovement $movement, InventoryMovementService $movementService)
    {
        $context = $this->clinicContext(request());
        $movement = $this->movementForContext($movement, $context);
        $reversal = $movementService->reverse($movement, request()->user(), $context);

        return redirect()->route('inventory.show', $reversal->inventory_id)
            ->with('success', 'Movimiento revertido correctamente.');
    }

    public function transfer(TransferInventoryRequest $request, InventoryMovementService $movementService)
    {
        $context = $request->clinicContext();
        abort_unless($context instanceof ClinicContext, 403, 'El contexto clínico no está disponible.');
        $data = $request->validated();

        try {
            $transfer = $movementService->transfer(
                (int) $data['inventory_id'],
                (int) $data['destination_inventory_id'],
                (int) $data['quantity'],
                $data['reason'],
                $request->user(),
                $context,
            );
        } catch (InvalidArgumentException|RuntimeException $exception) {
            throw ValidationException::withMessages(['quantity' => $exception->getMessage()]);
        }

        if ($request->expectsJson()) {
            return response()->json(['data' => $transfer], 201);
        }

        return redirect()->route('inventory.show', $transfer['outgoing']->inventory_id)
            ->with('success', 'Transferencia registrada correctamente.');
    }

    public function export(ExportInventoryRequest $request, InventoryExportRepository $exportRepository)
    {
        $context = $request->clinicContext();
        abort_unless($context instanceof ClinicContext, 403, 'El contexto clínico no está disponible.');
        $filters = $request->validated();
        $limit = $filters['limit'] ?? 10000;
        $query = $exportRepository->query($filters, $context)->orderBy('id')->limit($limit);
        $rows = (clone $query)->count();
        $format = $filters['format'] ?? 'csv';

        activity('inventory')
            ->causedBy($request->user())
            ->withProperties(['clinic_id' => $context->clinicId, 'filters' => $filters, 'rows' => $rows, 'format' => $format])
            ->log('inventory.exported');

        $filename = 'inventario_'.now()->format('Y-m-d_H-i-s');
        if ($format === 'xlsx') {
            return Excel::download(new InventoryExport($filters, $context), $filename.'.xlsx');
        }
        if ($format === 'pdf') {
            return Pdf::loadView('inventory.export-pdf', ['inventories' => $query->get(), 'filters' => $filters])->setPaper('A4', 'landscape')->download($filename.'.pdf');
        }

        return response()->streamDownload(function () use ($query): void {
            $output = fopen('php://output', 'w');
            fwrite($output, "\xEF\xBB\xBF");
            fputcsv($output, ['Código', 'Producto', 'Categoría', 'Ubicación', 'Stock actual', 'Reservado', 'Disponible', 'Costo promedio', 'Valor total', 'Estado'], ';');
            $query->chunkById(250, function ($inventories) use ($output): void {
                foreach ($inventories as $inventory) {
                    $available = (int) $inventory->available_stock;
                    $minimum = (int) ($inventory->product->minimum_stock ?? 0);
                    fputcsv($output, [
                        $inventory->product->product_code ?? '', $inventory->product->name ?? '', $inventory->product->category ?? '',
                        $inventory->inventoryLocation->name ?? $inventory->location ?? 'Sin asignar', $inventory->current_stock, $inventory->reserved_stock,
                        $available, number_format((float) $inventory->average_cost, 2, '.', ''), number_format($inventory->current_stock * $inventory->average_cost, 2, '.', ''),
                        $available <= 0 ? 'Agotado' : ($available <= $minimum ? 'Stock bajo' : 'Disponible'),
                    ], ';');
                }
            });
            fclose($output);
        }, $filename.'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function lowStock(Request $request)
    {
        $context = $this->clinicContext($request);
        $lowStockProducts = Inventory::forClinic($context)->with([
            'product' => fn ($query) => $query->forClinic($context),
            'product.primarySupplier' => fn ($query) => $query->forClinic($context),
        ])
            ->whereHas('product', function ($query) use ($context) {
                $query->forClinic($context)->where('is_active', true);
            })
            ->whereRaw('available_stock <= (SELECT minimum_stock FROM products WHERE products.id = inventory.product_id)')
            ->orderByRaw('available_stock - (SELECT minimum_stock FROM products WHERE products.id = inventory.product_id)')
            ->paginate(20);

        return view('inventory.low-stock', compact('lowStockProducts'));
    }

    public function outOfStock(Request $request)
    {
        $context = $this->clinicContext($request);
        $outOfStockProducts = Inventory::forClinic($context)->with([
            'product' => fn ($query) => $query->forClinic($context),
            'product.primarySupplier' => fn ($query) => $query->forClinic($context),
        ])
            ->whereHas('product', function ($query) use ($context) {
                $query->forClinic($context)->where('is_active', true);
            })
            ->where('available_stock', 0)
            ->orderBy('product.name')
            ->paginate(20);

        return view('inventory.out-of-stock', compact('outOfStockProducts'));
    }

    public function expiringSoon(Request $request)
    {
        $context = $this->clinicContext($request);
        $expiringProducts = Product::forClinic($context)->with([
            'inventories' => fn ($query) => $query->forClinic($context),
            'primarySupplier' => fn ($query) => $query->forClinic($context),
        ])
            ->whereHas('inventories', fn ($query) => $query->forClinic($context))
            ->where('is_active', true)
            ->where('expiry_date', '<=', now()->addDays(30))
            ->where('expiry_date', '>', now())
            ->orderBy('expiry_date')
            ->paginate(20);

        return view('inventory.expiring-soon', compact('expiringProducts'));
    }

    public function report(Request $request)
    {
        $context = $this->clinicContext($request);
        $stats = [
            'total_products' => Product::forClinic($context)->where('is_active', true)
                ->whereHas('inventories', fn ($query) => $query->forClinic($context))
                ->count(),
            'low_stock_count' => Inventory::forClinic($context)->with('product')
                ->whereHas('product', function ($query) use ($context) {
                    $query->forClinic($context)->where('is_active', true);
                })
                ->whereRaw('available_stock <= (SELECT minimum_stock FROM products WHERE products.id = inventory.product_id)')
                ->count(),
            'out_of_stock_count' => Inventory::forClinic($context)->where('available_stock', 0)->count(),
            'total_value' => Inventory::forClinic($context)->join('products', 'inventory.product_id', '=', 'products.id')
                ->where('products.clinic_id', $context->clinicId)
                ->where('products.is_active', true)
                ->sum(DB::raw('current_stock * average_cost')),
        ];

        return view('inventory.report', compact('stats'));
    }

    private function clinicContext(Request $request): ClinicContext
    {
        $context = $request->attributes->get(ClinicContext::class)
            ?? $request->attributes->get('clinic.context');

        abort_unless($context instanceof ClinicContext, 403, 'El contexto clínico no está disponible.');

        return $context;
    }

    private function inventoryForContext(Inventory $inventory, ClinicContext $context): Inventory
    {
        abort_unless($inventory->clinic_id !== null && (int) $inventory->clinic_id === $context->clinicId, 404);

        return $inventory;
    }

    private function movementForContext(InventoryMovement $movement, ClinicContext $context): InventoryMovement
    {
        abort_unless($movement->clinic_id !== null && (int) $movement->clinic_id === $context->clinicId, 404);

        return $movement;
    }
}
