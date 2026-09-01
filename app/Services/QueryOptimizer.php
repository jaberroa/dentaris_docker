<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class QueryOptimizer
{
    /**
     * Optimize patient queries with eager loading
     */
    public function optimizePatientQuery(Builder $query): Builder
    {
        return $query->with([
            'contacts' => function ($query) {
                $query->select('id', 'patient_id', 'type', 'value', 'is_primary');
            },
            'documents' => function ($query) {
                $query->select('id', 'patient_id', 'name', 'type', 'file_path', 'created_at');
            },
            'appointments' => function ($query) {
                $query->select('id', 'patient_id', 'appointment_date', 'start_time', 'type', 'status')
                      ->with(['staff:id,user_id', 'staff.user:id,name']);
            }
        ]);
    }

    /**
     * Optimize appointment queries with eager loading
     */
    public function optimizeAppointmentQuery(Builder $query): Builder
    {
        return $query->with([
            'patient:id,first_name,last_name,patient_code,email,phone',
            'staff:id,user_id,specialty',
            'staff.user:id,name,email',
            'appointmentStatus:id,name,color'
        ]);
    }

    /**
     * Optimize inventory queries with eager loading
     */
    public function optimizeInventoryQuery(Builder $query): Builder
    {
        return $query->with([
            'inventory:id,product_id,current_stock,available_stock,reserved_stock,last_used',
            'primarySupplier:id,name,contact_person,email,phone'
        ]);
    }

    /**
     * Add database indexes for common queries
     */
    public function addCommonIndexes(): void
    {
        // Índices para pacientes
        $this->addIndexIfNotExists('patients', 'idx_patients_status', 'status');
        $this->addIndexIfNotExists('patients', 'idx_patients_gender', 'gender');
        $this->addIndexIfNotExists('patients', 'idx_patients_created_at', 'created_at');
        $this->addIndexIfNotExists('patients', 'idx_patients_email', 'email');

        // Índices para citas
        $this->addIndexIfNotExists('appointments', 'idx_appointments_date', 'appointment_date');
        $this->addIndexIfNotExists('appointments', 'idx_appointments_patient', 'patient_id');
        $this->addIndexIfNotExists('appointments', 'idx_appointments_staff', 'staff_id');
        $this->addIndexIfNotExists('appointments', 'idx_appointments_status', 'appointment_status_id');
        $this->addIndexIfNotExists('appointments', 'idx_appointments_date_staff', ['appointment_date', 'staff_id']);

        // Índices para inventario
        $this->addIndexIfNotExists('products', 'idx_products_category', 'category');
        $this->addIndexIfNotExists('products', 'idx_products_code', 'product_code');
        $this->addIndexIfNotExists('inventory', 'idx_inventory_stock', 'current_stock');
        $this->addIndexIfNotExists('inventory', 'idx_inventory_product', 'product_id');

        // Índices para pagos
        $this->addIndexIfNotExists('payments', 'idx_payments_date', 'payment_date');
        $this->addIndexIfNotExists('payments', 'idx_payments_status', 'status');
        $this->addIndexIfNotExists('payments', 'idx_payments_method', 'payment_method');
        $this->addIndexIfNotExists('payments', 'idx_payments_patient', 'patient_id');

        // Índices para facturas
        $this->addIndexIfNotExists('invoices', 'idx_invoices_date', 'invoice_date');
        $this->addIndexIfNotExists('invoices', 'idx_invoices_status', 'status');
        $this->addIndexIfNotExists('invoices', 'idx_invoices_patient', 'patient_id');
        $this->addIndexIfNotExists('invoices', 'idx_invoices_due_date', 'due_date');
    }

    /**
     * Add index if it doesn't exist
     */
    protected function addIndexIfNotExists(string $table, string $indexName, $columns): void
    {
        try {
            $columns = is_array($columns) ? implode(',', $columns) : $columns;
            DB::statement("CREATE INDEX IF NOT EXISTS {$indexName} ON {$table} ({$columns})");
        } catch (\Exception $e) {
            // Index might already exist or table might not exist
            \Log::info("Index {$indexName} on {$table} already exists or table doesn't exist");
        }
    }

    /**
     * Optimize query with select specific columns
     */
    public function selectOnlyNeededColumns(Builder $query, array $columns): Builder
    {
        return $query->select($columns);
    }

    /**
     * Add query hints for better performance
     */
    public function addQueryHints(Builder $query, string $hint): Builder
    {
        return $query->fromRaw("{$query->getModel()->getTable()} {$hint}");
    }

    /**
     * Optimize pagination queries
     */
    public function optimizePagination(Builder $query, int $perPage = 15): Builder
    {
        // Usar cursor pagination para mejor performance en grandes datasets
        if ($query->getModel()->getKeyName() === 'id') {
            return $query->orderBy('id');
        }

        return $query->orderBy($query->getModel()->getKeyName());
    }

    /**
     * Get query execution plan
     */
    public function getQueryPlan(Builder $query): array
    {
        $sql = $query->toSql();
        $bindings = $query->getBindings();
        
        $explainQuery = "EXPLAIN " . $sql;
        
        try {
            return DB::select($explainQuery, $bindings);
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Analyze slow queries
     */
    public function analyzeSlowQueries(): array
    {
        try {
            // MySQL slow query log analysis
            $slowQueries = DB::select("
                SELECT 
                    sql_text,
                    exec_count,
                    avg_timer_wait/1000000000 as avg_time_seconds,
                    sum_timer_wait/1000000000 as total_time_seconds
                FROM performance_schema.events_statements_summary_by_digest 
                WHERE avg_timer_wait > 1000000000 
                ORDER BY avg_timer_wait DESC 
                LIMIT 10
            ");

            return $slowQueries;
        } catch (\Exception $e) {
            return ['error' => 'Performance schema not available: ' . $e->getMessage()];
        }
    }

    /**
     * Get database statistics
     */
    public function getDatabaseStats(): array
    {
        try {
            $stats = DB::select("
                SELECT 
                    table_name,
                    table_rows,
                    ROUND(((data_length + index_length) / 1024 / 1024), 2) AS 'size_mb'
                FROM information_schema.tables 
                WHERE table_schema = DATABASE()
                ORDER BY (data_length + index_length) DESC
            ");

            return $stats;
        } catch (\Exception $e) {
            return ['error' => 'Could not retrieve database stats: ' . $e->getMessage()];
        }
    }
}





