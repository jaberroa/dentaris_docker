<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Supplier;
use App\Models\Inventory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function __construct()
    {
        // Middleware se aplica en las rutas
        $this->middleware('can:view_products')->only(['index', 'show']);
        $this->middleware('can:manage_products')->except(['index', 'show']);
    }

    public function index(Request $request)
    {
        $query = Product::with(['primarySupplier', 'inventory']);

        // Filtros
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('product_code', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('brand')) {
            $query->where('brand', $request->brand);
        }

        if ($request->filled('supplier')) {
            $query->where('primary_supplier_id', $request->supplier);
        }

        if ($request->filled('stock_status')) {
            switch ($request->stock_status) {
                case 'low':
                    $query->whereHas('inventory', function($q) {
                        $q->whereRaw('available_stock <= minimum_stock');
                    });
                    break;
                case 'out':
                    $query->whereHas('inventory', function($q) {
                        $q->where('available_stock', 0);
                    });
                    break;
                case 'normal':
                    $query->whereHas('inventory', function($q) {
                        $q->whereRaw('available_stock > minimum_stock');
                    });
                    break;
            }
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active);
        }

        // Ordenamiento
        $sortBy = $request->get('sort_by', 'name');
        $sortOrder = $request->get('sort_order', 'asc');

        switch ($sortBy) {
            case 'category':
                $query->orderBy('category', $sortOrder);
                break;
            case 'brand':
                $query->orderBy('brand', $sortOrder);
                break;
            case 'cost_price':
                $query->orderBy('cost_price', $sortOrder);
                break;
            case 'selling_price':
                $query->orderBy('selling_price', $sortOrder);
                break;
            default:
                $query->orderBy('name', $sortOrder);
        }

        $products = $query->paginate(20);

        // Datos para filtros
        $categories = Product::getCategories();
        $brands = Product::select('brand')->distinct()->whereNotNull('brand')->pluck('brand');
        $suppliers = Supplier::where('is_active', true)->get();

        return view('products.index', compact('products', 'categories', 'brands', 'suppliers'));
    }

    public function create()
    {
        $categories = Product::getCategories();
        $unitsOfMeasure = Product::getUnitsOfMeasure();
        $suppliers = Supplier::where('is_active', true)->get();

        return view('products.create', compact('categories', 'unitsOfMeasure', 'suppliers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'required|string',
            'subcategory' => 'nullable|string|max:255',
            'unit_of_measure' => 'required|string',
            'cost_price' => 'nullable|numeric|min:0',
            'selling_price' => 'nullable|numeric|min:0',
            'minimum_stock' => 'required|integer|min:0',
            'maximum_stock' => 'nullable|integer|min:0',
            'barcode' => 'nullable|string|max:255|unique:products',
            'brand' => 'nullable|string|max:255',
            'model' => 'nullable|string|max:255',
            'expiry_date' => 'nullable|date',
            'storage_conditions' => 'nullable|string',
            'usage_instructions' => 'nullable|string',
            'requires_prescription' => 'boolean',
            'is_controlled' => 'boolean',
            'primary_supplier_id' => 'nullable|exists:suppliers,id',
            'is_active' => 'boolean',
        ]);

        $product = Product::create([
            'product_code' => $this->generateProductCode(),
            'name' => $request->name,
            'description' => $request->description,
            'category' => $request->category,
            'subcategory' => $request->subcategory,
            'unit_of_measure' => $request->unit_of_measure,
            'cost_price' => $request->cost_price,
            'selling_price' => $request->selling_price,
            'minimum_stock' => $request->minimum_stock,
            'maximum_stock' => $request->maximum_stock,
            'barcode' => $request->barcode,
            'brand' => $request->brand,
            'model' => $request->model,
            'expiry_date' => $request->expiry_date,
            'storage_conditions' => $request->storage_conditions,
            'usage_instructions' => $request->usage_instructions,
            'requires_prescription' => $request->has('requires_prescription'),
            'is_controlled' => $request->has('is_controlled'),
            'primary_supplier_id' => $request->primary_supplier_id,
            'is_active' => $request->has('is_active'),
            'created_by' => auth()->id(),
        ]);

        // Crear registro de inventario inicial
        Inventory::create([
            'product_id' => $product->id,
            'current_stock' => 0,
            'reserved_stock' => 0,
            'available_stock' => 0,
            'average_cost' => $product->cost_price ?: 0,
        ]);

        return redirect()->route('products.show', $product)
            ->with('success', 'Producto creado correctamente');
    }

    public function show(Product $product)
    {
        $product->load(['primarySupplier', 'inventory', 'purchaseItems.purchase']);

        $stats = [
            'current_stock' => $product->inventory ? $product->inventory->current_stock : 0,
            'available_stock' => $product->inventory ? $product->inventory->available_stock : 0,
            'reserved_stock' => $product->inventory ? $product->inventory->reserved_stock : 0,
            'stock_value' => $product->inventory ? $product->inventory->getStockValue() : 0,
            'total_purchases' => $product->purchaseItems()->count(),
            'last_purchase_date' => $product->purchaseItems()->latest()->first()?->purchase->purchase_date,
        ];

        return view('products.show', compact('product', 'stats'));
    }

    public function edit(Product $product)
    {
        $categories = Product::getCategories();
        $unitsOfMeasure = Product::getUnitsOfMeasure();
        $suppliers = Supplier::where('is_active', true)->get();

        return view('products.edit', compact('product', 'categories', 'unitsOfMeasure', 'suppliers'));
    }

    public function update(Request $request, Product $product)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'required|string',
            'subcategory' => 'nullable|string|max:255',
            'unit_of_measure' => 'required|string',
            'cost_price' => 'nullable|numeric|min:0',
            'selling_price' => 'nullable|numeric|min:0',
            'minimum_stock' => 'required|integer|min:0',
            'maximum_stock' => 'nullable|integer|min:0',
            'barcode' => 'nullable|string|max:255|unique:products,barcode,' . $product->id,
            'brand' => 'nullable|string|max:255',
            'model' => 'nullable|string|max:255',
            'expiry_date' => 'nullable|date',
            'storage_conditions' => 'nullable|string',
            'usage_instructions' => 'nullable|string',
            'requires_prescription' => 'boolean',
            'is_controlled' => 'boolean',
            'primary_supplier_id' => 'nullable|exists:suppliers,id',
            'is_active' => 'boolean',
        ]);

        $product->update([
            'name' => $request->name,
            'description' => $request->description,
            'category' => $request->category,
            'subcategory' => $request->subcategory,
            'unit_of_measure' => $request->unit_of_measure,
            'cost_price' => $request->cost_price,
            'selling_price' => $request->selling_price,
            'minimum_stock' => $request->minimum_stock,
            'maximum_stock' => $request->maximum_stock,
            'barcode' => $request->barcode,
            'brand' => $request->brand,
            'model' => $request->model,
            'expiry_date' => $request->expiry_date,
            'storage_conditions' => $request->storage_conditions,
            'usage_instructions' => $request->usage_instructions,
            'requires_prescription' => $request->has('requires_prescription'),
            'is_controlled' => $request->has('is_controlled'),
            'primary_supplier_id' => $request->primary_supplier_id,
            'is_active' => $request->has('is_active'),
        ]);

        // Actualizar alertas de inventario si existe
        if ($product->inventory) {
            $product->inventory->updateAlerts();
        }

        return redirect()->route('products.show', $product)
            ->with('success', 'Producto actualizado correctamente');
    }

    public function destroy(Product $product)
    {
        // Verificar si tiene movimientos de inventario o compras
        if ($product->purchaseItems()->count() > 0) {
            return back()->withErrors(['error' => 'No se puede eliminar un producto que tiene compras asociadas']);
        }

        // Eliminar inventario asociado
        if ($product->inventory) {
            $product->inventory->delete();
        }

        $product->delete();

        return redirect()->route('products.index')
            ->with('success', 'Producto eliminado correctamente');
    }

    public function lowStock()
    {
        $products = Product::with(['primarySupplier', 'inventory'])
            ->where('is_active', true)
            ->whereHas('inventory', function($query) {
                $query->whereRaw('available_stock <= minimum_stock');
            })
            ->orderByRaw('available_stock - minimum_stock')
            ->paginate(20);

        return view('products.low-stock', compact('products'));
    }

    public function outOfStock()
    {
        $products = Product::with(['primarySupplier', 'inventory'])
            ->where('is_active', true)
            ->whereHas('inventory', function($query) {
                $query->where('available_stock', 0);
            })
            ->orderBy('name')
            ->paginate(20);

        return view('products.out-of-stock', compact('products'));
    }

    public function expiringSoon()
    {
        $products = Product::with(['primarySupplier', 'inventory'])
            ->where('is_active', true)
            ->where('expiry_date', '<=', now()->addDays(30))
            ->where('expiry_date', '>', now())
            ->orderBy('expiry_date')
            ->paginate(20);

        return view('products.expiring-soon', compact('products'));
    }

    public function expired()
    {
        $products = Product::with(['primarySupplier', 'inventory'])
            ->where('is_active', true)
            ->where('expiry_date', '<', now())
            ->orderBy('expiry_date')
            ->paginate(20);

        return view('products.expired', compact('products'));
    }

    public function byCategory($category)
    {
        $products = Product::with(['primarySupplier', 'inventory'])
            ->where('category', $category)
            ->where('is_active', true)
            ->orderBy('name')
            ->paginate(20);

        return view('products.by-category', compact('products', 'category'));
    }

    public function byBrand($brand)
    {
        $products = Product::with(['primarySupplier', 'inventory'])
            ->where('brand', $brand)
            ->where('is_active', true)
            ->orderBy('name')
            ->paginate(20);

        return view('products.by-brand', compact('products', 'brand'));
    }

    public function toggleStatus(Product $product)
    {
        $product->update(['is_active' => !$product->is_active]);
        
        $status = $product->is_active ? 'activado' : 'desactivado';
        
        return back()->with('success', "Producto {$status} correctamente");
    }

    public function updatePricing(Request $request, Product $product)
    {
        $request->validate([
            'cost_price' => 'nullable|numeric|min:0',
            'selling_price' => 'nullable|numeric|min:0',
        ]);

        $product->update([
            'cost_price' => $request->cost_price,
            'selling_price' => $request->selling_price,
        ]);

        return back()->with('success', 'Precios actualizados correctamente');
    }

    public function updateStockLevels(Request $request, Product $product)
    {
        $request->validate([
            'minimum_stock' => 'required|integer|min:0',
            'maximum_stock' => 'nullable|integer|min:0',
        ]);

        $product->update([
            'minimum_stock' => $request->minimum_stock,
            'maximum_stock' => $request->maximum_stock,
        ]);

        // Actualizar alertas de inventario
        if ($product->inventory) {
            $product->inventory->updateAlerts();
        }

        return back()->with('success', 'Niveles de stock actualizados correctamente');
    }

    public function search(Request $request)
    {
        $query = $request->get('q');
        
        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $products = Product::where('is_active', true)
            ->where(function($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('product_code', 'like', "%{$query}%")
                  ->orWhere('barcode', 'like', "%{$query}%");
            })
            ->with(['inventory'])
            ->limit(10)
            ->get();

        return response()->json($products);
    }

    public function report()
    {
        $stats = [
            'total_products' => Product::count(),
            'active_products' => Product::where('is_active', true)->count(),
            'low_stock_products' => Product::whereHas('inventory', function($query) {
                $query->whereRaw('available_stock <= minimum_stock');
            })->count(),
            'out_of_stock_products' => Product::whereHas('inventory', function($query) {
                $query->where('available_stock', 0);
            })->count(),
            'expiring_products' => Product::where('expiry_date', '<=', now()->addDays(30))
                ->where('expiry_date', '>', now())
                ->count(),
        ];

        $productsByCategory = Product::select('category', DB::raw('count(*) as count'))
            ->where('is_active', true)
            ->groupBy('category')
            ->orderBy('count', 'desc')
            ->get();

        $productsByBrand = Product::select('brand', DB::raw('count(*) as count'))
            ->where('is_active', true)
            ->whereNotNull('brand')
            ->groupBy('brand')
            ->orderBy('count', 'desc')
            ->limit(10)
            ->get();

        return view('products.report', compact('stats', 'productsByCategory', 'productsByBrand'));
    }

    private function generateProductCode()
    {
        $lastProduct = Product::latest()->first();
        $number = $lastProduct ? (int) substr($lastProduct->product_code, -6) + 1 : 1;
        
        return 'PROD-' . str_pad($number, 6, '0', STR_PAD_LEFT);
    }
}





