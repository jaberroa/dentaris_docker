<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\MedicalRecordController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\InventoryLocationController;
use App\Http\Controllers\LabWorkController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\TreatmentPlanController;
use App\Http\Controllers\DentalTreatmentPlanController;
use App\Http\Controllers\QuoteController;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\TreatmentController;
use App\Http\Controllers\PaymentController;
use Illuminate\Support\Facades\Route;

// Portal web público (raíz)
Route::get('/', function () {
    return view('welcome');
})->name('home');

// Rutas públicas (sin autenticación)
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store']);
    
    Route::get('/register', [RegisterController::class, 'create'])->name('register');
    Route::post('/register', [RegisterController::class, 'store']);
    
    Route::get('/forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])->name('password.email');
    
    Route::get('/reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('/reset-password', [NewPasswordController::class, 'store'])->name('password.update');
});

// Ruta de logout (sin middleware guest)
Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');



// Rutas protegidas (requieren autenticación)
Route::middleware('auth')->group(function () {
    
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/appointments', [DashboardController::class, 'getAppointmentData'])->name('dashboard.appointments');
    Route::get('/dashboard/revenue', [DashboardController::class, 'getRevenueData'])->name('dashboard.revenue');
    
    // Perfil de usuario
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
        Route::put('/password', [ProfileController::class, 'passwordUpdate'])->name('profile.password.update');
    
    // Gestión de Pacientes
    Route::get('/patients', [PatientController::class, 'index'])->name('patients.index');
    Route::get('/patients/search', [PatientController::class, 'search'])->name('patients.search');
    
    // Rutas específicas (deben ir antes que las rutas con parámetros)
    Route::get('/patients/create', [PatientController::class, 'create'])->name('patients.create');
    Route::post('/patients', [PatientController::class, 'store'])->name('patients.store');
    
    // Rutas de exportación
    Route::get('/patients/export/excel', [PatientController::class, 'exportExcel'])->name('patients.export.excel');
    Route::get('/patients/export/pdf', [PatientController::class, 'exportPdf'])->name('patients.export.pdf');
    
    // Rutas para actualizar género y estado de pacientes
    Route::patch('/patients/{patient}/gender', [PatientController::class, 'updateGender'])->name('patients.update.gender');
    Route::patch('/patients/{patient}/status', [PatientController::class, 'updateStatus'])->name('patients.update.status');
    
    // Rutas con parámetros (deben ir al final)
    Route::get('/patients/{patient}', [PatientController::class, 'show'])->name('patients.show');
    Route::get('/patients/{patient}/edit', [PatientController::class, 'edit'])->name('patients.edit');
    Route::put('/patients/{patient}', [PatientController::class, 'update'])->name('patients.update');
    Route::delete('/patients/{patient}', [PatientController::class, 'destroy'])->name('patients.destroy');
    Route::get('/patients/{patient}/export/history', [PatientController::class, 'exportPatientHistory'])->name('patients.export.history');
    
    // Gestión de Historias Clínicas
    Route::get('/medical-records', [MedicalRecordController::class, 'index'])->name('medical-records.index');
        Route::get('/medical-records/create', [MedicalRecordController::class, 'create'])->name('medical-records.create');
        Route::post('/medical-records', [MedicalRecordController::class, 'store'])->name('medical-records.store');
    Route::get('/medical-records/{medicalRecord}', [MedicalRecordController::class, 'show'])->name('medical-records.show');
        Route::get('/medical-records/{medicalRecord}/edit', [MedicalRecordController::class, 'edit'])->name('medical-records.edit');
        Route::put('/medical-records/{medicalRecord}', [MedicalRecordController::class, 'update'])->name('medical-records.update');
        Route::delete('/medical-records/{medicalRecord}', [MedicalRecordController::class, 'destroy'])->name('medical-records.destroy');
        Route::get('/medical-records/{medicalRecord}/export', [MedicalRecordController::class, 'exportPdf'])->name('medical-records.export');
    Route::get('/patients/{patient}/medical-records', [MedicalRecordController::class, 'getPatientRecords'])->name('patients.medical-records');
    
    // Gestión de Citas
    Route::get('/appointments', [AppointmentController::class, 'index'])->name('appointments.index');
    Route::get('/appointments/weekly', [AppointmentController::class, 'weekly'])->name('appointments.weekly');
    Route::get('/appointments/monthly', [AppointmentController::class, 'monthly'])->name('appointments.monthly');
    Route::get('/appointments/yearly', [AppointmentController::class, 'yearly'])->name('appointments.yearly');
    
    // Rutas específicas (deben ir antes que las rutas con parámetros)
    Route::get('/appointments/create', [AppointmentController::class, 'create'])->name('appointments.create');
    Route::post('/appointments', [AppointmentController::class, 'store'])->name('appointments.store');
    
    // Rutas con parámetros (deben ir al final)
    Route::get('/appointments/{appointment}', [AppointmentController::class, 'show'])->name('appointments.show');
    Route::get('/appointments/{appointment}/edit', [AppointmentController::class, 'edit'])->name('appointments.edit');
    Route::put('/appointments/{appointment}', [AppointmentController::class, 'update'])->name('appointments.update');
    Route::delete('/appointments/{appointment}', [AppointmentController::class, 'destroy'])->name('appointments.destroy');
    Route::post('/appointments/{appointment}/confirm', [AppointmentController::class, 'confirm'])->name('appointments.confirm');
    Route::post('/appointments/{appointment}/cancel', [AppointmentController::class, 'cancel'])->name('appointments.cancel');
    Route::patch('/appointments/{appointment}/status', [AppointmentController::class, 'updateStatus'])->name('appointments.update.status');
    Route::get('/appointments/search-staff', [AppointmentController::class, 'searchStaff'])->name('appointments.search.staff');
    
    // Gestión de Inventario
    Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
    Route::get('/inventory/movements', [InventoryController::class, 'movements'])
        ->middleware('permission:view_inventory')
        ->name('inventory.movements');
    Route::get('/inventory/locations', [InventoryLocationController::class, 'index'])
        ->middleware('permission:view_inventory')
        ->name('inventory.locations.index');
    Route::get('/inventory/low-stock', [InventoryController::class, 'lowStock'])->name('inventory.low-stock');
    Route::get('/inventory/out-of-stock', [InventoryController::class, 'outOfStock'])->name('inventory.out-of-stock');
    Route::get('/inventory/expiring-soon', [InventoryController::class, 'expiringSoon'])->name('inventory.expiring-soon');
    Route::get('/inventory/report', [InventoryController::class, 'report'])->name('inventory.report');
    Route::get('/inventory/{inventory}', [InventoryController::class, 'show'])
        ->middleware('can:view,inventory')
        ->name('inventory.show');
    
    Route::middleware('permission:manage_inventory')->group(function () {
        Route::post('/inventory/locations', [InventoryLocationController::class, 'store'])->name('inventory.locations.store');
        Route::put('/inventory/locations/{inventoryLocation}', [InventoryLocationController::class, 'update'])->name('inventory.locations.update');
        Route::put('/inventory/{inventory}', [InventoryController::class, 'update'])
            ->middleware('can:update,inventory')
            ->name('inventory.update');
        Route::post('/inventory/transfer', [InventoryController::class, 'transfer'])->name('inventory.transfer');
        Route::post('/inventory/{inventory}/locations', [InventoryLocationController::class, 'createStockLocation'])
            ->middleware('can:update,inventory')
            ->name('inventory.locations.stock.store');
        Route::post('/inventory/export', [InventoryController::class, 'export'])->name('inventory.export');
    });

    Route::post('/inventory/{inventory}/adjust', [InventoryController::class, 'adjust'])
        ->middleware('can:adjust,inventory')
        ->name('inventory.adjust');
    Route::post('/inventory/movements/{movement}/reverse', [InventoryController::class, 'reverseMovement'])
        ->middleware('can:reverse,movement')
        ->name('inventory.movements.reverse');
    
    // Gestión de Facturación
    Route::get('/billing', [BillingController::class, 'index'])->name('billing.index');
    Route::get('/billing/{invoice}', [BillingController::class, 'show'])
        ->middleware('can:view,invoice')
        ->name('billing.show');
    Route::get('/billing/{invoice}/pdf', [BillingController::class, 'downloadPdf'])->name('billing.pdf');
    
    Route::middleware('can:manage_billing')->group(function () {
        Route::get('/billing/create', [BillingController::class, 'create'])->name('billing.create');
        Route::post('/billing', [BillingController::class, 'store'])->name('billing.store');
        Route::get('/billing/{invoice}/edit', [BillingController::class, 'edit'])->name('billing.edit');
        Route::put('/billing/{invoice}', [BillingController::class, 'update'])->name('billing.update');
        Route::delete('/billing/{invoice}', [BillingController::class, 'destroy'])->name('billing.destroy');
        Route::post('/billing/{invoice}/send', [BillingController::class, 'sendInvoice'])->name('billing.send');
    });
    
    // Gestión de Reportes
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    
    Route::middleware('can:view_reports')->group(function () {
        Route::get('/reports/financial', [ReportController::class, 'financial'])->name('reports.financial');
        Route::get('/reports/appointments', [ReportController::class, 'appointments'])->name('reports.appointments');
        Route::get('/reports/patients', [ReportController::class, 'patients'])->name('reports.patients');
        Route::get('/reports/inventory', [ReportController::class, 'inventory'])->name('reports.inventory');
        Route::get('/reports/kpis', [ReportController::class, 'kpis'])->name('reports.kpis');
    });
    
    // Gestión de Notificaciones
    // Notificaciones - acceso libre para usuarios autenticados
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/{notification}', [NotificationController::class, 'show'])->name('notifications.show');
    Route::post('/notifications/{notification}/mark-read', [NotificationController::class, 'markAsRead'])->name('notifications.mark-read');
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');
    Route::delete('/notifications/{notification}', [NotificationController::class, 'destroy'])->name('notifications.destroy');
    
    // Gestión de Personal
    Route::get('/staff', [StaffController::class, 'index'])->name('staff.index');
    
    // Rutas específicas (deben ir antes que las rutas con parámetros)
    Route::get('/staff/create', [StaffController::class, 'create'])->name('staff.create');
    Route::post('/staff', [StaffController::class, 'store'])->name('staff.store');
    
    // Rutas de exportación
    Route::get('/staff/export/excel', [StaffController::class, 'exportExcel'])->name('staff.export.excel');
    Route::get('/staff/export/pdf', [StaffController::class, 'exportPdf'])->name('staff.export.pdf');
    
    // Rutas con parámetros (deben ir al final)
    Route::get('/staff/{staff}', [StaffController::class, 'show'])->name('staff.show');
    Route::get('/staff/{staff}/edit', [StaffController::class, 'edit'])->name('staff.edit');
    Route::put('/staff/{staff}', [StaffController::class, 'update'])->name('staff.update');
    Route::delete('/staff/{staff}', [StaffController::class, 'destroy'])->name('staff.destroy');
    
    // Gestión de Planes de Tratamiento
    Route::get('/treatment-plans', [TreatmentPlanController::class, 'index'])->name('treatment-plans.index');
    Route::get('/treatment-plans/create', [TreatmentPlanController::class, 'create'])->name('treatment-plans.create');
    Route::post('/treatment-plans', [TreatmentPlanController::class, 'store'])->name('treatment-plans.store');
    Route::get('/treatment-plans/{treatmentPlan}', [TreatmentPlanController::class, 'show'])->name('treatment-plans.show');
    Route::get('/treatment-plans/{treatmentPlan}/edit', [TreatmentPlanController::class, 'edit'])->name('treatment-plans.edit');
    Route::put('/treatment-plans/{treatmentPlan}', [TreatmentPlanController::class, 'update'])->name('treatment-plans.update');
    Route::delete('/treatment-plans/{treatmentPlan}', [TreatmentPlanController::class, 'destroy'])->name('treatment-plans.destroy');
    Route::patch('/treatment-plans/{treatmentPlan}/status', [TreatmentPlanController::class, 'updateStatus'])->name('treatment-plans.update-status');

    // Nuevo módulo: Planes Odontológicos (experimental, no rompe lo existente)
    Route::resource('dental-plans', DentalTreatmentPlanController::class);
    Route::post('/dental-plans/{dental_plan}/procedures', [DentalTreatmentPlanController::class, 'storeProcedure'])->name('dental-plans.procedures.store');
    Route::post('/dental-plans/{dental_plan}/tooth-evaluation', [DentalTreatmentPlanController::class, 'saveToothEvaluation'])->name('dental-plans.tooth-evaluation.store');
    Route::delete('/dental-plans/{dental_plan}/tooth-evaluation/{toothNumber}', [DentalTreatmentPlanController::class, 'deleteToothEvaluation'])->name('dental-plans.tooth-evaluation.delete');
    Route::get('/dental-plans/{dental_plan}/export-pdf', [DentalTreatmentPlanController::class, 'exportPDF'])->name('dental-plans.export-pdf');
    
    // Gestión de Trabajos de Laboratorio
    Route::get('/lab-works', [LabWorkController::class, 'index'])->name('lab-works.index');
    Route::get('/lab-works/{labWork}', [LabWorkController::class, 'show'])->name('lab-works.show');
    
    Route::middleware('can:manage_lab_works')->group(function () {
        Route::get('/lab-works/create', [LabWorkController::class, 'create'])->name('lab-works.create');
        Route::post('/lab-works', [LabWorkController::class, 'store'])->name('lab-works.store');
        Route::get('/lab-works/{labWork}/edit', [LabWorkController::class, 'edit'])->name('lab-works.edit');
        Route::put('/lab-works/{labWork}', [LabWorkController::class, 'update'])->name('lab-works.update');
        Route::delete('/lab-works/{labWork}', [LabWorkController::class, 'destroy'])->name('lab-works.destroy');
    });
    
    // Gestión de Cotizaciones
    Route::get('/quotes', [QuoteController::class, 'index'])->name('quotes.index');
    Route::get('/quotes/{quote}', [QuoteController::class, 'show'])->name('quotes.show');
    
    Route::middleware('can:manage_quotes')->group(function () {
        Route::get('/quotes/create', [QuoteController::class, 'create'])->name('quotes.create');
        Route::post('/quotes', [QuoteController::class, 'store'])->name('quotes.store');
        Route::get('/quotes/{quote}/edit', [QuoteController::class, 'edit'])->name('quotes.edit');
        Route::put('/quotes/{quote}', [QuoteController::class, 'update'])->name('quotes.update');
        Route::delete('/quotes/{quote}', [QuoteController::class, 'destroy'])->name('quotes.destroy');
    });
    
    // Gestión de Proveedores
    Route::get('/suppliers', [SupplierController::class, 'index'])->name('suppliers.index');
    Route::get('/suppliers/{supplier}', [SupplierController::class, 'show'])->name('suppliers.show');
    
    Route::middleware('can:manage_suppliers')->group(function () {
        Route::get('/suppliers/create', [SupplierController::class, 'create'])->name('suppliers.create');
        Route::post('/suppliers', [SupplierController::class, 'store'])->name('suppliers.store');
        Route::get('/suppliers/{supplier}/edit', [SupplierController::class, 'edit'])->name('suppliers.edit');
        Route::put('/suppliers/{supplier}', [SupplierController::class, 'update'])->name('suppliers.update');
        Route::delete('/suppliers/{supplier}', [SupplierController::class, 'destroy'])->name('suppliers.destroy');
    });
    
    // Gestión de Tratamientos
    Route::get('/treatments', [TreatmentController::class, 'index'])->name('treatments.index');
    Route::get('/treatments/{treatment}', [TreatmentController::class, 'show'])->name('treatments.show');
    
    Route::middleware('can:manage_treatments')->group(function () {
        Route::get('/treatments/create', [TreatmentController::class, 'create'])->name('treatments.create');
        Route::post('/treatments', [TreatmentController::class, 'store'])->name('treatments.store');
        Route::get('/treatments/{treatment}/edit', [TreatmentController::class, 'edit'])->name('treatments.edit');
        Route::put('/treatments/{treatment}', [TreatmentController::class, 'update'])->name('treatments.update');
        Route::delete('/treatments/{treatment}', [TreatmentController::class, 'destroy'])->name('treatments.destroy');
    });
    
    // Gestión de Pagos
    Route::get('/payments', [PaymentController::class, 'index'])->name('payments.index');
    Route::get('/payments/{payment}', [PaymentController::class, 'show'])->name('payments.show');
    
    Route::middleware('can:manage_payments')->group(function () {
        Route::get('/payments/create', [PaymentController::class, 'create'])->name('payments.create');
        Route::post('/payments', [PaymentController::class, 'store'])->name('payments.store');
        Route::get('/payments/{payment}/edit', [PaymentController::class, 'edit'])->name('payments.edit');
        Route::put('/payments/{payment}', [PaymentController::class, 'update'])->name('payments.update');
        Route::delete('/payments/{payment}', [PaymentController::class, 'destroy'])->name('payments.destroy');
    });
    
    // Gestión de Compras
    Route::get('/purchases', [PurchaseController::class, 'index'])->name('purchases.index');
    Route::get('/purchases/{purchase}', [PurchaseController::class, 'show'])->name('purchases.show');
    
    Route::middleware('can:manage_purchases')->group(function () {
        Route::get('/purchases/create', [PurchaseController::class, 'create'])->name('purchases.create');
        Route::post('/purchases', [PurchaseController::class, 'store'])->name('purchases.store');
        Route::get('/purchases/{purchase}/edit', [PurchaseController::class, 'edit'])->name('purchases.edit');
        Route::put('/purchases/{purchase}', [PurchaseController::class, 'update'])->name('purchases.update');
        Route::delete('/purchases/{purchase}', [PurchaseController::class, 'destroy'])->name('purchases.destroy');
    });

});

