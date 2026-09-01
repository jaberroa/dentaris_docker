<?php

namespace App\Services;

use App\Models\Inventory;
use App\Models\Product;
use App\Models\Notification;
use Illuminate\Support\Facades\Log;

class InventoryAlertService
{
    /**
     * Verificar alertas de inventario
     */
    public function checkInventoryAlerts()
    {
        $alerts = [];

        // Verificar stock bajo
        $lowStockAlerts = $this->checkLowStockAlerts();
        $alerts = array_merge($alerts, $lowStockAlerts);

        // Verificar stock agotado
        $outOfStockAlerts = $this->checkOutOfStockAlerts();
        $alerts = array_merge($alerts, $outOfStockAlerts);

        // Verificar productos próximos a vencer
        $expiringAlerts = $this->checkExpiringProductsAlerts();
        $alerts = array_merge($alerts, $expiringAlerts);

        // Verificar productos vencidos
        $expiredAlerts = $this->checkExpiredProductsAlerts();
        $alerts = array_merge($alerts, $expiredAlerts);

        return $alerts;
    }

    /**
     * Verificar alertas de stock bajo
     */
    private function checkLowStockAlerts()
    {
        $lowStockProducts = Inventory::with(['product'])
            ->whereHas('product', function($query) {
                $query->where('is_active', true);
            })
            ->whereRaw('available_stock <= (SELECT minimum_stock FROM products WHERE products.id = inventory.product_id)')
            ->where('available_stock', '>', 0)
            ->get();

        $alerts = [];

        foreach ($lowStockProducts as $inventory) {
            $product = $inventory->product;
            
            // Verificar si ya existe una alerta activa
            $existingAlert = Notification::where('type', 'low_stock_alert')
                ->where('recipient_type', 'App\\Models\\Inventory')
                ->where('recipient_id', $inventory->id)
                ->where('status', 'active')
                ->first();

            if (!$existingAlert) {
                $alert = [
                    'type' => 'low_stock_alert',
                    'severity' => 'warning',
                    'title' => 'Stock Bajo',
                    'message' => "El producto '{$product->name}' tiene stock bajo. Stock actual: {$inventory->available_stock}, Stock mínimo: {$product->minimum_stock}",
                    'inventory_id' => $inventory->id,
                    'product_id' => $product->id,
                    'current_stock' => $inventory->available_stock,
                    'minimum_stock' => $product->minimum_stock,
                    'recommended_action' => 'Reabastecer inventario',
                ];

                $alerts[] = $alert;

                // Crear notificación en la base de datos
                $this->createAlertNotification($alert);
            }
        }

        return $alerts;
    }

    /**
     * Verificar alertas de stock agotado
     */
    private function checkOutOfStockAlerts()
    {
        $outOfStockProducts = Inventory::with(['product'])
            ->whereHas('product', function($query) {
                $query->where('is_active', true);
            })
            ->where('available_stock', 0)
            ->get();

        $alerts = [];

        foreach ($outOfStockProducts as $inventory) {
            $product = $inventory->product;
            
            // Verificar si ya existe una alerta activa
            $existingAlert = Notification::where('type', 'out_of_stock_alert')
                ->where('recipient_type', 'App\\Models\\Inventory')
                ->where('recipient_id', $inventory->id)
                ->where('status', 'active')
                ->first();

            if (!$existingAlert) {
                $alert = [
                    'type' => 'out_of_stock_alert',
                    'severity' => 'critical',
                    'title' => 'Stock Agotado',
                    'message' => "El producto '{$product->name}' está agotado. Stock actual: {$inventory->available_stock}",
                    'inventory_id' => $inventory->id,
                    'product_id' => $product->id,
                    'current_stock' => $inventory->available_stock,
                    'recommended_action' => 'Urgente: Reabastecer inventario inmediatamente',
                ];

                $alerts[] = $alert;

                // Crear notificación en la base de datos
                $this->createAlertNotification($alert);
            }
        }

        return $alerts;
    }

