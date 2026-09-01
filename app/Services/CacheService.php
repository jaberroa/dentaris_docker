<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use App\Models\Patient;
use App\Models\Appointment;
use App\Models\Product;
use App\Models\Invoice;
use App\Models\Payment;
use Carbon\Carbon;

class CacheService
{
    /**
     * Cache duration in minutes
     */
    const CACHE_DURATION = 60;

    /**
     * Get dashboard KPIs with cache
     */
    public function getDashboardKpis($dateFrom = null, $dateTo = null)
    {
        $cacheKey = 'dashboard_kpis_' . ($dateFrom ?? 'default') . '_' . ($dateTo ?? 'default');
        
        return Cache::remember($cacheKey, self::CACHE_DURATION, function () use ($dateFrom, $dateTo) {
            $dateFrom = $dateFrom ?? now()->startOfMonth();
            $dateTo = $dateTo ?? now()->endOfMonth();

            return [
                'patients' => [
                    'total' => Patient::count(),
                    'new_this_month' => Patient::whereMonth('created_at', now()->month)->count(),
                    'active' => Patient::where('status', 'active')->count(),
                ],
                'appointments' => [
                    'total' => Appointment::whereBetween('appointment_date', [$dateFrom, $dateTo])->count(),
                    'completed' => Appointment::whereBetween('appointment_date', [$dateFrom, $dateTo])
                        ->whereHas('appointmentStatus', function ($q) {
                            $q->where('name', 'completed');
                        })->count(),
                ],
                'revenue' => [
                    'total' => Payment::whereBetween('payment_date', [$dateFrom, $dateTo])
                        ->where('status', 'completed')
                        ->sum('amount'),
                    'pending' => Invoice::where('status', 'sent')
                        ->where('balance_due', '>', 0)
                        ->sum('balance_due'),
                ],
            ];
        });
    }

    /**
     * Get patient statistics with cache
     */
    public function getPatientStatistics()
    {
        return Cache::remember('patient_statistics', self::CACHE_DURATION, function () {
            return [
                'total_patients' => Patient::count(),
                'active_patients' => Patient::where('status', 'active')->count(),
                'new_patients_this_month' => Patient::whereMonth('created_at', now()->month)->count(),
                'patients_by_gender' => Patient::selectRaw('gender, COUNT(*) as count')
                    ->groupBy('gender')
                    ->get()
                    ->pluck('count', 'gender'),
            ];
        });
    }

    /**
     * Get inventory statistics with cache
     */
    public function getInventoryStatistics()
    {
        return Cache::remember('inventory_statistics', self::CACHE_DURATION, function () {
            return [
                'total_products' => Product::count(),
                'low_stock_count' => Product::whereHas('inventory', function ($q) {
                    $q->whereColumn('current_stock', '<=', 'products.minimum_stock');
                })->count(),
                'out_of_stock_count' => Product::whereHas('inventory', function ($q) {
                    $q->where('current_stock', 0);
                })->count(),
            ];
        });
    }

    /**
     * Get appointment statistics with cache
     */
    public function getAppointmentStatistics($dateFrom = null, $dateTo = null)
    {
        $cacheKey = 'appointment_statistics_' . ($dateFrom ?? 'default') . '_' . ($dateTo ?? 'default');
        
        return Cache::remember($cacheKey, self::CACHE_DURATION, function () use ($dateFrom, $dateTo) {
            $dateFrom = $dateFrom ?? now()->startOfMonth();
            $dateTo = $dateTo ?? now()->endOfMonth();

            return [
                'total_appointments' => Appointment::whereBetween('appointment_date', [$dateFrom, $dateTo])->count(),
                'completed_appointments' => Appointment::whereBetween('appointment_date', [$dateFrom, $dateTo])
                    ->whereHas('appointmentStatus', function ($q) {
                        $q->where('name', 'completed');
                    })->count(),
                'cancelled_appointments' => Appointment::whereBetween('appointment_date', [$dateFrom, $dateTo])
                    ->whereHas('appointmentStatus', function ($q) {
                        $q->where('name', 'cancelled');
                    })->count(),
            ];
        });
    }

    /**
     * Get revenue data with cache
     */
    public function getRevenueData($dateFrom = null, $dateTo = null)
    {
        $cacheKey = 'revenue_data_' . ($dateFrom ?? 'default') . '_' . ($dateTo ?? 'default');
        
        return Cache::remember($cacheKey, self::CACHE_DURATION, function () use ($dateFrom, $dateTo) {
            $dateFrom = $dateFrom ?? now()->startOfMonth();
            $dateTo = $dateTo ?? now()->endOfMonth();

            return [
                'daily_revenue' => Payment::whereBetween('payment_date', [$dateFrom, $dateTo])
                    ->where('status', 'completed')
                    ->selectRaw('DATE(payment_date) as date, SUM(amount) as total')
                    ->groupBy('date')
                    ->orderBy('date')
                    ->get(),
                'revenue_by_method' => Payment::whereBetween('payment_date', [$dateFrom, $dateTo])
                    ->where('status', 'completed')
                    ->selectRaw('payment_method, SUM(amount) as total')
                    ->groupBy('payment_method')
                    ->get(),
                'total_revenue' => Payment::whereBetween('payment_date', [$dateFrom, $dateTo])
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
}





