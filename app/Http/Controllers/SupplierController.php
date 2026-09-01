<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use App\Models\Product;
use App\Models\Purchase;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SupplierController extends Controller
{
    public function __construct()
    {
        // Middleware se aplica en las rutas, no en el constructor
    }

    public function index(Request $request)
    {
        $query = Supplier::withCount(['products', 'purchases']);

        // Filtros
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('company_name', 'like', "%{$search}%")
                  ->orWhere('contact_name', 'like', "%{$search}%")
                  ->orWhere('supplier_code', 'like', "%{$search}%");
            });
        }

        if ($request->filled('city')) {
            $query->where('city', $request->city);
        }

        if ($request->filled('payment_terms')) {
            $query->where('payment_terms', $request->payment_terms);
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active);
        }

        // Ordenamiento
        $sortBy = $request->get('sort_by', 'company_name');
        $sortOrder = $request->get('sort_order', 'asc');

        switch ($sortBy) {
            case 'products_count':
                $query->orderBy('products_count', $sortOrder);
                break;
            case 'purchases_count':
                $query->orderBy('purchases_count', $sortOrder);
                break;
            case 'quality_rating':
                $query->orderBy('quality_rating', $sortOrder);
                break;
            default:
                $query->orderBy('company_name', $sortOrder);
        }

        $suppliers = $query->paginate(20);

        // Datos para filtros
        $cities = Supplier::select('city')->distinct()->whereNotNull('city')->pluck('city');
        $paymentTerms = Supplier::getPaymentTermsOptions();

        return view('suppliers.index', compact('suppliers', 'cities', 'paymentTerms'));
    }

    public function create()
    {
        $paymentTerms = Supplier::getPaymentTermsOptions();
        return view('suppliers.create', compact('paymentTerms'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'company_name' => 'required|string|max:255',
            'contact_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'address' => 'required|string|max:255',
            'city' => 'required|string|max:100',
            'state' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
            'tax_id' => 'nullable|string|max:50',
            'payment_terms' => 'required|string',
            'credit_limit' => 'nullable|numeric|min:0',
            'specialties' => 'nullable|array',
            'services' => 'nullable|array',
            'average_turnaround_days' => 'nullable|numeric|min:1',
            'quality_rating' => 'nullable|numeric|min:0|max:5',
            'notes' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $supplier = Supplier::create([
            'supplier_code' => $this->generateSupplierCode(),
            'company_name' => $request->company_name,
            'contact_name' => $request->contact_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
            'city' => $request->city,
            'state' => $request->state,
            'postal_code' => $request->postal_code,
            'country' => $request->country,
            'tax_id' => $request->tax_id,
            'payment_terms' => $request->payment_terms,
            'credit_limit' => $request->credit_limit,
            'specialties' => $request->specialties,
            'services' => $request->services,
            'average_turnaround_days' => $request->average_turnaround_days,
            'quality_rating' => $request->quality_rating,
            'notes' => $request->notes,
            'is_active' => $request->has('is_active'),
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('suppliers.show', $supplier)
            ->with('success', 'Proveedor creado correctamente');
    }

    public function show(Supplier $supplier)
    {
        $supplier->load(['products', 'purchases' => function($query) {
            $query->latest()->limit(10);
        }]);

        $stats = [
            'total_products' => $supplier->products()->count(),
            'total_purchases' => $supplier->purchases()->count(),
            'total_purchase_value' => $supplier->purchases()->sum('total_amount'),
            'average_purchase_value' => $supplier->purchases()->avg('total_amount'),
            'last_purchase_date' => $supplier->purchases()->latest()->first()?->purchase_date,
        ];

        return view('suppliers.show', compact('supplier', 'stats'));
    }

    public function edit(Supplier $supplier)
    {
        $paymentTerms = Supplier::getPaymentTermsOptions();
        return view('suppliers.edit', compact('supplier', 'paymentTerms'));
    }

    public function update(Request $request, Supplier $supplier)
    {
        $request->validate([
            'company_name' => 'required|string|max:255',
            'contact_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'address' => 'required|string|max:255',
            'city' => 'required|string|max:100',
            'state' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
            'tax_id' => 'nullable|string|max:50',
            'payment_terms' => 'required|string',
            'credit_limit' => 'nullable|numeric|min:0',
            'specialties' => 'nullable|array',
            'services' => 'nullable|array',
            'average_turnaround_days' => 'nullable|numeric|min:1',
            'quality_rating' => 'nullable|numeric|min:0|max:5',
            'notes' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $supplier->update([
            'company_name' => $request->company_name,
            'contact_name' => $request->contact_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
            'city' => $request->city,
            'state' => $request->state,
            'postal_code' => $request->postal_code,
            'country' => $request->country,
            'tax_id' => $request->tax_id,
            'payment_terms' => $request->payment_terms,
            'credit_limit' => $request->credit_limit,
            'specialties' => $request->specialties,
            'services' => $request->services,
            'average_turnaround_days' => $request->average_turnaround_days,
            'quality_rating' => $request->quality_rating,
            'notes' => $request->notes,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('suppliers.show', $supplier)
            ->with('success', 'Proveedor actualizado correctamente');
    }

    public function destroy(Supplier $supplier)
    {
        // Verificar si tiene productos o compras asociadas
        if ($supplier->products()->count() > 0) {
            return back()->withErrors(['error' => 'No se puede eliminar un proveedor que tiene productos asociados']);
        }

        if ($supplier->purchases()->count() > 0) {
            return back()->withErrors(['error' => 'No se puede eliminar un proveedor que tiene compras asociadas']);
        }

        $supplier->delete();

        return redirect()->route('suppliers.index')
            ->with('success', 'Proveedor eliminado correctamente');
    }

    public function products(Supplier $supplier)
    {
        $products = $supplier->products()
            ->with('inventory')
            ->paginate(20);

        return view('suppliers.products', compact('supplier', 'products'));
    }

    public function purchases(Supplier $supplier)
    {
        $purchases = $supplier->purchases()
            ->with(['items.product'])
            ->latest()
            ->paginate(20);

        return view('suppliers.purchases', compact('supplier', 'purchases'));
    }

    public function performance(Supplier $supplier)
    {
        $metrics = $supplier->getPerformanceMetrics();
        $formattedMetrics = $supplier->getFormattedPerformanceMetricsAttribute();

        $monthlyPurchases = Purchase::where('supplier_id', $supplier->id)
            ->selectRaw('YEAR(purchase_date) as year, MONTH(purchase_date) as month, COUNT(*) as count, SUM(total_amount) as total')
            ->whereYear('purchase_date', now()->year)
            ->groupBy('year', 'month')
            ->orderBy('month')
            ->get();

        return view('suppliers.performance', compact('supplier', 'metrics', 'formattedMetrics', 'monthlyPurchases'));
    }

    public function toggleStatus(Supplier $supplier)
    {
        $supplier->update(['is_active' => !$supplier->is_active]);
        
        $status = $supplier->is_active ? 'activado' : 'desactivado';
        
        return back()->with('success', "Proveedor {$status} correctamente");
    }

    public function updateRating(Request $request, Supplier $supplier)
    {
        $request->validate([
            'rating' => 'required|numeric|min:0|max:5',
        ]);

        $supplier->updateQualityRating($request->rating);

        return back()->with('success', 'Calificación actualizada correctamente');
    }

    public function updateTurnaround(Request $request, Supplier $supplier)
    {
        $request->validate([
            'days' => 'required|integer|min:1',
        ]);

        $supplier->updateTurnaroundDays($request->days);

        return back()->with('success', 'Tiempo de entrega actualizado correctamente');
    }

    public function addSpecialty(Request $request, Supplier $supplier)
    {
        $request->validate([
            'specialty' => 'required|string|max:255',
        ]);

        $supplier->addSpecialty($request->specialty);

        return back()->with('success', 'Especialidad agregada correctamente');
    }

    public function removeSpecialty(Supplier $supplier, $specialty)
    {
        $supplier->removeSpecialty($specialty);

        return back()->with('success', 'Especialidad eliminada correctamente');
    }

    public function addService(Request $request, Supplier $supplier)
    {
        $request->validate([
            'service' => 'required|string|max:255',
        ]);

        $supplier->addService($request->service);

        return back()->with('success', 'Servicio agregado correctamente');
    }

    public function removeService(Supplier $supplier, $service)
    {
        $supplier->removeService($service);

        return back()->with('success', 'Servicio eliminado correctamente');
    }

    public function report()
    {
        $stats = [
            'total_suppliers' => Supplier::count(),
            'active_suppliers' => Supplier::where('is_active', true)->count(),
            'total_products' => Product::whereHas('primarySupplier')->count(),
            'total_purchases' => Purchase::count(),
        ];

        $topSuppliers = Supplier::withCount('purchases')
            ->withSum('purchases', 'total_amount')
            ->orderBy('purchases_sum_total_amount', 'desc')
            ->limit(10)
            ->get();

        $suppliersByCity = Supplier::select('city', DB::raw('count(*) as count'))
            ->whereNotNull('city')
            ->groupBy('city')
            ->orderBy('count', 'desc')
            ->get();

        return view('suppliers.report', compact('stats', 'topSuppliers', 'suppliersByCity'));
    }

    private function generateSupplierCode()
    {
        $lastSupplier = Supplier::latest()->first();
        $number = $lastSupplier ? (int) substr($lastSupplier->supplier_code, -4) + 1 : 1;
        
        return 'PROV-' . str_pad($number, 4, '0', STR_PAD_LEFT);
    }
}
