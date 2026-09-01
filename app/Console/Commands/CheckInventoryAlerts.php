<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Product;
use App\Models\Inventory;
use App\Services\InventoryAlertService;
use App\Jobs\ProcessInventoryAlertsJob;
use Carbon\Carbon;

class CheckInventoryAlerts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:check-inventory-alerts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verifica y envía alertas de inventario (stock bajo, agotado, vencido)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Verificando alertas de inventario...');

        $alerts = [];

        // Verificar stock bajo
        $lowStockProducts = Product::with('inventory')
            ->whereHas('inventory', function ($query) {
                $query->whereColumn('current_stock', '<=', 'products.minimum_stock')
                      ->where('current_stock', '>', 0);
            })
            ->get();

        foreach ($lowStockProducts as $product) {
            $alerts[] = [
                'type' => 'low_stock',
                'product_id' => $product->id,
                'product_name' => $product->name,
                'current_stock' => $product->inventory->current_stock,
                'minimum_stock' => $product->minimum_stock,
                'message' => "El producto '{$product->name}' tiene stock bajo. Actual: {$product->inventory->current_stock}, Mínimo: {$product->minimum_stock}.",
            ];
        }

        // Verificar stock agotado
        $outOfStockProducts = Product::with('inventory')
            ->whereHas('inventory', function ($query) {
                $query->where('current_stock', 0);
            })
            ->get();

        foreach ($outOfStockProducts as $product) {
            $alerts[] = [
                'type' => 'out_of_stock',
                'product_id' => $product->id,
                'product_name' => $product->name,
                'current_stock' => 0,
                'message' => "El producto '{$product->name}' está agotado.",
            ];
        }

        // Verificar productos vencidos
        $expiredProducts = Product::where('expiry_date', '<', Carbon::now())
            ->get();

        foreach ($expiredProducts as $product) {
            $alerts[] = [
                'type' => 'expired',
                'product_id' => $product->id,
                'product_name' => $product->name,
                'expiry_date' => $product->expiry_date,
                'message' => "El producto '{$product->name}' ha caducado el {$product->expiry_date->format('Y-m-d')}.",
            ];
        }

        // Verificar productos próximos a vencer (30 días)
        $expiringSoonProducts = Product::where('expiry_date', '>=', Carbon::now())
            ->where('expiry_date', '<=', Carbon::now()->addDays(30))
            ->get();

        foreach ($expiringSoonProducts as $product) {
            $alerts[] = [
                'type' => 'expiring_soon',
                'product_id' => $product->id,
                'product_name' => $product->name,
                'expiry_date' => $product->expiry_date,
                'days_to_expiry' => Carbon::now()->diffInDays($product->expiry_date),
                'message' => "El producto '{$product->name}' caducará en {$product->getDaysToExpiry()} días.",
            ];
        }

        if (!empty($alerts)) {
            $this->info("Se encontraron " . count($alerts) . " alertas de inventario.");
            
            // Procesar alertas
            ProcessInventoryAlertsJob::dispatch($alerts);
            
            foreach ($alerts as $alert) {
                $this->line("⚠️ {$alert['message']}");
            }
        } else {
            $this->info("No se encontraron alertas de inventario.");
        }

        $this->info('Verificación de alertas de inventario completada.');
    }
}