Route::fallback(function () {
    return view('errors.404');
});
    Route::middleware('can:manage_billing')->group(function () {

        Route::get('/billing/create', [BillingController::class, 'create'])->name('billing.create');

        Route::post('/billing', [BillingController::class, 'store'])->name('billing.store');

        Route::get('/billing/{invoice}/edit', [BillingController::class, 'edit'])->name('billing.edit');

        Route::put('/billing/{invoice}', [BillingController::class, 'update'])->name('billing.update');

        Route::delete('/billing/{invoice}', [BillingController::class, 'destroy'])->name('billing.destroy');

        Route::post('/billing/{invoice}/send', [BillingController::class, 'sendInvoice'])->name('billing.send');

    });
    
    // Gestión de Trabajos de Laboratorio

    Route::get('/lab-works', [LabWorkController::class, 'index'])->name('lab-works.index');

    Route::get('/lab-works/{labWork}', [LabWorkController::class, 'show'])->name('lab-works.show');

    

    Route::middleware('can:manage_lab_works')->group(function () {

        Route::get('/lab-works/create', [LabWorkController::class, 'create'])->name('lab-works.create');

        Route::post('/lab-works', [LabWorkController::class, 'store'])->name('lab-works.store');

        Route::get('/lab-works/{labWork}/edit', [LabWorkController::class, 'edit'])->name('lab-works.edit');

        Route::put('/lab-works/{labWork}', [LabWorkController::class, 'update'])->name('lab-works.update');

        Route::delete('/lab-works/{labWork}', [LabWorkController::class, 'destroy'])->name('lab-works.destroy');

    });

    

    // Gestión de Cotizaciones

    Route::get('/quotes', [QuoteController::class, 'index'])->name('quotes.index');

    Route::get('/quotes/{quote}', [QuoteController::class, 'show'])->name('quotes.show');

    

    Route::middleware('can:manage_quotes')->group(function () {

        Route::get('/quotes/create', [QuoteController::class, 'create'])->name('quotes.create');

        Route::post('/quotes', [QuoteController::class, 'store'])->name('quotes.store');

        Route::get('/quotes/{quote}/edit', [QuoteController::class, 'edit'])->name('quotes.edit');

        Route::put('/quotes/{quote}', [QuoteController::class, 'update'])->name('quotes.update');

        Route::delete('/quotes/{quote}', [QuoteController::class, 'destroy'])->name('quotes.destroy');

    });

    

    // Gestión de Proveedores

    Route::get('/suppliers', [SupplierController::class, 'index'])->name('suppliers.index');

    Route::get('/suppliers/{supplier}', [SupplierController::class, 'show'])->name('suppliers.show');

    

    Route::middleware('can:manage_suppliers')->group(function () {

        Route::get('/suppliers/create', [SupplierController::class, 'create'])->name('suppliers.create');

        Route::post('/suppliers', [SupplierController::class, 'store'])->name('suppliers.store');

        Route::get('/suppliers/{supplier}/edit', [SupplierController::class, 'edit'])->name('suppliers.edit');

        Route::put('/suppliers/{supplier}', [SupplierController::class, 'update'])->name('suppliers.update');

        Route::delete('/suppliers/{supplier}', [SupplierController::class, 'destroy'])->name('suppliers.destroy');

    });

    

    // Gestión de Tratamientos

    Route::get('/treatments', [TreatmentController::class, 'index'])->name('treatments.index');

    Route::get('/treatments/{treatment}', [TreatmentController::class, 'show'])->name('treatments.show');

    

    Route::middleware('can:manage_treatments')->group(function () {

        Route::get('/treatments/create', [TreatmentController::class, 'create'])->name('treatments.create');

        Route::post('/treatments', [TreatmentController::class, 'store'])->name('treatments.store');

        Route::get('/treatments/{treatment}/edit', [TreatmentController::class, 'edit'])->name('treatments.edit');

        Route::put('/treatments/{treatment}', [TreatmentController::class, 'update'])->name('treatments.update');

        Route::delete('/treatments/{treatment}', [TreatmentController::class, 'destroy'])->name('treatments.destroy');

    });

    

    // Gestión de Pagos

    Route::get('/payments', [PaymentController::class, 'index'])->name('payments.index');

    Route::get('/payments/{payment}', [PaymentController::class, 'show'])->name('payments.show');

    

    Route::middleware('can:manage_payments')->group(function () {

        Route::get('/payments/create', [PaymentController::class, 'create'])->name('payments.create');

        Route::post('/payments', [PaymentController::class, 'store'])->name('payments.store');

        Route::get('/payments/{payment}/edit', [PaymentController::class, 'edit'])->name('payments.edit');

        Route::put('/payments/{payment}', [PaymentController::class, 'update'])->name('payments.update');

        Route::delete('/payments/{payment}', [PaymentController::class, 'destroy'])->name('payments.destroy');

    });

    

    // Gestión de Compras

    Route::get('/purchases', [PurchaseController::class, 'index'])->name('purchases.index');

    Route::get('/purchases/{purchase}', [PurchaseController::class, 'show'])->name('purchases.show');

    

    Route::middleware('can:manage_purchases')->group(function () {

        Route::get('/purchases/create', [PurchaseController::class, 'create'])->name('purchases.create');

        Route::post('/purchases', [PurchaseController::class, 'store'])->name('purchases.store');

        Route::get('/purchases/{purchase}/edit', [PurchaseController::class, 'edit'])->name('purchases.edit');

        Route::put('/purchases/{purchase}', [PurchaseController::class, 'update'])->name('purchases.update');

        Route::delete('/purchases/{purchase}', [PurchaseController::class, 'destroy'])->name('purchases.destroy');

    });



Route::fallback(function () {

    return view('errors.404');

});
