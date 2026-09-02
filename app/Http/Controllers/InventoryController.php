<?php

namespace App\Http\Controllers;

use App\Data\InventoryMovementData;
use App\Http\Requests\Inventory\CreateInventoryAdjustmentRequest;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\Supplier;
use App\Services\InventoryMovementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryController extends Controller
{
    public function __construct()
    {
        // Middleware se aplica en las rutas, no en el constructor
    }

    public function index(Request $request)
    {
        $query = Inventory::with(['product.primarySupplier']);

        // Filtros
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('product', function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('product_code', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category')) {
            $query->whereHas('product', function($q) use ($request) {
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

        // Ordenamiento
        $sortBy = $request->get('sort_by', 'product.name');
        $sortOrder = $request->get('sort_order', 'asc');

        switch ($sortBy) {
            case 'stock':
                $query->orderBy('available_stock', $sortOrder);
                break;
            case 'value':
                $query->orderByRaw('current_stock * average_cost ' . $sortOrder);
                break;
            default:
                $query->join('products', 'inventory.product_id', '=', 'products.id')
                      ->orderBy('products.name', $sortOrder);
        }

        $inventories = $query->paginate(20);

        // Datos para filtros
        $categories = Product::select('category')->distinct()->pluck('category');
        $suppliers = Supplier::where('is_active', true)->get();

        return view('inventory.index', compact('inventories', 'categories', 'suppliers'));
    }

    public function show(Inventory $inventory)
    {
        $inventory->load(['product.primarySupplier']);
        return view('inventory.show', compact('inventory'));
    }

    public function update(Request $request, Inventory $inventory)
    {
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

    public function adjust(CreateInventoryAdjustmentRequest $request, InventoryMovementService $movementService)
    {
        $data = $request->validated();
        $movement = $movementService->adjust(
            new InventoryMovementData(
                (int) $data['inventory_id'],
                (int) $data['product_id'],
                $data['type'],
                (int) $data['quantity'],
                $data['reason'],
            ),
            $request->user(),
        );

        if ($request->expectsJson()) {
            return response()->json(['data' => $movement], 201);
        }

        return redirect()->route('inventory.show', $movement->inventory_id)
            ->with('success', 'Movimiento de inventario registrado correctamente');
    }

    public function lowStock()
    {
        $lowStockProducts = Inventory::with(['product.primarySupplier'])
            ->whereHas('product', function($query) {
                $query->where('is_active', true);
            })
            ->whereRaw('available_stock <= (SELECT minimum_stock FROM products WHERE products.id = inventory.product_id)')
            ->orderByRaw('available_stock - (SELECT minimum_stock FROM products WHERE products.id = inventory.product_id)')
            ->paginate(20);

        return view('inventory.low-stock', compact('lowStockProducts'));
    }

    public function outOfStock()
    {
        $outOfStockProducts = Inventory::with(['product.primarySupplier'])
            ->whereHas('product', function($query) {
                $query->where('is_active', true);
            })
            ->where('available_stock', 0)
            ->orderBy('product.name')
            ->paginate(20);

        return view('inventory.out-of-stock', compact('outOfStockProducts'));
    }

    public function expiringSoon()
    {
        $expiringProducts = Product::with(['inventory', 'primarySupplier'])
            ->where('is_active', true)
            ->where('expiry_date', '<=', now()->addDays(30))
            ->where('expiry_date', '>', now())
            ->orderBy('expiry_date')
            ->paginate(20);

        return view('inventory.expiring-soon', compact('expiringProducts'));
    }

    public function report()
    {
        $stats = [
            'total_products' => Product::where('is_active', true)->count(),
            'low_stock_count' => Inventory::with('product')
                ->whereHas('product', function($query) {
                    $query->where('is_active', true);
                })
                ->whereRaw('available_stock <= (SELECT minimum_stock FROM products WHERE products.id = inventory.product_id)')
                ->count(),
            'out_of_stock_count' => Inventory::where('available_stock', 0)->count(),
            'total_value' => Inventory::join('products', 'inventory.product_id', '=', 'products.id')
                ->where('products.is_active', true)
                ->sum(DB::raw('current_stock * average_cost')),
        ];

        return view('inventory.report', compact('stats'));
    }
}
