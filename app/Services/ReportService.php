<?php

namespace App\Services;

use App\Models\Patient;
use App\Models\Appointment;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Staff;
use App\Models\Product;
use App\Models\Inventory;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportService
{
    public function generateFinancialReport($startDate, $endDate)
    {
        $report = [
            'period' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'days' => Carbon::parse($startDate)->diffInDays(Carbon::parse($endDate)) + 1
            ],
            'summary' => $this->getFinancialSummary($startDate, $endDate),
            'revenue_by_day' => $this->getRevenueByDay($startDate, $endDate),
            'revenue_by_staff' => $this->getRevenueByStaff($startDate, $endDate),
            'payment_methods' => $this->getPaymentMethods($startDate, $endDate),
        ];

        return $report;
    }

    public function generateAppointmentReport($startDate, $endDate)
    {
        $report = [
            'period' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'days' => Carbon::parse($startDate)->diffInDays(Carbon::parse($endDate)) + 1
            ],
            'summary' => $this->getAppointmentSummary($startDate, $endDate),
            'appointments_by_day' => $this->getAppointmentsByDay($startDate, $endDate),
            'appointments_by_staff' => $this->getAppointmentsByStaff($startDate, $endDate),
            'appointments_by_status' => $this->getAppointmentsByStatus($startDate, $endDate),
        ];

        return $report;
    }

    public function generateInventoryReport()
    {
        $report = [
            'summary' => $this->getInventorySummary(),
            'products_by_category' => $this->getProductsByCategory(),
            'low_stock_products' => $this->getLowStockProducts(),
            'out_of_stock_products' => $this->getOutOfStockProducts(),
        ];

        return $report;
    }

    private function getFinancialSummary($startDate, $endDate)
    {
        $revenue = Payment::whereBetween('payment_date', [$startDate, $endDate])
            ->where('status', 'completed')
            ->sum('amount');

        $invoices = Invoice::whereBetween('invoice_date', [$startDate, $endDate]);
        $totalInvoices = $invoices->count();
        $paidInvoices = $invoices->where('status', 'paid')->count();

        return [
            'total_revenue' => $revenue,
            'total_invoices' => $totalInvoices,
            'paid_invoices' => $paidInvoices,
            'unpaid_invoices' => $totalInvoices - $paidInvoices,
            'average_invoice_value' => $totalInvoices > 0 ? $revenue / $totalInvoices : 0,
            'payment_rate' => $totalInvoices > 0 ? ($paidInvoices / $totalInvoices) * 100 : 0,
        ];
    }

    private function getRevenueByDay($startDate, $endDate)
    {
        return Payment::selectRaw('DATE(payment_date) as date, SUM(amount) as revenue')
            ->whereBetween('payment_date', [$startDate, $endDate])
            ->where('status', 'completed')
            ->groupBy('date')
            ->orderBy('date')
            ->get();
    }

    private function getRevenueByStaff($startDate, $endDate)
    {
        return Staff::with('user')
            ->withSum(['invoices as total_revenue' => function($query) use ($startDate, $endDate) {
                $query->whereBetween('invoice_date', [$startDate, $endDate]);
            }], 'total_amount')
            ->where('is_active', true)
            ->orderBy('total_revenue', 'desc')
            ->get();
    }

    private function getPaymentMethods($startDate, $endDate)
    {
        return Payment::select('payment_method', DB::raw('COUNT(*) as count'), DB::raw('SUM(amount) as total'))
            ->whereBetween('payment_date', [$startDate, $endDate])
            ->where('status', 'completed')
            ->groupBy('payment_method')
            ->get();
    }

    private function getAppointmentSummary($startDate, $endDate)
    {
        $appointments = Appointment::whereBetween('appointment_date', [$startDate, $endDate]);
        $total = $appointments->count();
        $completed = $appointments->where('status', 'completed')->count();
        $cancelled = $appointments->where('status', 'cancelled')->count();

        return [
            'total_appointments' => $total,
            'completed_appointments' => $completed,
            'cancelled_appointments' => $cancelled,
            'completion_rate' => $total > 0 ? ($completed / $total) * 100 : 0,
            'cancellation_rate' => $total > 0 ? ($cancelled / $total) * 100 : 0,
        ];
    }

    private function getAppointmentsByDay($startDate, $endDate)
    {
        return Appointment::selectRaw('DATE(appointment_date) as date, COUNT(*) as count')
            ->whereBetween('appointment_date', [$startDate, $endDate])
            ->groupBy('date')
            ->orderBy('date')
            ->get();
    }

    private function getAppointmentsByStaff($startDate, $endDate)
    {
        return Staff::with('user')
            ->withCount(['appointments' => function($query) use ($startDate, $endDate) {
                $query->whereBetween('appointment_date', [$startDate, $endDate]);
            }])
            ->where('is_active', true)
            ->orderBy('appointments_count', 'desc')
            ->get();
    }

    private function getAppointmentsByStatus($startDate, $endDate)
    {
        return Appointment::select('status', DB::raw('COUNT(*) as count'))
            ->whereBetween('appointment_date', [$startDate, $endDate])
            ->groupBy('status')
            ->get();
    }

    private function getInventorySummary()
    {
        $totalProducts = Product::where('is_active', true)->count();
        $lowStock = Inventory::with('product')
            ->whereHas('product', function($query) {
                $query->where('is_active', true);
            })
            ->whereRaw('available_stock <= (SELECT minimum_stock FROM products WHERE products.id = inventory.product_id)')
            ->count();
        $outOfStock = Inventory::where('available_stock', 0)->count();

        return [
            'total_products' => $totalProducts,
            'low_stock_products' => $lowStock,
            'out_of_stock_products' => $outOfStock,
            'stock_alerts' => $lowStock + $outOfStock,
        ];
    }

    private function getProductsByCategory()
    {
        return Product::select('category', DB::raw('COUNT(*) as count'))
            ->where('is_active', true)
            ->groupBy('category')
            ->orderBy('count', 'desc')
            ->get();
    }

    private function getLowStockProducts()
    {
        return Product::with('inventory')
            ->where('is_active', true)
            ->whereHas('inventory', function($query) {
                $query->whereRaw('available_stock <= minimum_stock');
            })
            ->limit(20)
            ->get();
    }

    private function getOutOfStockProducts()
    {
        return Product::with('inventory')
            ->where('is_active', true)
            ->whereHas('inventory', function($query) {
                $query->where('available_stock', 0);
            })
            ->limit(20)
            ->get();
    }
}