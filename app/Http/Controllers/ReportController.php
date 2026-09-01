<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\Appointment;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\TreatmentPlan;
use App\Models\LabWork;
use App\Models\Product;
use App\Models\Inventory;
use App\Models\Staff;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function __construct()
    {
        // Middleware se aplica en las rutas
        // Middleware se aplica en las rutas
    }

    public function index()
    {
        return view('reports.index');
    }

    public function financial()
    {
        $period = request('period', 'month');
        $startDate = $this->getStartDate($period);
        
        $stats = [
            'total_revenue' => Payment::where('payment_date', '>=', $startDate)
                ->where('status', 'completed')
                ->sum('amount'),
            'total_invoices' => Invoice::where('invoice_date', '>=', $startDate)->count(),
            'paid_invoices' => Invoice::where('invoice_date', '>=', $startDate)
                ->where('status', 'paid')
                ->count(),
            'pending_invoices' => Invoice::where('invoice_date', '>=', $startDate)
                ->where('status', '!=', 'paid')
                ->count(),
        ];

        $revenueByMonth = $this->getRevenueByMonth();
        $paymentMethods = $this->getPaymentMethodsStats($startDate);

        return view('reports.financial', compact('stats', 'revenueByMonth', 'paymentMethods'));
    }

    public function appointments()
    {
        $period = request('period', 'month');
        $startDate = $this->getStartDate($period);

        $stats = [
            'total_appointments' => Appointment::where('appointment_date', '>=', $startDate)->count(),
            'completed_appointments' => Appointment::where('appointment_date', '>=', $startDate)
                ->where('status', 'completed')
                ->count(),
            'cancelled_appointments' => Appointment::where('appointment_date', '>=', $startDate)
                ->where('status', 'cancelled')
                ->count(),
        ];

        $appointmentsByStatus = $this->getAppointmentsByStatus($startDate);
        $appointmentsByStaff = $this->getAppointmentsByStaff($startDate);

        return view('reports.appointments', compact('stats', 'appointmentsByStatus', 'appointmentsByStaff'));
    }

    public function patients()
    {
        $period = request('period', 'month');
        $startDate = $this->getStartDate($period);

        $stats = [
            'total_patients' => Patient::count(),
            'new_patients' => Patient::where('created_at', '>=', $startDate)->count(),
            'active_patients' => Patient::whereHas('appointments', function($query) use ($startDate) {
                $query->where('appointment_date', '>=', $startDate);
            })->count(),
        ];

        $patientsByAge = $this->getPatientsByAge();
        $patientsByGender = $this->getPatientsByGender();

        return view('reports.patients', compact('stats', 'patientsByAge', 'patientsByGender'));
    }

    public function inventory()
    {
        $stats = [
            'total_products' => Product::where('is_active', true)->count(),
            'low_stock_products' => Inventory::with('product')
                ->whereHas('product', function($query) {
                    $query->where('is_active', true);
                })
                ->whereRaw('available_stock <= (SELECT minimum_stock FROM products WHERE products.id = inventory.product_id)')
                ->count(),
            'out_of_stock_products' => Inventory::where('available_stock', 0)->count(),
            'total_inventory_value' => Inventory::join('products', 'inventory.product_id', '=', 'products.id')
                ->where('products.is_active', true)
                ->sum(DB::raw('current_stock * average_cost')),
        ];

        $productsByCategory = $this->getProductsByCategory();
        $lowStockProducts = $this->getLowStockProducts();

        return view('reports.inventory', compact('stats', 'productsByCategory', 'lowStockProducts'));
    }

    public function staff()
    {
        $period = request('period', 'month');
        $startDate = $this->getStartDate($period);

        $stats = [
            'total_staff' => Staff::where('is_active', true)->count(),
            'active_staff' => Staff::where('is_active', true)
                ->whereHas('appointments', function($query) use ($startDate) {
                    $query->where('appointment_date', '>=', $startDate);
                })->count(),
        ];

        $appointmentsByStaff = $this->getAppointmentsByStaff($startDate);
        $revenueByStaff = $this->getRevenueByStaff($startDate);

        return view('reports.staff', compact('stats', 'appointmentsByStaff', 'revenueByStaff'));
    }

    public function kpis()
    {
        $period = request('period', 'month');
        $startDate = $this->getStartDate($period);

        $kpis = [
            'appointment_completion_rate' => $this->getAppointmentCompletionRate($startDate),
            'patient_retention_rate' => $this->getPatientRetentionRate($startDate),
            'average_revenue_per_patient' => $this->getAverageRevenuePerPatient($startDate),
            'lab_work_completion_rate' => $this->getLabWorkCompletionRate($startDate),
        ];

        return view('reports.kpis', compact('kpis'));
    }

    private function getStartDate($period)
    {
        switch ($period) {
            case 'week':
                return now()->startOfWeek();
            case 'quarter':
                return now()->startOfQuarter();
            case 'year':
                return now()->startOfYear();
            default:
                return now()->startOfMonth();
        }
    }

    private function getRevenueByMonth()
    {
        $data = [];
        for ($i = 11; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $revenue = Payment::whereYear('payment_date', $month->year)
                ->whereMonth('payment_date', $month->month)
                ->where('status', 'completed')
                ->sum('amount');
            
            $data[] = [
                'month' => $month->format('M Y'),
                'revenue' => $revenue
            ];
        }
        return $data;
    }

    private function getPaymentMethodsStats($startDate)
    {
        return Payment::select('payment_method', DB::raw('count(*) as count'), DB::raw('sum(amount) as total'))
            ->where('payment_date', '>=', $startDate)
            ->where('status', 'completed')
            ->groupBy('payment_method')
            ->get();
    }

    private function getAppointmentsByStatus($startDate)
    {
        return Appointment::select('status', DB::raw('count(*) as count'))
            ->where('appointment_date', '>=', $startDate)
            ->groupBy('status')
            ->get();
    }

    private function getAppointmentsByStaff($startDate)
    {
        return Staff::with('user')
            ->withCount(['appointments' => function($query) use ($startDate) {
                $query->where('appointment_date', '>=', $startDate);
            }])
            ->where('is_active', true)
            ->orderBy('appointments_count', 'desc')
            ->get();
    }

    private function getPatientsByAge()
    {
        return Patient::selectRaw('
            CASE 
                WHEN age < 18 THEN "0-17"
                WHEN age BETWEEN 18 AND 30 THEN "18-30"
                WHEN age BETWEEN 31 AND 45 THEN "31-45"
                WHEN age BETWEEN 46 AND 60 THEN "46-60"
                WHEN age > 60 THEN "60+"
            END as age_group,
            count(*) as count
        ')
        ->groupBy('age_group')
        ->get();
    }

    private function getPatientsByGender()
    {
        return Patient::select('gender', DB::raw('count(*) as count'))
            ->groupBy('gender')
            ->get();
    }

    private function getProductsByCategory()
    {
        return Product::select('category', DB::raw('count(*) as count'))
            ->where('is_active', true)
            ->groupBy('category')
            ->get();
    }

    private function getLowStockProducts()
    {
        return Product::with('inventory')
            ->where('is_active', true)
            ->whereHas('inventory', function($query) {
                $query->whereRaw('available_stock <= minimum_stock');
            })
            ->limit(10)
            ->get();
    }

    private function getRevenueByStaff($startDate)
    {
        return Staff::with('user')
            ->withSum(['invoices as total_revenue' => function($query) use ($startDate) {
                $query->where('invoice_date', '>=', $startDate);
            }], 'total_amount')
            ->where('is_active', true)
            ->orderBy('total_revenue', 'desc')
            ->get();
    }

    private function getAppointmentCompletionRate($startDate)
    {
        $total = Appointment::where('appointment_date', '>=', $startDate)->count();
        $completed = Appointment::where('appointment_date', '>=', $startDate)
            ->where('status', 'completed')
            ->count();

        return $total > 0 ? round(($completed / $total) * 100, 2) : 0;
    }

    private function getPatientRetentionRate($startDate)
    {
        $newPatients = Patient::where('created_at', '>=', $startDate)->count();
        $returningPatients = Patient::whereHas('appointments', function($query) use ($startDate) {
            $query->where('appointment_date', '>=', $startDate);
        })->where('created_at', '<', $startDate)->count();

        $total = $newPatients + $returningPatients;
        return $total > 0 ? round(($returningPatients / $total) * 100, 2) : 0;
    }

    private function getAverageRevenuePerPatient($startDate)
    {
        $totalRevenue = Payment::where('payment_date', '>=', $startDate)
            ->where('status', 'completed')
            ->sum('amount');
        
        $uniquePatients = Payment::where('payment_date', '>=', $startDate)
            ->where('status', 'completed')
            ->distinct('patient_id')
            ->count();

        return $uniquePatients > 0 ? round($totalRevenue / $uniquePatients, 2) : 0;
    }

    private function getLabWorkCompletionRate($startDate)
    {
        $total = LabWork::where('work_date', '>=', $startDate)->count();
        $completed = LabWork::where('work_date', '>=', $startDate)
            ->where('status', 'delivered')
            ->count();

        return $total > 0 ? round(($completed / $total) * 100, 2) : 0;
    }
}
