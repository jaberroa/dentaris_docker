<?php

namespace App\Providers;

use App\Models\Inventory;
use App\Models\InventoryMovement;
use App\Models\Invoice;
use App\Models\Patient;
use App\Models\Staff;
use App\Policies\InventoryPolicy;
use App\Policies\InventoryMovementPolicy;
use App\Policies\InvoicePolicy;
use App\Policies\PatientPolicy;
use App\Policies\StaffPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Invoice::class, InvoicePolicy::class);
        Gate::policy(Inventory::class, InventoryPolicy::class);
        Gate::policy(InventoryMovement::class, InventoryMovementPolicy::class);
        Gate::policy(Patient::class, PatientPolicy::class);
        Gate::policy(Staff::class, StaffPolicy::class);
    }
}
