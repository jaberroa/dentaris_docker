<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use App\Models\Patient;
use App\Models\Inventory;
use App\Models\Invoice;
use App\Models\Payment;
use App\Modules\Clinics\Data\ClinicContext;
use App\Modules\Clinics\Services\ClinicalRelatedRecordAccessService;
use Carbon\Carbon;

class CacheService
{
    public function __construct(
        private readonly ClinicalRelatedRecordAccessService $clinicalRecords,
    ) {
    }

    /**
     * Cache duration in minutes
     */
    const CACHE_DURATION = 60;

    /**
     * Get dashboard KPIs with cache
     */
    public function getDashboardKpis(ClinicContext $context, $dateFrom = null, $dateTo = null)
    {
        $cacheKey = $this->clinicCacheKey('dashboard_kpis', $context, $dateFrom, $dateTo);

        return Cache::remember($cacheKey, self::CACHE_DURATION, function () use ($context, $dateFrom, $dateTo) {
            $dateFrom = $dateFrom ?? now()->startOfMonth();
            $dateTo = $dateTo ?? now()->endOfMonth();

            return [
                'patients' => [
                    'total' => Patient::forClinic($context)->count(),
                    'new_this_month' => Patient::forClinic($context)->whereMonth('created_at', now()->month)->count(),
                    'active' => Patient::forClinic($context)->where('is_active', true)->count(),
                ],
                'appointments' => [
                    'total' => $this->clinicalRecords->appointments($context)->whereBetween('appointment_date', [$dateFrom, $dateTo])->count(),
                    'completed' => $this->clinicalRecords->appointments($context)->whereBetween('appointment_date', [$dateFrom, $dateTo])
                        ->whereHas('status', function ($q) {
                            $q->where('name', 'completed');
                        })->count(),
                ],
                'revenue' => [
                    'total' => Payment::forClinic($context)->whereBetween('payment_date', [$dateFrom, $dateTo])
                        ->where('status', 'completed')
                        ->sum('amount'),
                    'pending' => Invoice::forClinic($context)->where('status', 'sent')
                        ->where('balance_due', '>', 0)
                        ->sum('balance_due'),
                ],
            ];
        });
    }

    /**
     * Get patient statistics with cache
     */
    public function getPatientStatistics(ClinicContext $context)
    {
        return Cache::remember($this->clinicCacheKey('patient_statistics', $context), self::CACHE_DURATION, function () use ($context) {
            return [
                'total_patients' => Patient::forClinic($context)->count(),
                'active_patients' => Patient::forClinic($context)->where('is_active', true)->count(),
                'new_patients_this_month' => Patient::forClinic($context)->whereMonth('created_at', now()->month)->count(),
                'patients_by_gender' => Patient::forClinic($context)->selectRaw('gender, COUNT(*) as count')
                    ->groupBy('gender')
                    ->get()
                    ->pluck('count', 'gender'),
            ];
        });
    }

    /**
     * Get inventory statistics with cache
     */
    public function getInventoryStatistics(ClinicContext $context)
    {
        return Cache::remember($this->clinicCacheKey('inventory_statistics', $context), self::CACHE_DURATION, function () use ($context) {
            return [
                'total_products' => Inventory::forClinic($context)->distinct()->count('product_id'),
                'low_stock_count' => Inventory::forClinic($context)
                    ->join('products', 'inventory.product_id', '=', 'products.id')
                    ->whereColumn('inventory.current_stock', '<=', 'products.minimum_stock')
                    ->count(),
                'out_of_stock_count' => Inventory::forClinic($context)->where('current_stock', 0)->count(),
            ];
        });
    }

