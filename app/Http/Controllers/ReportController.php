<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Inventory;
use App\Models\Staff;
use App\Modules\Clinics\Data\ClinicContext;
use App\Modules\Clinics\Services\ClinicalRelatedRecordAccessService;
use App\Modules\Clinics\Services\ClinicOwnedDomainReadinessService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function __construct(
        private readonly ClinicalRelatedRecordAccessService $clinicalRecords,
        private readonly ClinicOwnedDomainReadinessService $domainReadiness,
    ) {
    }

    public function index(Request $request)
    {
        $this->clinicalRecords->context($request);

        return view('reports.index');
    }

    public function financial(Request $request)
    {
        $context = $this->clinicalRecords->context($request);
        $period = $request->input('period', 'month');
        $startDate = $this->getStartDate($period);
        
        $stats = [
            'total_revenue' => Payment::forClinic($context)->where('payment_date', '>=', $startDate)
                ->where('status', 'completed')
                ->sum('amount'),
            'total_invoices' => Invoice::forClinic($context)->where('invoice_date', '>=', $startDate)->count(),
            'paid_invoices' => Invoice::forClinic($context)->where('invoice_date', '>=', $startDate)
                ->where('status', 'paid')
                ->count(),
            'pending_invoices' => Invoice::forClinic($context)->where('invoice_date', '>=', $startDate)
                ->where('status', '!=', 'paid')
                ->count(),
        ];

        $revenueByMonth = $this->getRevenueByMonth($context);
        $paymentMethods = $this->getPaymentMethodsStats($context, $startDate);

        return view('reports.financial', compact('stats', 'revenueByMonth', 'paymentMethods'));
    }

    public function appointments(Request $request)
    {
        $context = $this->clinicalRecords->context($request);
        $period = $request->input('period', 'month');
        $startDate = $this->getStartDate($period);

        $stats = [
            'total_appointments' => $this->clinicalRecords->appointments($context)->where('appointment_date', '>=', $startDate)->count(),
            'completed_appointments' => $this->clinicalRecords->appointments($context)->where('appointment_date', '>=', $startDate)
                ->whereHas('status', fn ($query) => $query->where('name', 'completed'))
                ->count(),
            'cancelled_appointments' => $this->clinicalRecords->appointments($context)->where('appointment_date', '>=', $startDate)
                ->whereHas('status', fn ($query) => $query->where('name', 'cancelled'))
                ->count(),
        ];

        $appointmentsByStatus = $this->getAppointmentsByStatus($context, $startDate);
        $appointmentsByStaff = $this->getAppointmentsByStaff($context, $startDate);

        return view('reports.appointments', compact('stats', 'appointmentsByStatus', 'appointmentsByStaff'));
    }

    public function patients(Request $request)
    {
        $context = $this->clinicalRecords->context($request);
        $period = $request->input('period', 'month');
        $startDate = $this->getStartDate($period);

        $stats = [
            'total_patients' => Patient::forClinic($context)->count(),
            'new_patients' => Patient::forClinic($context)->where('created_at', '>=', $startDate)->count(),
            'active_patients' => Patient::forClinic($context)->whereHas('appointments', function($query) use ($context, $startDate) {
                $query->where('appointment_date', '>=', $startDate)
                    ->whereHas('staff', fn ($staffQuery) => $staffQuery->forClinic($context));
            })->count(),
        ];

        $patientsByAge = $this->getPatientsByAge($context);
        $patientsByGender = $this->getPatientsByGender($context);

        return view('reports.patients', compact('stats', 'patientsByAge', 'patientsByGender'));
    }

    public function inventory(Request $request)
    {
        $context = $this->clinicalRecords->context($request);
        $stats = [
            'total_products' => Inventory::forClinic($context)->distinct()->count('product_id'),
            'low_stock_products' => Inventory::forClinic($context)->with('product')
                ->whereHas('product', function($query) {
                    $query->where('is_active', true);
                })
                ->whereRaw('available_stock <= (SELECT minimum_stock FROM products WHERE products.id = inventory.product_id)')
                ->count(),
            'out_of_stock_products' => Inventory::forClinic($context)->where('available_stock', 0)->count(),
            'total_inventory_value' => Inventory::forClinic($context)->join('products', 'inventory.product_id', '=', 'products.id')
                ->where('products.is_active', true)
                ->sum(DB::raw('current_stock * average_cost')),
        ];

        $productsByCategory = $this->getProductsByCategory($context);
        $lowStockProducts = $this->getLowStockProducts($context);

        return view('reports.inventory', compact('stats', 'productsByCategory', 'lowStockProducts'));
    }

    public function staff(Request $request)
    {
        $context = $this->clinicalRecords->context($request);
        $period = $request->input('period', 'month');
        $startDate = $this->getStartDate($period);

        $stats = [
            'total_staff' => Staff::forClinic($context)->where('is_active', true)->count(),
            'active_staff' => Staff::forClinic($context)->where('is_active', true)
                ->whereHas('appointments', function($query) use ($context, $startDate) {
                    $query->where('appointment_date', '>=', $startDate)
                        ->whereHas('patient', fn ($patientQuery) => $patientQuery->forClinic($context));
                })->count(),
        ];

        $appointmentsByStaff = $this->getAppointmentsByStaff($context, $startDate);
        $revenueByStaff = $this->domainReadiness->isReady('billing')
            ? $this->getRevenueByStaff($context, $startDate)
            : collect();

        return view('reports.staff', compact('stats', 'appointmentsByStaff', 'revenueByStaff'));
    }

    public function kpis(Request $request)
    {
        $context = $this->clinicalRecords->context($request);
        $period = $request->input('period', 'month');
        $startDate = $this->getStartDate($period);
        $billingReady = $this->domainReadiness->isReady('billing');

        $kpis = [
            'appointment_completion_rate' => $this->getAppointmentCompletionRate($context, $startDate),
            'patient_retention_rate' => $this->getPatientRetentionRate($context, $startDate),
            'average_revenue_per_patient' => $billingReady
                ? $this->getAverageRevenuePerPatient($context, $startDate)
                : 0,
            'lab_work_completion_rate' => 0,
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

    private function getRevenueByMonth(ClinicContext $context): array
    {
        $data = [];
        for ($i = 11; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $revenue = Payment::forClinic($context)->whereYear('payment_date', $month->year)
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

    private function getPaymentMethodsStats(ClinicContext $context, $startDate)
    {
        return Payment::forClinic($context)->select('payment_method', DB::raw('count(*) as count'), DB::raw('sum(amount) as total'))
            ->where('payment_date', '>=', $startDate)
            ->where('status', 'completed')
            ->groupBy('payment_method')
            ->get();
    }

    private function getAppointmentsByStatus(ClinicContext $context, $startDate)
    {
        return $this->clinicalRecords->appointments($context)
            ->join('appointment_statuses', 'appointment_statuses.id', '=', 'appointments.appointment_status_id')
            ->select('appointment_statuses.name as status', DB::raw('count(*) as count'))
            ->where('appointment_date', '>=', $startDate)
            ->groupBy('appointment_statuses.name')
            ->get();
    }

    private function getAppointmentsByStaff(ClinicContext $context, $startDate)
    {
        return Staff::forClinic($context)->with('user')
            ->withCount(['appointments' => function($query) use ($context, $startDate) {
                $query->where('appointment_date', '>=', $startDate)
                    ->whereHas('patient', fn ($patientQuery) => $patientQuery->forClinic($context));
            }])
            ->where('is_active', true)
            ->orderBy('appointments_count', 'desc')
            ->get();
    }

    private function getPatientsByAge(ClinicContext $context)
    {
        return Patient::forClinic($context)
            ->whereNotNull('birth_date')
            ->get(['birth_date'])
            ->groupBy(function (Patient $patient): string {
                $age = $patient->birth_date->age;

                return match (true) {
                    $age < 18 => '0-17',
                    $age <= 30 => '18-30',
                    $age <= 45 => '31-45',
                    $age <= 60 => '46-60',
                    default => '60+',
                };
            })
            ->map(fn ($patients, $ageGroup) => (object) [
                'age_group' => $ageGroup,
                'count' => $patients->count(),
            ])
            ->values();
    }

    private function getPatientsByGender(ClinicContext $context)
    {
        return Patient::forClinic($context)->select('gender', DB::raw('count(*) as count'))
            ->groupBy('gender')
            ->get();
    }

    private function getProductsByCategory(ClinicContext $context)
    {
        return Product::query()
            ->join('inventory', 'inventory.product_id', '=', 'products.id')
            ->where('inventory.clinic_id', $context->clinicId)
            ->select('products.category', DB::raw('count(distinct products.id) as count'))
            ->where('products.is_active', true)
            ->groupBy('products.category')
            ->get();
    }

    private function getLowStockProducts(ClinicContext $context)
    {
        return Product::with(['inventories' => fn ($query) => $query->forClinic($context)])
            ->where('is_active', true)
            ->whereHas('inventories', function($query) use ($context) {
                $query->forClinic($context)
                    ->whereRaw('available_stock <= (SELECT minimum_stock FROM products WHERE products.id = inventory.product_id)');
            })
            ->limit(10)
            ->get();
    }

    private function getRevenueByStaff(ClinicContext $context, $startDate)
    {
        return Staff::forClinic($context)->with('user')
            ->withSum(['invoices as total_revenue' => function($query) use ($context, $startDate) {
                $query->forClinic($context)->where('invoice_date', '>=', $startDate);
            }], 'total_amount')
            ->where('is_active', true)
            ->orderBy('total_revenue', 'desc')
            ->get();
    }

    private function getAppointmentCompletionRate(ClinicContext $context, $startDate): float
    {
        $total = $this->clinicalRecords->appointments($context)->where('appointment_date', '>=', $startDate)->count();
        $completed = $this->clinicalRecords->appointments($context)->where('appointment_date', '>=', $startDate)
            ->whereHas('status', fn ($query) => $query->where('name', 'completed'))
            ->count();

        return $total > 0 ? round(($completed / $total) * 100, 2) : 0;
    }

    private function getPatientRetentionRate(ClinicContext $context, $startDate): float
    {
        $newPatients = Patient::forClinic($context)->where('created_at', '>=', $startDate)->count();
        $returningPatients = Patient::forClinic($context)->whereHas('appointments', function($query) use ($context, $startDate) {
            $query->where('appointment_date', '>=', $startDate)
                ->whereHas('staff', fn ($staffQuery) => $staffQuery->forClinic($context));
        })->where('created_at', '<', $startDate)->count();

        $total = $newPatients + $returningPatients;
        return $total > 0 ? round(($returningPatients / $total) * 100, 2) : 0;
    }

    private function getAverageRevenuePerPatient(ClinicContext $context, $startDate): float
    {
        $totalRevenue = Payment::forClinic($context)->where('payment_date', '>=', $startDate)
            ->where('status', 'completed')
            ->sum('amount');
        
        $uniquePatients = Payment::forClinic($context)->where('payment_date', '>=', $startDate)
            ->where('status', 'completed')
            ->distinct('patient_id')
            ->count();

        return $uniquePatients > 0 ? round($totalRevenue / $uniquePatients, 2) : 0;
    }
}
