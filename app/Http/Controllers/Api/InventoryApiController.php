<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;
use App\Models\Product;
use App\Models\Inventory;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class InventoryApiController extends Controller
{
    use ApiResponse;

    /**
     * Display a listing of inventory items.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Product::with('inventory', 'primarySupplier');

            // Filtros
            if ($request->has('search')) {
                $search = $request->get('search');
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('product_code', 'like', "%{$search}%");
                });
            }

            if ($request->has('category')) {
                $query->where('category', $request->get('category'));
            }

            // Ordenamiento
            $sortBy = $request->get('sort_by', 'name');
            $sortOrder = $request->get('sort_order', 'asc');
            $query->orderBy($sortBy, $sortOrder);

            // Paginación
            $perPage = $request->get('per_page', 15);
            $products = $query->paginate($perPage);

            return $this->paginatedResponse($products, 'Inventory items retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Error retrieving inventory items: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified inventory item.
     */
    public function show(Product $product): JsonResponse
    {
        try {
            $product->load(['inventory', 'primarySupplier']);
            
            return $this->successResponse($product, 'Inventory item retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Error retrieving inventory item: ' . $e->getMessage());
        }
    }

    /**
     * Get low stock products.
     */
    public function getLowStock(): JsonResponse
    {
        try {
            $products = Product::with('inventory')
                ->whereHas('inventory', function ($q) {
                    $q->whereColumn('current_stock', '<=', 'products.minimum_stock');
                })
                ->get();

            return $this->successResponse($products, 'Low stock products retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Error retrieving low stock products: ' . $e->getMessage());
        }
    }

    /**
     * Get out of stock products.
     */
    public function getOutOfStock(): JsonResponse
    {
        try {
            $products = Product::with('inventory')
                ->whereHas('inventory', function ($q) {
                    $q->where('current_stock', 0);
                })
                ->get();

            return $this->successResponse($products, 'Out of stock products retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Error retrieving out of stock products: ' . $e->getMessage());
        }
    }

    /**
     * Get inventory statistics.
     */
    public function getStatistics(): JsonResponse
    {
        try {
            $stats = [
                'total_products' => Product::count(),
                'low_stock_count' => Product::whereHas('inventory', function ($q) {
                    $q->whereColumn('current_stock', '<=', 'products.minimum_stock');
                })->count(),
                'out_of_stock_count' => Product::whereHas('inventory', function ($q) {
                    $q->where('current_stock', 0);
                })->count(),
            ];

            return $this->successResponse($stats, 'Inventory statistics retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Error retrieving inventory statistics: ' . $e->getMessage());
        }
    }
}