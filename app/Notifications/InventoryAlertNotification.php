<?php

namespace App\Notifications;

use App\Models\Product;
use App\Models\Inventory;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\DatabaseMessage;
use Illuminate\Notifications\Notification;

class InventoryAlertNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Product $product,
        public Inventory $inventory,
        public string $alertType = 'low_stock'
    ) {}

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        $subject = match($this->alertType) {
            'low_stock' => 'Alerta de Stock Bajo',
            'out_of_stock' => 'Alerta de Stock Agotado',
            'expiring_soon' => 'Alerta de Producto Próximo a Vencer',
            'expired' => 'Alerta de Producto Vencido',
            default => 'Alerta de Inventario'
        };

        $message = match($this->alertType) {
            'low_stock' => "El producto tiene stock bajo y necesita reposición",
            'out_of_stock' => "El producto está agotado y requiere reposición inmediata",
            'expiring_soon' => "El producto está próximo a vencer",
            'expired' => "El producto ha vencido y debe ser retirado",
            default => "Alerta de inventario"
        };

        return (new MailMessage)
            ->subject($subject)
            ->greeting('Alerta de Inventario')
            ->line($message)
            ->line('**Detalles del producto:**')
            ->line('📦 Producto: ' . $this->product->name)
            ->line('🏷️ Código: ' . $this->product->product_code)
            ->line('📊 Stock Actual: ' . $this->inventory->current_stock)
            ->line('📈 Stock Mínimo: ' . $this->product->minimum_stock)
            ->line('💰 Precio: $' . number_format($this->product->cost_price, 2))
            ->when($this->alertType === 'expiring_soon' || $this->alertType === 'expired', function ($message) {
                return $message->line('📅 Fecha de Vencimiento: ' . $this->product->expiry_date->format('d/m/Y'));
            })
            ->action('Ver Producto', url('/products/' . $this->product->id))
            ->line('Por favor, revisa el inventario y realiza las acciones necesarias.');
    }

    public function toDatabase($notifiable): array
    {
        return [
            'type' => 'inventory_alert',
            'product_id' => $this->product->id,
            'inventory_id' => $this->inventory->id,
            'alert_type' => $this->alertType,
            'current_stock' => $this->inventory->current_stock,
            'minimum_stock' => $this->product->minimum_stock,
            'product_name' => $this->product->name,
            'message' => 'Alerta de inventario',
        ];
    }
}