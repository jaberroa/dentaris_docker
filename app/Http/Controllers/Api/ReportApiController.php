<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;
use App\Services\ReportService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;

class ReportApiController extends Controller
{
    use ApiResponse;

    protected $reportService;

    public function __construct(ReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    /**
     * Get financial report.
     */
    public function financial(Request $request): JsonResponse
    {
        try {
            $dateFrom = $request->get('date_from', now()->startOfMonth());
            $dateTo = $request->get('date_to', now()->endOfMonth());

            $report = $this->reportService->generateFinancialSummaryReport(
                Carbon::parse($dateFrom),
                Carbon::parse($dateTo)
            );

            return $this->successResponse($report, 'Financial report generated successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Error generating financial report: ' . $e->getMessage());
        }
    }

    /**
     * Get appointments report.
     */
    public function appointments(Request $request): JsonResponse
    {
        try {
            $dateFrom = $request->get('date_from', now()->startOfMonth());
            $dateTo = $request->get('date_to', now()->endOfMonth());
            $staffId = $request->get('staff_id');

            $report = $this->reportService->generateAppointmentReport(
                Carbon::parse($dateFrom),
                Carbon::parse($dateTo),
                $staffId
            );

            return $this->successResponse($report, 'Appointments report generated successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Error generating appointments report: ' . $e->getMessage());
        }
    }

    /**
     * Get patients report.
     */
    public function patients(): JsonResponse
    {
        try {
            $report = $this->reportService->generatePatientDemographicsReport();

            return $this->successResponse($report, 'Patients report generated successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Error generating patients report: ' . $e->getMessage());
        }
    }

    /**
     * Get inventory report.
     */
    public function inventory(): JsonResponse
    {
        try {
            $report = $this->reportService->generateInventoryStockReport();

            return $this->successResponse($report, 'Inventory report generated successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Error generating inventory report: ' . $e->getMessage());
        }
    }

    /**
     * Get staff report.
     */
    public function staff(Request $request): JsonResponse
    {
        try {
            $dateFrom = $request->get('date_from', now()->startOfMonth());
            $dateTo = $request->get('date_to', now()->endOfMonth());

            $report = [
                'total_staff' => \App\Models\Staff::count(),
                'active_staff' => \App\Models\Staff::where('is_active', true)->count(),
                'appointments_by_staff' => \App\Models\Appointment::whereBetween('appointment_date', [$dateFrom, $dateTo])
                    ->join('staff', 'appointments.staff_id', '=', 'staff.id')
                    ->join('users', 'staff.user_id', '=', 'users.id')
                    ->selectRaw('users.name, COUNT(*) as appointments_count')
                    ->groupBy('users.name')
                    ->get(),
            ];

            return $this->successResponse($report, 'Staff report generated successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Error generating staff report: ' . $e->getMessage());
        }
    }

    /**
     * Get KPIs report.
     */
    public function kpis(Request $request): JsonResponse
    {
        try {
            $dateFrom = $request->get('date_from', now()->startOfMonth());
            $dateTo = $request->get('date_to', now()->endOfMonth());

            $kpis = [
                'patients' => [
                    'total' => \App\Models\Patient::count(),
                    'new_this_period' => \App\Models\Patient::whereBetween('created_at', [$dateFrom, $dateTo])->count(),
                ],
                'appointments' => [
                    'total' => \App\Models\Appointment::whereBetween('appointment_date', [$dateFrom, $dateTo])->count(),
                    'completed' => \App\Models\Appointment::whereBetween('appointment_date', [$dateFrom, $dateTo])
                        ->whereHas('appointmentStatus', function ($q) {
                            $q->where('name', 'completed');
                        })->count(),
                ],
                'revenue' => [
                    'total' => \App\Models\Payment::whereBetween('payment_date', [$dateFrom, $dateTo])
                        ->where('status', 'completed')
                        ->sum('amount'),
                    'pending' => \App\Models\Invoice::where('status', 'sent')
                        ->where('balance_due', '>', 0)
                        ->sum('balance_due'),
                ],
            ];

            return $this->successResponse($kpis, 'KPIs report generated successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Error generating KPIs report: ' . $e->getMessage());
        }
    }

    /**
     * Export report to PDF/Excel.
     */
    public function export(Request $request): JsonResponse
    {
        try {
            $reportType = $request->get('type');
            $format = $request->get('format', 'pdf');
            $dateFrom = $request->get('date_from', now()->startOfMonth());
            $dateTo = $request->get('date_to', now()->endOfMonth());

            // Aquí se implementaría la lógica de exportación
            // Por ahora retornamos un mensaje de éxito

            return $this->successResponse([
                'message' => 'Report export initiated',
                'type' => $reportType,
                'format' => $format,
                'date_range' => "{$dateFrom} to {$dateTo}",
            ], 'Report export initiated successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Error exporting report: ' . $e->getMessage());
        }
    }
}