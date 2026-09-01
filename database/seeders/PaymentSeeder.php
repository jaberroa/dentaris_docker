<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Payment;
use App\Models\Invoice;
use App\Models\Patient;
use App\Models\User;
use Carbon\Carbon;

class PaymentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Creando datos de pagos...');

        // Obtener datos necesarios
        $patients = Patient::all();
        $users = User::all();

        if ($patients->isEmpty() || $users->isEmpty()) {
            $this->command->warn('No hay pacientes o usuarios disponibles. Ejecuta los seeders correspondientes primero.');
            return;
        }

        // Métodos de pago
        $paymentMethods = ['cash', 'card', 'transfer', 'check'];
        $statuses = ['completed', 'pending', 'failed'];
        $statusWeights = [85, 10, 5]; // 85% completados, 10% pendientes, 5% fallidos

        $paymentsCreated = 0;

        // Crear pagos para los últimos 6 meses
        for ($month = 0; $month < 6; $month++) {
            $monthDate = Carbon::now()->subMonths($month);
            $daysInMonth = $monthDate->daysInMonth;
            
            // Crear entre 15-25 pagos por mes
            $paymentsPerMonth = rand(15, 25);
            
            for ($i = 0; $i < $paymentsPerMonth; $i++) {
                $patient = $patients->random();
                $user = $users->random();
                $paymentMethod = $paymentMethods[array_rand($paymentMethods)];
                $status = $this->getWeightedRandomStatus($statuses, $statusWeights);
                
                // Generar fecha aleatoria del mes
                $day = rand(1, $daysInMonth);
                $paymentDate = $monthDate->copy()->day($day);
                
                // Generar monto realista basado en procedimientos dentales
                $amount = $this->generateRealisticAmount();
                
                // Generar número de pago único
                $paymentNumber = 'PAY-' . $paymentDate->format('Ymd') . '-' . str_pad($i + 1, 3, '0', STR_PAD_LEFT) . '-' . uniqid();
                
                // Crear o encontrar una factura para este pago
                $invoice = $this->createOrFindInvoice($patient, $user, $paymentDate, $amount);
                
                $payment = Payment::create([
                    'payment_number' => $paymentNumber,
                    'invoice_id' => $invoice->id,
                    'patient_id' => $patient->id,
                    'payment_date' => $paymentDate,
                    'amount' => $amount,
                    'payment_method' => $paymentMethod,
                    'reference_number' => $this->generateReferenceNumber($paymentMethod),
                    'notes' => fake()->optional(0.3)->sentence(),
                    'status' => $status,
                    'transaction_id' => $status === 'completed' ? 'TXN-' . strtoupper(fake()->bothify('????-####-????')) : null,
                    'payment_details' => $this->generatePaymentDetails($paymentMethod),
                    'processed_by' => $user->id,
                ]);

                $paymentsCreated++;
            }
        }

        // Crear algunos pagos para el mes actual
        $currentMonth = Carbon::now();
        $currentMonthPayments = rand(8, 15);
        
        for ($i = 0; $i < $currentMonthPayments; $i++) {
            $patient = $patients->random();
            $user = $users->random();
            $paymentMethod = $paymentMethods[array_rand($paymentMethods)];
            $status = $this->getWeightedRandomStatus($statuses, $statusWeights);
            
            // Generar fecha aleatoria del mes actual
            $day = rand(1, $currentMonth->day);
            $paymentDate = $currentMonth->copy()->day($day);
            
            $amount = $this->generateRealisticAmount();
            $paymentNumber = 'PAY-' . $paymentDate->format('Ymd') . '-' . str_pad($i + 1, 3, '0', STR_PAD_LEFT);
            
            $invoice = $this->createOrFindInvoice($patient, $user, $paymentDate, $amount);
            
            $payment = Payment::create([
                'payment_number' => $paymentNumber,
                'invoice_id' => $invoice->id,
                'patient_id' => $patient->id,
                'payment_date' => $paymentDate,
                'amount' => $amount,
                'payment_method' => $paymentMethod,
                'reference_number' => $this->generateReferenceNumber($paymentMethod),
                'notes' => fake()->optional(0.3)->sentence(),
                'status' => $status,
                'transaction_id' => $status === 'completed' ? 'TXN-' . strtoupper(fake()->bothify('????-####-????')) : null,
                'payment_details' => $this->generatePaymentDetails($paymentMethod),
                'processed_by' => $user->id,
            ]);

            $paymentsCreated++;
        }

        $this->command->info('✅ Pagos creados exitosamente:');
        $this->command->info("- {$paymentsCreated} pagos generados");
        $this->command->info('- Distribuidos en los últimos 6 meses');
        $this->command->info('- Montos realistas de procedimientos dentales');
        $this->command->info('- Diferentes métodos de pago y estados');
    }

    /**
     * Seleccionar estado basado en pesos
     */
    private function getWeightedRandomStatus($statuses, $weights)
    {
        $totalWeight = array_sum($weights);
        $random = rand(1, $totalWeight);
        
        $currentWeight = 0;
        foreach ($statuses as $index => $status) {
            $currentWeight += $weights[$index];
            if ($random <= $currentWeight) {
                return $status;
            }
        }
        
        return $statuses[0]; // Fallback
    }

    /**
     * Generar monto realista para procedimientos dentales
     */
    private function generateRealisticAmount()
    {
        $procedures = [
            // Procedimientos preventivos
            ['min' => 500, 'max' => 1200, 'weight' => 30],
            // Restauraciones
            ['min' => 800, 'max' => 2500, 'weight' => 25],
            // Endodoncias
            ['min' => 3000, 'max' => 5000, 'weight' => 15],
            // Cirugía
            ['min' => 1500, 'max' => 3000, 'weight' => 10],
            // Prótesis
            ['min' => 5000, 'max' => 15000, 'weight' => 10],
            // Implantes
            ['min' => 8000, 'max' => 20000, 'weight' => 5],
            // Ortodoncia
            ['min' => 3000, 'max' => 8000, 'weight' => 5],
        ];

        $totalWeight = array_sum(array_column($procedures, 'weight'));
        $random = rand(1, $totalWeight);
        
        $currentWeight = 0;
        foreach ($procedures as $procedure) {
            $currentWeight += $procedure['weight'];
            if ($random <= $currentWeight) {
                return rand($procedure['min'], $procedure['max']);
            }
        }
        
        return rand(500, 2000); // Fallback
    }

    /**
     * Generar número de referencia basado en método de pago
     */
    private function generateReferenceNumber($paymentMethod)
    {
        return match($paymentMethod) {
            'card' => 'CARD-' . fake()->numerify('####-####-####-####'),
            'transfer' => 'TRF-' . fake()->numerify('##########'),
            'check' => 'CHK-' . fake()->numerify('######'),
            default => null
        };
    }

    /**
     * Generar detalles del pago
     */
    private function generatePaymentDetails($paymentMethod)
    {
        return match($paymentMethod) {
            'card' => [
                'card_type' => fake()->randomElement(['Visa', 'Mastercard', 'American Express']),
                'last_four' => fake()->numerify('####'),
                'authorization_code' => fake()->bothify('AUTH-####-####')
            ],
            'transfer' => [
                'bank' => fake()->randomElement(['BBVA', 'Santander', 'HSBC', 'Banorte']),
                'account_last_four' => fake()->numerify('####')
            ],
            'check' => [
                'check_number' => fake()->numerify('######'),
                'bank' => fake()->randomElement(['BBVA', 'Santander', 'HSBC', 'Banorte'])
            ],
            default => null
        };
    }

    /**
     * Crear o encontrar una factura para el pago
     */
    private function createOrFindInvoice($patient, $user, $paymentDate, $amount)
    {
        // Buscar factura existente no pagada
        $existingInvoice = Invoice::where('patient_id', $patient->id)
            ->whereIn('status', ['draft', 'sent', 'overdue'])
            ->where('balance_due', '>=', $amount)
            ->first();

        if ($existingInvoice) {
            return $existingInvoice;
        }

        // Crear nueva factura
        $invoiceNumber = 'INV-' . $paymentDate->format('Ymd') . '-' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT) . '-' . uniqid();
        $subtotal = $amount;
        $taxRate = 16; // IVA en México
        $taxAmount = $subtotal * ($taxRate / 100);
        $totalAmount = $subtotal + $taxAmount;

        return Invoice::create([
            'invoice_number' => $invoiceNumber,
            'patient_id' => $patient->id,
            'staff_id' => 1, // Asumir que existe staff con ID 1
            'invoice_date' => $paymentDate,
            'due_date' => $paymentDate->copy()->addDays(30),
            'status' => 'sent',
            'subtotal' => $subtotal,
            'tax_rate' => $taxRate,
            'tax_amount' => $taxAmount,
            'discount_amount' => 0,
            'total_amount' => $totalAmount,
            'paid_amount' => 0,
            'balance_due' => $totalAmount,
            'notes' => fake()->optional(0.4)->sentence(),
            'payment_terms' => '30 días',
            'is_recurring' => false,
            'created_by' => $user->id,
        ]);
    }
}
