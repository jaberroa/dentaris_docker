<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\Appointment;
use App\Models\Staff;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\TreatmentPlan;
use App\Models\LabWork;
use App\Models\Product;
use App\Models\Inventory;
use App\Models\Supplier;
use App\Models\Purchase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function __construct()
    {
        // Middleware se aplica en las rutas, no en el constructor
    }

    public function index()
    {
        $user = auth()->user();
        
        // KPIs principales
        $kpis = $this->getMainKPIs();
        
        // Estadísticas de citas
        $appointmentStats = $this->getAppointmentStats();
        
        // Estadísticas financieras
        $financialStats = $this->getFinancialStats();
        
        // Estadísticas de inventario
        $inventoryStats = $this->getInventoryStats();
        
        // Citas del día
        $todayAppointments = $this->getTodayAppointments();
        
        // Próximas citas
        $upcomingAppointments = $this->getUpcomingAppointments();
        
        // Alertas y notificaciones
        $alerts = $this->getAlerts();
        
        // Gráficos de datos
        $charts = $this->getChartData();
        
        // Trabajos de laboratorio pendientes
        $pendingLabWorks = $this->getPendingLabWorks();
        
        // Productos con bajo stock
        $lowStockProducts = $this->getLowStockProducts();
        
        // Cotizaciones pendientes
        $pendingQuotes = $this->getPendingQuotes();

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

    private function getMainKPIs()
    {
        $today = Carbon::today();
        $thisMonth = Carbon::now()->startOfMonth();
        $lastMonth = Carbon::now()->subMonth()->startOfMonth();

        return [
            'total_patients' => Patient::count(),
            'total_staff' => Staff::count(),
            'today_appointments' => Appointment::whereDate('appointment_date', $today)->count(),
            'monthly_appointments' => Appointment::where('appointment_date', '>=', $thisMonth)->count(),
            'monthly_revenue' => Payment::where('payment_date', '>=', $thisMonth)
                ->where('status', 'completed')
                ->sum('amount'),
            'monthly_expenses' => Purchase::where('purchase_date', '>=', $thisMonth)
                ->where('status', 'received')
                ->sum('total_amount'),
            'pending_invoices' => Invoice::where('status', '!=', 'paid')->count(),
            'overdue_invoices' => Invoice::where('due_date', '<', $today)
                ->where('status', '!=', 'paid')
                ->count(),
            'active_treatment_plans' => TreatmentPlan::where('status', 'active')->count(),
            'pending_lab_works' => LabWork::whereIn('status', ['pending', 'sent', 'in_progress'])->count(),
            'low_stock_products' => Inventory::with('product')
                ->whereHas('product', function($query) {
                    $query->where('is_active', true);
                })
                ->whereRaw('available_stock <= (SELECT minimum_stock FROM products WHERE products.id = inventory.product_id)')
                ->count(),
            'total_suppliers' => Supplier::where('is_active', true)->count(),
        ];
    }

    private function getAppointmentStats()
    {
        $today = Carbon::today();
        $thisWeek = Carbon::now()->startOfWeek();
        $thisMonth = Carbon::now()->startOfMonth();

        return [
            'today' => [
                'total' => Appointment::whereDate('appointment_date', $today)->count(),
                'completed' => Appointment::whereDate('appointment_date', $today)
                    ->whereHas('status', function($query) {
                        $query->where('name', 'completed');
                    })->count(),
                'cancelled' => Appointment::whereDate('appointment_date', $today)
                    ->whereHas('status', function($query) {
                        $query->where('name', 'cancelled');
                    })->count(),
            ],
            'this_week' => [
                'total' => Appointment::where('appointment_date', '>=', $thisWeek)->count(),
                'completed' => Appointment::where('appointment_date', '>=', $thisWeek)
                    ->whereHas('status', function($query) {
                        $query->where('name', 'completed');
                    })->count(),
            ],
            'this_month' => [
                'total' => Appointment::where('appointment_date', '>=', $thisMonth)->count(),
                'completed' => Appointment::where('appointment_date', '>=', $thisMonth)
                    ->whereHas('status', function($query) {
                        $query->where('name', 'completed');
                    })->count(),
            ],
            'by_status' => Appointment::with('status')
                ->get()
                ->groupBy('status.name')
                ->map->count()
                ->toArray(),
        ];
    }

    private function getFinancialStats()
    {
        $today = Carbon::today();
        $thisMonth = Carbon::now()->startOfMonth();
        $lastMonth = Carbon::now()->subMonth()->startOfMonth();

        return [
            'daily_revenue' => Payment::whereDate('payment_date', $today)
                ->where('status', 'completed')
                ->sum('amount'),
            'monthly_revenue' => Payment::where('payment_date', '>=', $thisMonth)
                ->where('status', 'completed')
                ->sum('amount'),
            'last_month_revenue' => Payment::whereBetween('payment_date', [$lastMonth, $thisMonth])
                ->where('status', 'completed')
                ->sum('amount'),
            'pending_payments' => Payment::where('status', 'pending')->sum('amount'),
            'overdue_invoices' => Invoice::where('due_date', '<', $today)
                ->where('status', '!=', 'paid')
                ->sum('balance_due'),
            'monthly_expenses' => Purchase::where('purchase_date', '>=', $thisMonth)
                ->where('status', 'received')
                ->sum('total_amount'),
            'profit_margin' => $this->calculateProfitMargin($thisMonth),
        ];
    }

    private function getInventoryStats()
    {
        return [
            'total_products' => Product::where('is_active', true)->count(),
            'low_stock' => Inventory::with('product')
                ->whereHas('product', function($query) {
                    $query->where('is_active', true);
                })
                ->whereRaw('available_stock <= (SELECT minimum_stock FROM products WHERE products.id = inventory.product_id)')
                ->count(),
            'out_of_stock' => Inventory::where('available_stock', 0)->count(),
            'total_value' => Inventory::join('products', 'inventory.product_id', '=', 'products.id')
                ->where('products.is_active', true)
                ->sum(DB::raw('inventory.current_stock * inventory.average_cost')),
            'expiring_soon' => Product::where('expiry_date', '<=', Carbon::now()->addDays(30))
                ->where('expiry_date', '>', Carbon::now())
                ->count(),
            'expired' => Product::where('expiry_date', '<', Carbon::now())->count(),
        ];
    }

    private function getTodayAppointments()
    {
        $today = Carbon::today();
        
        return Appointment::with(['patient', 'staff.user', 'status'])
            ->whereDate('appointment_date', $today)
            ->orderBy('start_time')
            ->get();
    }

    private function getUpcomingAppointments()
    {
        $tomorrow = Carbon::tomorrow();
        $nextWeek = Carbon::now()->addWeek();
        
        return Appointment::with(['patient', 'staff.user', 'status'])
            ->whereBetween('appointment_date', [$tomorrow, $nextWeek])
            ->whereHas('status', function($query) {
                $query->whereNotIn('name', ['completed', 'cancelled']);
            })
            ->orderBy('appointment_date')
            ->orderBy('start_time')
            ->limit(10)
            ->get();
    }

    private function getAlerts()
    {
        $alerts = [];

        // Citas canceladas hoy
        $cancelledToday = Appointment::whereDate('appointment_date', Carbon::today())
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
        $overdueInvoices = Invoice::where('due_date', '<', Carbon::today())
            ->where('status', '!=', 'paid')
            ->count();
        
        if ($overdueInvoices > 0) {
            $alerts[] = [
                'type' => 'danger',
                'message' => "{$overdueInvoices} facturas vencidas",
                'icon' => 'alert',
            ];
        }

        // Productos con bajo stock
        $lowStockCount = Inventory::with('product')
            ->whereHas('product', function($query) {
                $query->where('is_active', true);
            })
            ->whereRaw('available_stock <= (SELECT minimum_stock FROM products WHERE products.id = inventory.product_id)')
            ->count();
        
        if ($lowStockCount > 0) {
            $alerts[] = [
                'type' => 'warning',
                'message' => "{$lowStockCount} productos con bajo stock",
                'icon' => 'inventory',
            ];
        }

        // Trabajos de laboratorio atrasados
        $overdueLabWorks = LabWork::where('expected_delivery', '<', Carbon::today())
            ->whereIn('status', ['pending', 'sent', 'in_progress'])
            ->count();
        
        if ($overdueLabWorks > 0) {
            $alerts[] = [
                'type' => 'danger',
                'message' => "{$overdueLabWorks} trabajos de laboratorio atrasados",
                'icon' => 'lab',
            ];
        }

        return $alerts;
    }

    private function getChartData()
    {
        // Citas por día de la semana (últimos 7 días)
        $appointmentsByDay = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $appointmentsByDay[$date->format('D')] = Appointment::whereDate('appointment_date', $date)->count();
        }

        // Ingresos por mes (últimos 6 meses)
        $revenueByMonth = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $revenueByMonth[$month->format('M Y')] = Payment::whereYear('payment_date', $month->year)
                ->whereMonth('payment_date', $month->month)
                ->where('status', 'completed')
                ->sum('amount');
        }

        // Citas por estado (incluyendo todos los estados)
        $appointmentsByStatus = Appointment::with('status')
            ->get()
            ->groupBy('status.name')
            ->map->count()
            ->toArray();

        // Productos por categoría
        $productsByCategory = Product::select('category', DB::raw('count(*) as count'))
            ->where('is_active', true)
            ->groupBy('category')
            ->pluck('count', 'category')
            ->toArray();

        return [
            'appointments_by_day' => $appointmentsByDay,
            'revenue_by_month' => $revenueByMonth,
            'appointments_by_status' => $appointmentsByStatus,
            'products_by_category' => $productsByCategory,
        ];
    }


    private function getPendingLabWorks()
    {
        return LabWork::with(['patient', 'dentalLab', 'items.prosthesis'])
            ->whereIn('status', ['pending', 'sent', 'in_progress'])
            ->orderBy('expected_delivery')
            ->limit(5)
            ->get();
    }

    private function getLowStockProducts()
    {
        return Product::with('inventory')
            ->where('is_active', true)
            ->whereHas('inventory', function($query) {
                $query->whereRaw('available_stock <= minimum_stock');
            })
            ->limit(5)
            ->get();
    }

    private function getPendingQuotes()
    {
        return \App\Models\Quote::with(['patient', 'staff.user'])
            ->where('status', 'pending')
            ->where('valid_until', '>=', Carbon::today())
            ->orderBy('valid_until')
            ->limit(5)
            ->get();
    }

    private function calculateProfitMargin($startDate)
    {
        $revenue = Payment::where('payment_date', '>=', $startDate)
            ->where('status', 'completed')
            ->sum('amount');
        
        $expenses = Purchase::where('purchase_date', '>=', $startDate)
            ->where('status', 'received')
            ->sum('total_amount');
        
        if ($revenue == 0) return 0;
        
        return round((($revenue - $expenses) / $revenue) * 100, 2);
    }

    public function getAppointmentData(Request $request)
    {
        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);
        
        $appointments = Appointment::with(['patient', 'staff.user'])
            ->whereBetween('appointment_date', [$startDate, $endDate])
            ->get();
        
        return response()->json($appointments);
    }

    public function getRevenueData(Request $request)
    {
        $period = $request->period ?? 'month'; // month, week, day
        
        switch ($period) {
            case 'day':
                $data = $this->getDailyRevenueData();
                break;
            case 'week':
                $data = $this->getWeeklyRevenueData();
                break;
            default:
                $data = $this->getMonthlyRevenueData();
        }
        
        return response()->json($data);
    }

    private function getDailyRevenueData()
    {
        $data = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $revenue = Payment::whereDate('payment_date', $date)
                ->where('status', 'completed')
                ->sum('amount');
            
            $data[] = [
                'date' => $date->format('d/m'),
                'revenue' => $revenue
            ];
        }
        
        return $data;
    }

    private function getWeeklyRevenueData()
    {
        $data = [];
        for ($i = 11; $i >= 0; $i--) {
            $week = Carbon::now()->subWeeks($i);
            $weekStart = $week->startOfWeek();
            $weekEnd = $week->copy()->endOfWeek();
            
            $revenue = Payment::whereBetween('payment_date', [$weekStart, $weekEnd])
                ->where('status', 'completed')
                ->sum('amount');
            
            $data[] = [
                'week' => $weekStart->format('d/m') . ' - ' . $weekEnd->format('d/m'),
                'revenue' => $revenue
            ];
        }
        
        return $data;
    }

    private function getMonthlyRevenueData()
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
}