    /**
     * Get appointment statistics with cache
     */
    public function getAppointmentStatistics(ClinicContext $context, $dateFrom = null, $dateTo = null)
    {
        $cacheKey = $this->clinicCacheKey('appointment_statistics', $context, $dateFrom, $dateTo);

        return Cache::remember($cacheKey, self::CACHE_DURATION, function () use ($context, $dateFrom, $dateTo) {
            $dateFrom = $dateFrom ?? now()->startOfMonth();
            $dateTo = $dateTo ?? now()->endOfMonth();

            return [
                'total_appointments' => $this->clinicalRecords->appointments($context)->whereBetween('appointment_date', [$dateFrom, $dateTo])->count(),
                'completed_appointments' => $this->clinicalRecords->appointments($context)->whereBetween('appointment_date', [$dateFrom, $dateTo])
                    ->whereHas('status', function ($q) {
                        $q->where('name', 'completed');
                    })->count(),
                'cancelled_appointments' => $this->clinicalRecords->appointments($context)->whereBetween('appointment_date', [$dateFrom, $dateTo])
                    ->whereHas('status', function ($q) {
                        $q->where('name', 'cancelled');
                    })->count(),
            ];
        });
    }

    /**
     * Get revenue data with cache
     */
    public function getRevenueData(ClinicContext $context, $dateFrom = null, $dateTo = null)
    {
        $cacheKey = $this->clinicCacheKey('revenue_data', $context, $dateFrom, $dateTo);

        return Cache::remember($cacheKey, self::CACHE_DURATION, function () use ($context, $dateFrom, $dateTo) {
            $dateFrom = $dateFrom ?? now()->startOfMonth();
            $dateTo = $dateTo ?? now()->endOfMonth();

            return [
                'daily_revenue' => Payment::forClinic($context)->whereBetween('payment_date', [$dateFrom, $dateTo])
                    ->where('status', 'completed')
                    ->selectRaw('DATE(payment_date) as date, SUM(amount) as total')
                    ->groupBy('date')
                    ->orderBy('date')
                    ->get(),
                'revenue_by_method' => Payment::forClinic($context)->whereBetween('payment_date', [$dateFrom, $dateTo])
                    ->where('status', 'completed')
                    ->selectRaw('payment_method, SUM(amount) as total')
                    ->groupBy('payment_method')
                    ->get(),
                'total_revenue' => Payment::forClinic($context)->whereBetween('payment_date', [$dateFrom, $dateTo])
                    ->where('status', 'completed')
                    ->sum('amount'),
            ];
        });
    }

    /**
     * Clear all cache
     */
    public function clearAllCache()
    {
        Cache::flush();
    }

    /**
     * Clear specific cache by pattern
     */
    public function clearCacheByPattern($pattern)
    {
        $keys = Cache::getRedis()->keys($pattern);
        if (!empty($keys)) {
            Cache::getRedis()->del($keys);
        }
    }

    /**
     * Clear dashboard cache
     */
    public function clearDashboardCache()
    {
        $this->clearCacheByPattern('dashboard_*');
    }

    /**
     * Clear patient cache
     */
    public function clearPatientCache()
    {
        $this->clearCacheByPattern('patient_*');
    }

    /**
     * Clear appointment cache
     */
    public function clearAppointmentCache()
    {
        $this->clearCacheByPattern('appointment_*');
    }

    /**
     * Clear inventory cache
     */
    public function clearInventoryCache()
    {
        $this->clearCacheByPattern('inventory_*');
    }

    /**
     * Get cache statistics
     */
    public function getCacheStatistics()
    {
        return [
            'cache_driver' => config('cache.default'),
            'cache_prefix' => config('cache.prefix'),
            'memory_usage' => memory_get_usage(true),
            'peak_memory' => memory_get_peak_usage(true),
        ];
    }

    private function clinicCacheKey(
        string $namespace,
        ClinicContext $context,
        mixed $dateFrom = null,
        mixed $dateTo = null,
    ): string {
        $from = $dateFrom === null ? 'default' : Carbon::parse($dateFrom)->toIso8601String();
        $to = $dateTo === null ? 'default' : Carbon::parse($dateTo)->toIso8601String();

        return implode(':', [
            $namespace,
            'clinic',
            $context->clinicId,
            hash('sha256', $from.'|'.$to),
        ]);
    }
}





