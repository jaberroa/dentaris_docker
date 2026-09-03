<?php

namespace App\Http\Controllers;

use App\Exports\StaffExport;
use App\Http\Requests\Staff\StoreStaffRequest;
use App\Http\Requests\Staff\UpdateStaffRequest;
use App\Models\Role;
use App\Models\Staff;
use App\Modules\Clinics\Data\ClinicContext;
use App\Modules\Clinics\Services\StaffClinicalService;
use DomainException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Throwable;

class StaffController extends Controller
{
    public function __construct(
        private readonly StaffClinicalService $staffService,
    ) {
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $context = $this->clinicContext($request);
        Gate::authorize('viewAny', Staff::class);
        $query = $this->filteredQuery($request, $context);

        // Manejar per_page
        $perPage = $request->get('per_page', 10);
        
        if ($perPage === 'all') {
            // Para mostrar todos los registros
            $staff = $query->orderBy('created_at', 'desc')->get();
            $total = $staff->count();
            $currentPage = 1;
            
            // Crear paginador manual
            $staff = new \Illuminate\Pagination\LengthAwarePaginator(
                $staff,
                $total,
                max($total, 1),
                $currentPage,
                ['path' => request()->url(), 'pageName' => 'page']
            );
            $staff->appends($request->query());
        } else {
            $perPage = (int) $perPage;
            if (! in_array($perPage, [10, 25, 50, 100], true)) {
                $perPage = 10;
            }
            $staff = $query->orderBy('created_at', 'desc')->paginate($perPage)->withQueryString();
        }

        $this->staffService->loadClinicRoles($staff->getCollection(), $context);

        $perPageValue = $request->get('per_page', 10);
        
        return view('staff.index', compact('staff', 'perPageValue'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $this->clinicContext($request);
        Gate::authorize('create', Staff::class);
        $roles = Role::query()->where('is_active', true)->orderBy('display_name')->get();

        return view('staff.create', compact('roles'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreStaffRequest $request)
    {
        $context = $request->clinicContext();
        Gate::authorize('create', Staff::class);

        try {
            $this->staffService->create(
                $request->userData(),
                $request->staffData(),
                $request->roleId(),
                $context,
                $this->auditContext($request),
            );
        } catch (DomainException $exception) {
            return back()->withErrors(['staff' => $exception->getMessage()])->withInput();
        }

        return redirect()->route('staff.index')->with('success', 'Personal creado exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Staff $staff)
    {
        $context = $this->clinicContext($request);
        $staff = $this->staffForContext($staff, $context);
        Gate::authorize('view', $staff);
        $staff->load('user');
        $this->staffService->loadClinicRoles(new \Illuminate\Database\Eloquent\Collection([$staff]), $context);

        return view('staff.show', compact('staff'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, Staff $staff)
    {
        $context = $this->clinicContext($request);
        $staff = $this->staffForContext($staff, $context);
        Gate::authorize('update', $staff);
        $staff->load('user');
        $this->staffService->loadClinicRoles(new \Illuminate\Database\Eloquent\Collection([$staff]), $context);
        $roles = Role::query()->where('is_active', true)->orderBy('display_name')->get();

        return view('staff.edit', compact('staff', 'roles'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateStaffRequest $request, Staff $staff)
    {
        $context = $request->clinicContext();
        $staff = $this->staffForContext($staff, $context);
        Gate::authorize('update', $staff);

        try {
            $this->staffService->update(
                $staff,
                $request->userData(),
                $request->staffData(),
                $request->roleId(),
                $context,
                $this->auditContext($request),
            );
        } catch (DomainException $exception) {
            return back()->withErrors(['staff' => $exception->getMessage()])->withInput();
        }

        return redirect()->route('staff.index')->with('success', 'Personal actualizado exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Staff $staff)
    {
        $context = $this->clinicContext($request);
        $staff = $this->staffForContext($staff, $context);
        Gate::authorize('delete', $staff);

        try {
            $result = $this->staffService->delete($staff, $context, $this->auditContext($request));

            if ($result['appointments'] > 0) {
                return redirect()->route('staff.index')
                    ->with('error', 'No se puede eliminar este personal porque tiene '.$result['appointments'].' citas asociadas. Primero debe reasignar o cancelar las citas.');
            }

            if ($result['medical_records'] > 0) {
                return redirect()->route('staff.index')
                    ->with('error', 'No se puede eliminar este personal porque tiene '.$result['medical_records'].' historiales médicos asociados.');
            }

            return redirect()->route('staff.index')->with('success', 'Personal eliminado exitosamente.');

        } catch (Throwable $exception) {
            Log::error('Error deleting staff member', [
                'staff_id' => $staff->id,
                'clinic_id' => $context->clinicId,
                'error' => $exception->getMessage(),
            ]);
            
            return redirect()->route('staff.index')
                ->with('error', 'No se puede eliminar este personal porque tiene registros asociados en el sistema.');
        }
    }

    /**
     * Exportar personal a Excel
     */
    public function exportExcel(Request $request)
    {
        $this->clinicContext($request);
        Gate::authorize('exportAny', Staff::class);
        $filters = $request->only(['search', 'specialty', 'is_active']);
        
        $filename = 'personal_' . now()->format('Y-m-d_H-i-s') . '.xlsx';
        
        return Excel::download(new StaffExport($filters), $filename);
    }

    /**
     * Exportar personal a PDF
     */
    public function exportPdf(Request $request)
    {
        $context = $this->clinicContext($request);
        Gate::authorize('exportAny', Staff::class);
        $filters = $request->only(['search', 'specialty', 'is_active']);
        $staff = $this->filteredQuery($request, $context)->orderBy('created_at', 'desc')->get();
        $this->staffService->loadClinicRoles($staff, $context);
        
        $filename = 'personal_' . now()->format('Y-m-d_H-i-s') . '.pdf';
        
        $pdf = Pdf::loadView('staff.export-pdf', compact('staff', 'filters'))
                  ->setPaper('A4', 'landscape')
                  ->setOptions([
                      'isHtml5ParserEnabled' => true,
                      'isRemoteEnabled' => true,
                      'defaultFont' => 'DejaVu Sans'
                  ]);

        return $pdf->download($filename);
    }

    private function filteredQuery(Request $request, ClinicContext $context): Builder
    {
        $query = Staff::query()->forClinic($context)->with('user');

        if ($request->filled('search')) {
            $search = (string) $request->input('search');
            $query->where(function (Builder $query) use ($search): void {
                $query->whereHas('user', function (Builder $userQuery) use ($search): void {
                    $userQuery->where(function (Builder $identityQuery) use ($search): void {
                        $identityQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
                })->orWhere('employee_id', 'like', "%{$search}%")
                    ->orWhere('specialty', 'like', "%{$search}%");
            });
        }

        if ($request->filled('specialty')) {
            $query->where('specialty', $request->input('specialty'));
        }

        if ($request->filled('is_active') && in_array((string) $request->input('is_active'), ['0', '1'], true)) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        return $query;
    }

    private function clinicContext(Request $request): ClinicContext
    {
        $context = $request->attributes->get(ClinicContext::class);

        abort_unless($context instanceof ClinicContext, 403, 'El contexto clínico no está disponible.');

        return $context;
    }

    private function staffForContext(Staff $staff, ClinicContext $context): Staff
    {
        abort_unless($staff->clinic_id !== null && (int) $staff->clinic_id === $context->clinicId, 404);

        return $staff;
    }

    /**
     * @return array{ip_address: string, user_agent: string|null, session_id: string|null}
     */
    private function auditContext(Request $request): array
    {
        return [
            'ip_address' => $request->ip() ?? 'unknown',
            'user_agent' => $request->userAgent(),
            'session_id' => $request->hasSession() ? $request->session()->getId() : null,
        ];
    }
}
