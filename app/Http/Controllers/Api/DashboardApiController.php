<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;
use App\Services\CacheService;
use App\Models\Appointment;
use App\Models\Patient;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Inventory;
use App\Modules\Clinics\Data\ClinicContext;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardApiController extends Controller
{
    use ApiResponse;

    protected $cacheService;

    public function __construct(CacheService $cacheService)
    {
        $this->cacheService = $cacheService;
    }

    /**
     * Get dashboard KPIs.
     */
    public function getKpis(Request $request): JsonResponse
    {
        $context = $request->attributes->get(ClinicContext::class)
            ?? $request->attributes->get('clinic.context');

        if (! $context instanceof ClinicContext) {
            return response()->json([
                'message' => 'El contexto clínico no está disponible.',
                'code' => 'CLINIC_CONTEXT_UNAVAILABLE',
            ], 403);
        }

        try {
            $dateFrom = $request->get('date_from', now()->startOfMonth());
            $dateTo = $request->get('date_to', now()->endOfMonth());

            // Usar cache para KPIs
            $kpis = $this->cacheService->getDashboardKpis($context, $dateFrom, $dateTo);

            return $this->successResponse($kpis, 'KPIs retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Error retrieving KPIs: ' . $e->getMessage());
        }
    }

    /**
     * Get appointments data for dashboard.
     */
    public function getAppointments(Request $request): JsonResponse
    {
        try {
            $dateFrom = $request->get('date_from', now()->startOfWeek());
            $dateTo = $request->get('date_to', now()->endOfWeek());

            $appointments = Appointment::with(['patient', 'staff.user', 'appointmentStatus'])
                ->whereBetween('appointment_date', [$dateFrom, $dateTo])
                ->orderBy('appointment_date')
                ->orderBy('start_time')
                ->get();

            // Agrupar por fecha
            $appointmentsByDate = $appointments->groupBy(function ($appointment) {
                return $appointment->appointment_date->format('Y-m-d');
            });

            return $this->successResponse($appointmentsByDate, 'Appointments data retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Error retrieving appointments data: ' . $e->getMessage());
        }
    }

    /**
     * Get revenue data for dashboard.
     */
    public function getRevenue(Request $request): JsonResponse
    {
        try {
            $dateFrom = $request->get('date_from', now()->startOfMonth());
            $dateTo = $request->get('date_to', now()->endOfMonth());

            // Revenue por día
            $dailyRevenue = Payment::whereBetween('payment_date', [$dateFrom, $dateTo])
                ->where('status', 'completed')
                ->selectRaw('DATE(payment_date) as date, SUM(amount) as total')
                ->groupBy('date')
                ->orderBy('date')
                ->get();

            // Revenue por método de pago
            $revenueByMethod = Payment::whereBetween('payment_date', [$dateFrom, $dateTo])
                ->where('status', 'completed')
                ->selectRaw('payment_method, SUM(amount) as total')
                ->groupBy('payment_method')
                ->get();

            // Revenue por mes (últimos 12 meses)
            $monthlyRevenue = Payment::where('payment_date', '>=', now()->subMonths(12))
                ->where('status', 'completed')
                ->selectRaw('YEAR(payment_date) as year, MONTH(payment_date) as month, SUM(amount) as total')
                ->groupBy('year', 'month')
                ->orderBy('year')
                ->orderBy('month')
                ->get();

            $data = [
                'daily_revenue' => $dailyRevenue,
                'revenue_by_method' => $revenueByMethod,
                'monthly_revenue' => $monthlyRevenue,
                'total_revenue' => $dailyRevenue->sum('total'),
            ];

            return $this->successResponse($data, 'Revenue data retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Error retrieving revenue data: ' . $e->getMessage());
        }
    }

    /**
     * Get alerts for dashboard.
     */
    public function getAlerts(): JsonResponse
    {
        try {
            $alerts = [];

            // Alertas de inventario
            $lowStockProducts = Product::whereHas('inventory', function ($q) {
                $q->whereColumn('current_stock', '<=', 'products.minimum_stock')
                  ->where('current_stock', '>', 0);
            })->with('inventory')->get();

            foreach ($lowStockProducts as $product) {
                $alerts[] = [
                    'type' => 'inventory',
                    'level' => 'warning',
                    'title' => 'Stock Bajo',
                    'message' => "El producto {$product->name} tiene stock bajo ({$product->inventory->current_stock})",
                    'product_id' => $product->id,
                ];
            }

            // Productos agotados
            $outOfStockProducts = Product::whereHas('inventory', function ($q) {
                $q->where('current_stock', 0);
            })->with('inventory')->get();

            foreach ($outOfStockProducts as $product) {
                $alerts[] = [
                    'type' => 'inventory',
                    'level' => 'danger',
                    'title' => 'Stock Agotado',
                    'message' => "El producto {$product->name} está agotado",
                    'product_id' => $product->id,
                ];
            }

            // Citas próximas (próximas 2 horas)
            $upcomingAppointments = Appointment::with(['patient', 'staff.user'])
                ->where('appointment_date', now()->toDateString())
                ->whereBetween('start_time', [now()->format('H:i'), now()->addHours(2)->format('H:i')])
                ->whereHas('appointmentStatus', function ($q) {
                    $q->where('name', 'scheduled');
                })
                ->get();

            foreach ($upcomingAppointments as $appointment) {
                $alerts[] = [
                    'type' => 'appointment',
                    'level' => 'info',
                    'title' => 'Cita Próxima',
                    'message' => "Cita con {$appointment->patient->first_name} {$appointment->patient->last_name} a las {$appointment->start_time->format('H:i')}",
                    'appointment_id' => $appointment->id,
                ];
            }

            // Pagos vencidos
            $overdueInvoices = Invoice::with('patient')
                ->where('status', 'sent')
                ->where('due_date', '<', now())
                ->where('balance_due', '>', 0)
                ->get();

            foreach ($overdueInvoices as $invoice) {
                $alerts[] = [
                    'type' => 'payment',
                    'level' => 'warning',
                    'title' => 'Pago Vencido',
                    'message' => "Factura {$invoice->invoice_number} de {$invoice->patient->first_name} {$invoice->patient->last_name} vencida",
                    'invoice_id' => $invoice->id,
                ];
            }

            return $this->successResponse($alerts, 'Alerts retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Error retrieving alerts: ' . $e->getMessage());
        }
    }

    /**
     * Get recent activity for dashboard.
     */
    public function getRecentActivity(Request $request): JsonResponse
    {
        try {
            $limit = $request->get('limit', 10);

            $activities = [];

            // Citas recientes
            $recentAppointments = Appointment::with(['patient', 'staff.user', 'appointmentStatus'])
                ->orderBy('created_at', 'desc')
                ->limit($limit)
                ->get();

            foreach ($recentAppointments as $appointment) {
                $activities[] = [
                    'type' => 'appointment',
                    'action' => 'created',
                    'description' => "Nueva cita creada para {$appointment->patient->first_name} {$appointment->patient->last_name}",
                    'date' => $appointment->created_at,
                    'user' => $appointment->staff->user->name,
                ];
            }

            // Pacientes nuevos
            $newPatients = Patient::orderBy('created_at', 'desc')
                ->limit($limit)
                ->get();

            foreach ($newPatients as $patient) {
                $activities[] = [
                    'type' => 'patient',
                    'action' => 'created',
                    'description' => "Nuevo paciente registrado: {$patient->first_name} {$patient->last_name}",
                    'date' => $patient->created_at,
                ];
            }

            // Ordenar por fecha
            usort($activities, function ($a, $b) {
                return $b['date'] <=> $a['date'];
            });

            return $this->successResponse(array_slice($activities, 0, $limit), 'Recent activity retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Error retrieving recent activity: ' . $e->getMessage());
        }
    }

    /**
     * Get statistics for charts.
     */
    public function getChartData(Request $request): JsonResponse
    {
        try {
            $dateFrom = $request->get('date_from', now()->startOfMonth());
            $dateTo = $request->get('date_to', now()->endOfMonth());

            $data = [
                'appointments_by_status' => Appointment::whereBetween('appointment_date', [$dateFrom, $dateTo])
                    ->join('appointment_statuses', 'appointments.appointment_status_id', '=', 'appointment_statuses.id')
                    ->selectRaw('appointment_statuses.name, COUNT(*) as count')
                    ->groupBy('appointment_statuses.name')
                    ->get(),
                'patients_by_gender' => Patient::selectRaw('gender, COUNT(*) as count')
                    ->groupBy('gender')
                    ->get(),
                'revenue_by_method' => Payment::whereBetween('payment_date', [$dateFrom, $dateTo])
                    ->where('status', 'completed')
                    ->selectRaw('payment_method, SUM(amount) as total')
                    ->groupBy('payment_method')
                    ->get(),
                'appointments_by_staff' => Appointment::whereBetween('appointment_date', [$dateFrom, $dateTo])
                    ->join('staff', 'appointments.staff_id', '=', 'staff.id')
                    ->join('users', 'staff.user_id', '=', 'users.id')
                    ->selectRaw('users.name, COUNT(*) as count')
                    ->groupBy('users.name')
                    ->get(),
            ];

            return $this->successResponse($data, 'Chart data retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Error retrieving chart data: ' . $e->getMessage());
        }
    }
}
