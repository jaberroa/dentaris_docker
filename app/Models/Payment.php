<?php

namespace App\Models;

use App\Modules\Clinics\Data\ClinicContext;
use App\Modules\Clinics\Models\Clinic;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_number', 'invoice_id', 'patient_id', 'payment_date', 'amount',
        'payment_method', 'reference_number', 'notes', 'status', 'transaction_id',
        'payment_details', 'processed_by'
    ];

    protected function casts(): array
    {
        return [
            'payment_date' => 'date',
            'amount' => 'decimal:2',
            'payment_details' => 'array',
        ];
    }

    // Relaciones
    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function clinic()
    {
        return $this->belongsTo(Clinic::class);
    }

    public function scopeForClinic(Builder $query, ClinicContext $context): Builder
    {
        return $query->where($query->qualifyColumn('clinic_id'), $context->clinicId);
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function processor()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    // Scopes
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function scopeByPaymentMethod($query, $method)
    {
        return $query->where('payment_method', $method);
    }

    public function scopeByPatient($query, $patientId)
    {
        return $query->where('patient_id', $patientId);
    }

    public function scopeByInvoice($query, $invoiceId)
    {
        return $query->where('invoice_id', $invoiceId);
    }

    public function scopeCash($query)
    {
        return $query->where('payment_method', 'cash');
    }

    public function scopeCard($query)
    {
        return $query->where('payment_method', 'card');
    }

    public function scopeTransfer($query)
    {
        return $query->where('payment_method', 'transfer');
    }

    // Métodos de utilidad
    public function isCompleted()
    {
        return $this->status === 'completed';
    }

    public function isPending()
    {
        return $this->status === 'pending';
    }

    public function isFailed()
    {
        return $this->status === 'failed';
    }

    public function isCancelled()
    {
        return $this->status === 'cancelled';
    }

    public function markAsCompleted()
    {
        $this->update(['status' => 'completed']);
    }

    public function markAsFailed($errorMessage = null)
    {
        $this->update([
            'status' => 'failed',
            'notes' => $errorMessage ? $this->notes . "\nError: " . $errorMessage : $this->notes
        ]);
    }

    public function markAsCancelled($reason = null)
    {
        $this->update([
            'status' => 'cancelled',
            'notes' => $reason ? $this->notes . "\nCancelado: " . $reason : $this->notes
        ]);
    }

    public function getFormattedAmountAttribute()
    {
        return '$' . number_format($this->amount, 2);
    }

    public function getFormattedPaymentDateAttribute()
    {
        return $this->payment_date->format('d/m/Y');
    }

    public function getPaymentMethodDisplayAttribute()
    {
        return match($this->payment_method) {
            'cash' => 'Efectivo',
            'card' => 'Tarjeta',
            'transfer' => 'Transferencia',
            'check' => 'Cheque',
            'other' => 'Otro',
            default => $this->payment_method
        };
    }

    public function getStatusDisplayAttribute()
    {
        return match($this->status) {
            'pending' => 'Pendiente',
            'completed' => 'Completado',
            'failed' => 'Fallido',
            'cancelled' => 'Cancelado',
            default => $this->status
        };
    }

    public function updateInvoicePayment()
    {
        if ($this->isCompleted()) {
            $this->invoice->addPayment($this->amount, $this->payment_method);
        }
    }
}
