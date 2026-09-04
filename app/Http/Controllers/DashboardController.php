<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\Staff;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Inventory;
use App\Modules\Clinics\Data\ClinicContext;
use App\Modules\Clinics\Services\ClinicalRelatedRecordAccessService;
use App\Modules\Clinics\Services\ClinicOwnedDomainReadinessService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function __construct(
        private readonly ClinicalRelatedRecordAccessService $clinicalRecords,
        private readonly ClinicOwnedDomainReadinessService $domainReadiness,
    ) {
    }

    public function index(Request $request)
    {
        $context = $this->clinicalRecords->context($request);
        $inventoryReady = $this->domainReadiness->isReady('inventory');
        $billingReady = $this->domainReadiness->isReady('billing');
        
        // KPIs principales
        $kpis = $this->getMainKPIs($context, $inventoryReady, $billingReady);
        
        // Estadísticas de citas
        $appointmentStats = $this->getAppointmentStats($context);
        
        // Estadísticas financieras
        $financialStats = $this->getFinancialStats($context, $billingReady);
        
        // Estadísticas de inventario
        $inventoryStats = $this->getInventoryStats($context, $inventoryReady);
        
        // Citas del día
        $todayAppointments = $this->getTodayAppointments($context);
        
        // Próximas citas
        $upcomingAppointments = $this->getUpcomingAppointments($context);
        
        // Alertas y notificaciones
        $alerts = $this->getAlerts($context, $inventoryReady, $billingReady);
        
        // Gráficos de datos
        $charts = $this->getChartData($context, $inventoryReady, $billingReady);
        
        // Los módulos aún sin propiedad clínica no se agregan al tablero.
        $pendingLabWorks = collect();
        
        // Productos con bajo stock
        $lowStockProducts = $this->getLowStockProducts($context, $inventoryReady);
        
        $pendingQuotes = collect();

        return view('dashboard.index', compact(
            'kpis',
            'appointmentStats',
            'financialStats',
            'inventoryStats',
            'todayAppointments',
            'upcomingAppointments',
            'alerts',
            'charts',
            'pendingLabWorks',
            'lowStockProducts',
            'pendingQuotes'
        ));
    }

    private function getMainKPIs(ClinicContext $context, bool $inventoryReady, bool $billingReady): array
    {
        $today = Carbon::today();
        $thisMonth = Carbon::now()->startOfMonth();

        return [
            'total_patients' => Patient::forClinic($context)->count(),
            'total_staff' => Staff::forClinic($context)->count(),
            'today_appointments' => $this->clinicalRecords->appointments($context)->whereDate('appointment_date', $today)->count(),
            'monthly_appointments' => $this->clinicalRecords->appointments($context)->where('appointment_date', '>=', $thisMonth)->count(),
            'monthly_revenue' => $billingReady ? Payment::forClinic($context)->where('payment_date', '>=', $thisMonth)
                ->where('status', 'completed')
                ->sum('amount') : 0,
            'monthly_expenses' => 0,
            'pending_invoices' => $billingReady ? Invoice::forClinic($context)->where('status', '!=', 'paid')->count() : 0,
            'overdue_invoices' => $billingReady ? Invoice::forClinic($context)->where('due_date', '<', $today)
                ->where('status', '!=', 'paid')
                ->count() : 0,
            'active_treatment_plans' => 0,
            'pending_lab_works' => 0,
            'low_stock_products' => $inventoryReady ? Inventory::forClinic($context)->with('product')
                ->whereHas('product', function($query) {
                    $query->where('is_active', true);
                })
                ->whereRaw('available_stock <= (SELECT minimum_stock FROM products WHERE products.id = inventory.product_id)')
                ->count() : 0,
            'total_suppliers' => 0,
        ];
    }

    private function getAppointmentStats(ClinicContext $context): array
    {
        $today = Carbon::today();
        $thisWeek = Carbon::now()->startOfWeek();
        $thisMonth = Carbon::now()->startOfMonth();

        return [
            'today' => [
                'total' => $this->clinicalRecords->appointments($context)->whereDate('appointment_date', $today)->count(),
                'completed' => $this->clinicalRecords->appointments($context)->whereDate('appointment_date', $today)
                    ->whereHas('status', function($query) {
                        $query->where('name', 'completed');
                    })->count(),
                'cancelled' => $this->clinicalRecords->appointments($context)->whereDate('appointment_date', $today)
                    ->whereHas('status', function($query) {
                        $query->where('name', 'cancelled');
                    })->count(),
            ],
            'this_week' => [
                'total' => $this->clinicalRecords->appointments($context)->where('appointment_date', '>=', $thisWeek)->count(),
                'completed' => $this->clinicalRecords->appointments($context)->where('appointment_date', '>=', $thisWeek)
                    ->whereHas('status', function($query) {
                        $query->where('name', 'completed');
                    })->count(),
            ],
            'this_month' => [
                'total' => $this->clinicalRecords->appointments($context)->where('appointment_date', '>=', $thisMonth)->count(),
                'completed' => $this->clinicalRecords->appointments($context)->where('appointment_date', '>=', $thisMonth)
                    ->whereHas('status', function($query) {
                        $query->where('name', 'completed');
                    })->count(),
            ],
            'by_status' => $this->clinicalRecords->appointments($context)->with('status')
                ->get()
                ->groupBy('status.name')
                ->map->count()
                ->toArray(),
        ];
    }

    private function getFinancialStats(ClinicContext $context, bool $billingReady): array
    {
        if (! $billingReady) {
            return [
                'daily_revenue' => 0,
                'monthly_revenue' => 0,
                'last_month_revenue' => 0,
                'pending_payments' => 0,
                'overdue_invoices' => 0,
                'monthly_expenses' => 0,
                'profit_margin' => 0,
            ];
        }

        $today = Carbon::today();
        $thisMonth = Carbon::now()->startOfMonth();
        $lastMonth = Carbon::now()->subMonth()->startOfMonth();

        return [
            'daily_revenue' => Payment::forClinic($context)->whereDate('payment_date', $today)
                ->where('status', 'completed')
                ->sum('amount'),
            'monthly_revenue' => Payment::forClinic($context)->where('payment_date', '>=', $thisMonth)
                ->where('status', 'completed')
                ->sum('amount'),
            'last_month_revenue' => Payment::forClinic($context)->whereBetween('payment_date', [$lastMonth, $thisMonth])
                ->where('status', 'completed')
                ->sum('amount'),
            'pending_payments' => Payment::forClinic($context)->where('status', 'pending')->sum('amount'),
            'overdue_invoices' => Invoice::forClinic($context)->where('due_date', '<', $today)
                ->where('status', '!=', 'paid')
                ->sum('balance_due'),
            'monthly_expenses' => 0,
            'profit_margin' => 0,
        ];
    }

    private function getInventoryStats(ClinicContext $context, bool $inventoryReady): array
    {
        if (! $inventoryReady) {
            return [
                'total_products' => 0,
                'low_stock' => 0,
                'out_of_stock' => 0,
                'total_value' => 0,
                'expiring_soon' => 0,
                'expired' => 0,
            ];
        }

        return [
            'total_products' => Inventory::forClinic($context)->distinct()->count('product_id'),
            'low_stock' => Inventory::forClinic($context)->with('product')
                ->whereHas('product', function($query) {
                    $query->where('is_active', true);
                })
                ->whereRaw('available_stock <= (SELECT minimum_stock FROM products WHERE products.id = inventory.product_id)')
                ->count(),
            'out_of_stock' => Inventory::forClinic($context)->where('available_stock', 0)->count(),
            'total_value' => Inventory::forClinic($context)->join('products', 'inventory.product_id', '=', 'products.id')
                ->where('products.is_active', true)
                ->sum(DB::raw('inventory.current_stock * inventory.average_cost')),
            'expiring_soon' => Product::whereHas('inventories', fn ($query) => $query->forClinic($context))
                ->where('expiry_date', '<=', Carbon::now()->addDays(30))
                ->where('expiry_date', '>', Carbon::now())
                ->count(),
            'expired' => Product::whereHas('inventories', fn ($query) => $query->forClinic($context))
                ->where('expiry_date', '<', Carbon::now())->count(),
        ];
    }

    private function getTodayAppointments(ClinicContext $context)
    {
        $today = Carbon::today();
        
        return $this->clinicalRecords->appointments($context)->with(['patient', 'staff.user', 'status'])
            ->whereDate('appointment_date', $today)
            ->orderBy('start_time')
            ->get();
    }

    private function getUpcomingAppointments(ClinicContext $context)
    {
        $tomorrow = Carbon::tomorrow();
        $nextWeek = Carbon::now()->addWeek();
        
        return $this->clinicalRecords->appointments($context)->with(['patient', 'staff.user', 'status'])
            ->whereBetween('appointment_date', [$tomorrow, $nextWeek])
            ->whereHas('status', function($query) {
                $query->whereNotIn('name', ['completed', 'cancelled']);
            })
            ->orderBy('appointment_date')
            ->orderBy('start_time')
            ->limit(10)
            ->get();
    }

    private function getAlerts(ClinicContext $context, bool $inventoryReady, bool $billingReady): array
    {
        $alerts = [];

        // Citas canceladas hoy
        $cancelledToday = $this->clinicalRecords->appointments($context)->whereDate('appointment_date', Carbon::today())
            ->whereHas('status', function($query) {
                $query->where('name', 'cancelled');
            })
            ->count();
        
        if ($cancelledToday > 0) {
            $alerts[] = [
                'type' => 'warning',
                'message' => "{$cancelledToday} citas canceladas hoy",
                'icon' => 'warning',
            ];
        }

        // Facturas vencidas
        $overdueInvoices = $billingReady
            ? Invoice::forClinic($context)->where('due_date', '<', Carbon::today())
                ->where('status', '!=', 'paid')
                ->count()
            : 0;
        
        if ($overdueInvoices > 0) {
            $alerts[] = [
                'type' => 'danger',
                'message' => "{$overdueInvoices} facturas vencidas",
                'icon' => 'alert',
            ];
        }

        // Productos con bajo stock
        $lowStockCount = $inventoryReady
            ? Inventory::forClinic($context)->with('product')
                ->whereHas('product', function($query) {
                    $query->where('is_active', true);
                })
                ->whereRaw('available_stock <= (SELECT minimum_stock FROM products WHERE products.id = inventory.product_id)')
                ->count()
            : 0;
        
        if ($lowStockCount > 0) {
            $alerts[] = [
                'type' => 'warning',
                'message' => "{$lowStockCount} productos con bajo stock",
                'icon' => 'inventory',
            ];
        }

        return $alerts;
    }

    private function getChartData(ClinicContext $context, bool $inventoryReady, bool $billingReady): array
    {
        // Citas por día de la semana (últimos 7 días)
        $appointmentsByDay = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $appointmentsByDay[$date->format('D')] = $this->clinicalRecords->appointments($context)
                ->whereDate('appointment_date', $date)
                ->count();
        }

        // Ingresos por mes (últimos 6 meses)
        $revenueByMonth = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $revenueByMonth[$month->format('M Y')] = $billingReady
                ? Payment::forClinic($context)->whereYear('payment_date', $month->year)
                    ->whereMonth('payment_date', $month->month)
                    ->where('status', 'completed')
                    ->sum('amount')
                : 0;
        }

        // Citas por estado (incluyendo todos los estados)
        $appointmentsByStatus = $this->clinicalRecords->appointments($context)->with('status')
            ->get()
            ->groupBy('status.name')
            ->map->count()
            ->toArray();

        // Productos por categoría
        $productsByCategory = $inventoryReady
            ? Product::query()
                ->join('inventory', 'inventory.product_id', '=', 'products.id')
                ->where('inventory.clinic_id', $context->clinicId)
                ->where('products.is_active', true)
                ->select('products.category', DB::raw('count(distinct products.id) as count'))
                ->groupBy('products.category')
                ->pluck('count', 'products.category')
                ->toArray()
            : [];

        return [
            'appointments_by_day' => $appointmentsByDay,
            'revenue_by_month' => $revenueByMonth,
            'appointments_by_status' => $appointmentsByStatus,
            'products_by_category' => $productsByCategory,
        ];
    }


    private function getLowStockProducts(ClinicContext $context, bool $inventoryReady)
    {
        if (! $inventoryReady) {
            return collect();
        }

        return Product::with(['inventories' => fn ($query) => $query->forClinic($context)])
            ->where('is_active', true)
            ->whereHas('inventories', function($query) use ($context) {
                $query->forClinic($context)
                    ->whereRaw('available_stock <= (SELECT minimum_stock FROM products WHERE products.id = inventory.product_id)');
            })
            ->limit(5)
            ->get();
    }

    public function getAppointmentData(Request $request)
    {
        $context = $this->clinicalRecords->context($request);
        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);
        
        $appointments = $this->clinicalRecords->appointments($context)->with(['patient', 'staff.user'])
            ->whereBetween('appointment_date', [$startDate, $endDate])
            ->get();
        
        return response()->json($appointments);
    }

    public function getRevenueData(Request $request)
    {
        $context = $this->clinicalRecords->context($request);
        $period = $request->period ?? 'month'; // month, week, day
        
        switch ($period) {
            case 'day':
                $data = $this->getDailyRevenueData($context);
                break;
            case 'week':
                $data = $this->getWeeklyRevenueData($context);
                break;
            default:
                $data = $this->getMonthlyRevenueData($context);
        }
        
        return response()->json($data);
    }

    private function getDailyRevenueData(ClinicContext $context): array
    {
        $data = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $revenue = Payment::forClinic($context)->whereDate('payment_date', $date)
                ->where('status', 'completed')
                ->sum('amount');
            
            $data[] = [
                'date' => $date->format('d/m'),
                'revenue' => $revenue
            ];
        }
        
        return $data;
    }

    private function getWeeklyRevenueData(ClinicContext $context): array
    {
        $data = [];
        for ($i = 11; $i >= 0; $i--) {
            $week = Carbon::now()->subWeeks($i);
            $weekStart = $week->startOfWeek();
            $weekEnd = $week->copy()->endOfWeek();
            
            $revenue = Payment::forClinic($context)->whereBetween('payment_date', [$weekStart, $weekEnd])
                ->where('status', 'completed')
                ->sum('amount');
            
            $data[] = [
                'week' => $weekStart->format('d/m') . ' - ' . $weekEnd->format('d/m'),
                'revenue' => $revenue
            ];
        }
        
        return $data;
    }

    private function getMonthlyRevenueData(ClinicContext $context): array
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
}