    /**
     * Verificar alertas de productos próximos a vencer
     */
    private function checkExpiringProductsAlerts()
    {
        $expiringProducts = Product::where('is_active', true)
            ->where('expiry_date', '<=', now()->addDays(30))
            ->where('expiry_date', '>', now())
            ->get();

        $alerts = [];

        foreach ($expiringProducts as $product) {
            $daysToExpiry = now()->diffInDays($product->expiry_date);
            
            // Verificar si ya existe una alerta activa
            $existingAlert = Notification::where('type', 'expiring_product_alert')
                ->where('recipient_type', 'App\\Models\\Product')
                ->where('recipient_id', $product->id)
                ->where('status', 'active')
                ->first();

            if (!$existingAlert) {
                $severity = $daysToExpiry <= 7 ? 'critical' : ($daysToExpiry <= 15 ? 'warning' : 'info');
                
                $alert = [
                    'type' => 'expiring_product_alert',
                    'severity' => $severity,
                    'title' => 'Producto Próximo a Vencer',
                    'message' => "El producto '{$product->name}' vence en {$daysToExpiry} días ({$product->expiry_date->format('d/m/Y')})",
                    'product_id' => $product->id,
                    'expiry_date' => $product->expiry_date,
                    'days_to_expiry' => $daysToExpiry,
                    'recommended_action' => $daysToExpiry <= 7 ? 'Usar inmediatamente o descartar' : 'Planificar uso prioritario',
                ];

                $alerts[] = $alert;

                // Crear notificación en la base de datos
                $this->createAlertNotification($alert);
            }
        }

        return $alerts;
    }

    /**
     * Verificar alertas de productos vencidos
     */
    private function checkExpiredProductsAlerts()
    {
        $expiredProducts = Product::where('is_active', true)
            ->where('expiry_date', '<', now())
            ->get();

        $alerts = [];

        foreach ($expiredProducts as $product) {
            $daysExpired = now()->diffInDays($product->expiry_date);
            
            // Verificar si ya existe una alerta activa
            $existingAlert = Notification::where('type', 'expired_product_alert')
                ->where('recipient_type', 'App\\Models\\Product')
                ->where('recipient_id', $product->id)
                ->where('status', 'active')
                ->first();

            if (!$existingAlert) {
                $alert = [
                    'type' => 'expired_product_alert',
                    'severity' => 'critical',
                    'title' => 'Producto Vencido',
                    'message' => "El producto '{$product->name}' está vencido desde hace {$daysExpired} días (venció el {$product->expiry_date->format('d/m/Y')})",
                    'product_id' => $product->id,
                    'expiry_date' => $product->expiry_date,
                    'days_expired' => $daysExpired,
                    'recommended_action' => 'Descartar producto inmediatamente',
                ];

                $alerts[] = $alert;

                // Crear notificación en la base de datos
                $this->createAlertNotification($alert);
            }
        }

        return $alerts;
    }

    /**
     * Crear notificación de alerta en la base de datos
     */
    private function createAlertNotification($alert)
    {
        try {
            Notification::create([
                'type' => $alert['type'],
                'title' => $alert['title'],
                'message' => $alert['message'],
                'recipient_type' => $alert['recipient_type'] ?? 'App\\Models\\Inventory',
                'recipient_id' => $alert['inventory_id'] ?? $alert['product_id'],
                'status' => 'active',
                'severity' => $alert['severity'],
                'metadata' => [
                    'recommended_action' => $alert['recommended_action'],
                    'current_stock' => $alert['current_stock'] ?? null,
                    'minimum_stock' => $alert['minimum_stock'] ?? null,
                    'expiry_date' => $alert['expiry_date'] ?? null,
                    'days_to_expiry' => $alert['days_to_expiry'] ?? null,
                    'days_expired' => $alert['days_expired'] ?? null,
                ],
                'created_at' => now(),
                'created_by' => 1, // Sistema
            ]);

            Log::info("Inventory alert created: {$alert['type']} for product/inventory ID: " . ($alert['product_id'] ?? $alert['inventory_id']));
        } catch (\Exception $e) {
            Log::error("Error creating inventory alert: " . $e->getMessage());
        }
    }

