<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Supplier;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseController extends Controller
{
    public function __construct()
    {
        // Middleware se aplica en las rutas, no en el constructor
    }

    public function index(Request $request)
    {
        $query = Purchase::with(['supplier', 'items.product', 'creator']);

        // Filtros
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('purchase_number', 'like', "%{$search}%")
                  ->orWhere('invoice_number', 'like', "%{$search}%")
                  ->orWhereHas('supplier', function($subQ) use ($search) {
                      $subQ->where('company_name', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        if ($request->filled('date_from')) {
            $query->where('purchase_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('purchase_date', '<=', $request->date_to);
        }

        // Ordenamiento
        $sortBy = $request->get('sort_by', 'purchase_date');
        $sortOrder = $request->get('sort_order', 'desc');

        switch ($sortBy) {
            case 'total_amount':
                $query->orderBy('total_amount', $sortOrder);
                break;
            case 'supplier':
                $query->join('suppliers', 'purchases.supplier_id', '=', 'suppliers.id')
                      ->orderBy('suppliers.company_name', $sortOrder);
                break;
            default:
                $query->orderBy('purchase_date', $sortOrder);
        }

        $purchases = $query->paginate(20);

        // Datos para filtros
        $statuses = Purchase::getStatusOptions();
        $suppliers = Supplier::where('is_active', true)->get();

        return view('purchases.index', compact('purchases', 'statuses', 'suppliers'));
    }

    public function create()
    {
        $suppliers = Supplier::where('is_active', true)->orderBy('company_name')->get();
        $products = Product::where('is_active', true)->orderBy('name')->get();

        return view('purchases.create', compact('suppliers', 'products'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'purchase_date' => 'required|date',
            'expected_delivery' => 'required|date|after:purchase_date',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'shipping_cost' => 'nullable|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'invoice_number' => 'nullable|string|max:255',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity_ordered' => 'required|integer|min:1',
            'items.*.unit_cost' => 'required|numeric|min:0',
            'items.*.expiry_date' => 'nullable|date',
            'items.*.notes' => 'nullable|string',
        ]);

        DB::transaction(function() use ($request) {
            $purchase = Purchase::create([
                'purchase_number' => Purchase::generatePurchaseNumber(),
                'supplier_id' => $request->supplier_id,
                'purchase_date' => $request->purchase_date,
                'expected_delivery' => $request->expected_delivery,
                'status' => 'pending',
                'tax_rate' => $request->tax_rate ?: 0,
                'shipping_cost' => $request->shipping_cost ?: 0,
                'discount_amount' => $request->discount_amount ?: 0,
                'notes' => $request->notes,
                'invoice_number' => $request->invoice_number,
                'created_by' => auth()->id(),
            ]);

            // Crear items de la compra
            $subtotal = 0;
            foreach ($request->items as $itemData) {
                $totalCost = $itemData['quantity_ordered'] * $itemData['unit_cost'];
                $subtotal += $totalCost;

                PurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'product_id' => $itemData['product_id'],
                    'quantity_ordered' => $itemData['quantity_ordered'],
                    'unit_cost' => $itemData['unit_cost'],
                    'total_cost' => $totalCost,
                    'expiry_date' => $itemData['expiry_date'],
                    'notes' => $itemData['notes'],
                ]);
            }

            // Calcular totales
            $purchase->calculateTotals();
        });

        return redirect()->route('purchases.index')
            ->with('success', 'Orden de compra creada correctamente');
    }

    public function show(Purchase $purchase)
    {
        $purchase->load(['supplier', 'items.product', 'creator']);

        return view('purchases.show', compact('purchase'));
    }

    public function edit(Purchase $purchase)
    {
        if (!$purchase->canBeModified()) {
            return redirect()->route('purchases.show', $purchase)
                ->with('error', 'Esta orden no puede ser modificada');
        }

        $purchase->load(['items.product']);
        $suppliers = Supplier::where('is_active', true)->orderBy('company_name')->get();
        $products = Product::where('is_active', true)->orderBy('name')->get();

        return view('purchases.edit', compact('purchase', 'suppliers', 'products'));
    }

    public function update(Request $request, Purchase $purchase)
    {
        if (!$purchase->canBeModified()) {
            return back()->withErrors(['error' => 'Esta orden no puede ser modificada']);
        }

        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'purchase_date' => 'required|date',
            'expected_delivery' => 'required|date|after:purchase_date',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'shipping_cost' => 'nullable|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'invoice_number' => 'nullable|string|max:255',
        ]);

        $purchase->update([
            'supplier_id' => $request->supplier_id,
            'purchase_date' => $request->purchase_date,
            'expected_delivery' => $request->expected_delivery,
            'tax_rate' => $request->tax_rate ?: 0,
            'shipping_cost' => $request->shipping_cost ?: 0,
            'discount_amount' => $request->discount_amount ?: 0,
            'notes' => $request->notes,
            'invoice_number' => $request->invoice_number,
        ]);

        $purchase->calculateTotals();

        return redirect()->route('purchases.show', $purchase)
            ->with('success', 'Orden de compra actualizada correctamente');
    }

    public function destroy(Purchase $purchase)
    {
        if (!$purchase->canBeModified()) {
            return back()->withErrors(['error' => 'Esta orden no puede ser eliminada']);
        }

        $purchase->delete();

        return redirect()->route('purchases.index')
            ->with('success', 'Orden de compra eliminada correctamente');
    }

    public function addItem(Request $request, Purchase $purchase)
    {
        if (!$purchase->canBeModified()) {
            return back()->withErrors(['error' => 'No se pueden agregar items a esta orden']);
        }

        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity_ordered' => 'required|integer|min:1',
            'unit_cost' => 'required|numeric|min:0',
            'expiry_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        $purchase->addItem(
            $request->product_id,
            $request->quantity_ordered,
            $request->unit_cost,
            $request->expiry_date
        );

        $purchase->calculateTotals();

        return redirect()->route('purchases.show', $purchase)
            ->with('success', 'Item agregado correctamente');
    }

    public function removeItem(PurchaseItem $purchaseItem)
    {
        $purchase = $purchaseItem->purchase;
        
        if (!$purchase->canBeModified()) {
            return back()->withErrors(['error' => 'No se pueden modificar items de esta orden']);
        }

        $purchaseItem->delete();
        $purchase->calculateTotals();

        return redirect()->route('purchases.show', $purchase)
            ->with('success', 'Item eliminado correctamente');
    }

    public function markAsOrdered(Purchase $purchase)
    {
        if ($purchase->status !== 'pending') {
            return back()->withErrors(['error' => 'Solo se pueden ordenar compras pendientes']);
        }

        $purchase->markAsOrdered();

        return redirect()->route('purchases.show', $purchase)
            ->with('success', 'Orden marcada como enviada al proveedor');
    }

    public function receive(Request $request, Purchase $purchase)
    {
        if (!in_array($purchase->status, ['pending', 'ordered'])) {
            return back()->withErrors(['error' => 'Estado inválido para recibir']);
        }

        $request->validate([
            'actual_delivery' => 'required|date',
            'item_quantities' => 'required|array',
            'item_quantities.*' => 'required|integer|min:0',
            'notes' => 'nullable|string',
        ]);

        DB::transaction(function() use ($purchase, $request) {
            // Actualizar cantidades recibidas
            $purchase->updateItemQuantities($request->item_quantities);

            // Marcar como recibida
            $purchase->markAsReceived($request->actual_delivery);

            // Actualizar notas
            if ($request->notes) {
                $purchase->update([
                    'notes' => $purchase->notes . "\nRecibido: " . $request->notes
                ]);
            }
        });

        return redirect()->route('purchases.show', $purchase)
            ->with('success', 'Orden recibida correctamente');
    }

    public function cancel(Purchase $purchase, Request $request)
    {
        if (in_array($purchase->status, ['received', 'cancelled'])) {
            return back()->withErrors(['error' => 'No se puede cancelar esta orden']);
        }

        $request->validate([
            'reason' => 'required|string|max:255',
        ]);

        $purchase->markAsCancelled($request->reason);

        return redirect()->route('purchases.show', $purchase)
            ->with('success', 'Orden cancelada correctamente');
    }

    public function pending()
    {
        $purchases = Purchase::with(['supplier', 'creator'])
            ->where('status', 'pending')
            ->orderBy('purchase_date')
            ->paginate(20);

        return view('purchases.pending', compact('purchases'));
    }

    public function ordered()
    {
        $purchases = Purchase::with(['supplier', 'creator'])
            ->where('status', 'ordered')
            ->orderBy('expected_delivery')
            ->paginate(20);

        return view('purchases.ordered', compact('purchases'));
    }

    public function overdue()
    {
        $purchases = Purchase::with(['supplier', 'creator'])
            ->overdue()
            ->orderBy('expected_delivery')
            ->paginate(20);

        return view('purchases.overdue', compact('purchases'));
    }

    public function received()
    {
        $purchases = Purchase::with(['supplier', 'creator'])
            ->where('status', 'received')
            ->orderBy('actual_delivery', 'desc')
            ->paginate(20);

        return view('purchases.received', compact('purchases'));
    }

    public function report()
    {
        $stats = [
            'total_purchases' => Purchase::count(),
            'pending_purchases' => Purchase::where('status', 'pending')->count(),
            'ordered_purchases' => Purchase::where('status', 'ordered')->count(),
            'overdue_purchases' => Purchase::overdue()->count(),
            'total_value' => Purchase::sum('total_amount'),
            'monthly_purchases' => Purchase::thisMonth()->sum('total_amount'),
        ];

        $purchasesBySupplier = Supplier::withCount('purchases')
            ->withSum('purchases', 'total_amount')
            ->where('is_active', true)
            ->orderBy('purchases_sum_total_amount', 'desc')
            ->limit(10)
            ->get();

        $monthlyPurchases = Purchase::selectRaw('YEAR(purchase_date) as year, MONTH(purchase_date) as month, COUNT(*) as count, SUM(total_amount) as total')
            ->whereYear('purchase_date', now()->year)
            ->groupBy('year', 'month')
            ->orderBy('month')
            ->get();

        return view('purchases.report', compact('stats', 'purchasesBySupplier', 'monthlyPurchases'));
    }

    public function export(Request $request)
    {
        // TODO: Implementar exportación a Excel/CSV
        return response()->json(['message' => 'Función de exportación pendiente de implementar']);
    }
}