    /**
     * Marcar alerta como resuelta
     */
    public function resolveAlert($notificationId, $resolution = '')
    {
        try {
            $notification = Notification::findOrFail($notificationId);
            
            $notification->update([
                'status' => 'resolved',
                'resolved_at' => now(),
                'resolution' => $resolution,
            ]);

            Log::info("Inventory alert resolved: {$notification->type} - ID: {$notificationId}");
            
            return true;
        } catch (\Exception $e) {
            Log::error("Error resolving inventory alert: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener alertas activas
     */
    public function getActiveAlerts($limit = 50)
    {
        return Notification::where('status', 'active')
            ->whereIn('type', [
                'low_stock_alert',
                'out_of_stock_alert',
                'expiring_product_alert',
                'expired_product_alert'
            ])
            ->orderBy('severity', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Obtener estadísticas de alertas
     */
    public function getAlertStats($days = 30)
    {
        $startDate = now()->subDays($days);

        return [
            'total_alerts' => Notification::where('created_at', '>=', $startDate)
                ->whereIn('type', [
                    'low_stock_alert',
                    'out_of_stock_alert',
                    'expiring_product_alert',
                    'expired_product_alert'
                ])->count(),
            'active_alerts' => Notification::where('status', 'active')
                ->whereIn('type', [
                    'low_stock_alert',
                    'out_of_stock_alert',
                    'expiring_product_alert',
                    'expired_product_alert'
                ])->count(),
            'resolved_alerts' => Notification::where('status', 'resolved')
                ->where('resolved_at', '>=', $startDate)
                ->whereIn('type', [
                    'low_stock_alert',
                    'out_of_stock_alert',
                    'expiring_product_alert',
                    'expired_product_alert'
                ])->count(),
            'by_type' => Notification::where('created_at', '>=', $startDate)
                ->whereIn('type', [
                    'low_stock_alert',
                    'out_of_stock_alert',
                    'expiring_product_alert',
                    'expired_product_alert'
                ])
                ->select('type', DB::raw('COUNT(*) as count'))
                ->groupBy('type')
                ->get(),
            'by_severity' => Notification::where('created_at', '>=', $startDate)
                ->whereIn('type', [
                    'low_stock_alert',
                    'out_of_stock_alert',
                    'expiring_product_alert',
                    'expired_product_alert'
                ])
                ->select('severity', DB::raw('COUNT(*) as count'))
                ->groupBy('severity')
                ->get(),
        ];
    }

    /**
     * Limpiar alertas resueltas antiguas
     */
    public function cleanupResolvedAlerts($days = 90)
    {
        try {
            $deletedCount = Notification::where('status', 'resolved')
                ->where('resolved_at', '<', now()->subDays($days))
                ->whereIn('type', [
                    'low_stock_alert',
                    'out_of_stock_alert',
                    'expiring_product_alert',
                    'expired_product_alert'
                ])
                ->delete();

            Log::info("Cleaned up {$deletedCount} resolved inventory alerts older than {$days} days");
            
            return $deletedCount;
        } catch (\Exception $e) {
            Log::error("Error cleaning up resolved inventory alerts: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Actualizar alertas de inventario específico
     */
    public function updateInventoryAlerts(Inventory $inventory)
    {
        $product = $inventory->product;
        
        // Verificar si necesita alerta de stock bajo
        if ($inventory->available_stock <= $product->minimum_stock && $inventory->available_stock > 0) {
            $this->checkLowStockAlerts();
        }

        // Verificar si necesita alerta de stock agotado
        if ($inventory->available_stock == 0) {
            $this->checkOutOfStockAlerts();
        }

        // Si el stock se restauró, marcar alertas como resueltas
        if ($inventory->available_stock > $product->minimum_stock) {
            $this->resolveStockAlerts($inventory->id);
        }
    }

    /**
     * Resolver alertas de stock para un inventario específico
     */
    private function resolveStockAlerts($inventoryId)
    {
        Notification::where('status', 'active')
            ->whereIn('type', ['low_stock_alert', 'out_of_stock_alert'])
            ->where('recipient_type', 'App\\Models\\Inventory')
            ->where('recipient_id', $inventoryId)
            ->update([
                'status' => 'resolved',
                'resolved_at' => now(),
                'resolution' => 'Stock restaurado',
            ]);
    }
}





