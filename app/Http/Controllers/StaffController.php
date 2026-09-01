<?php

namespace App\Http\Controllers;

use App\Models\Staff;
use App\Models\User;
use App\Models\Role;
use App\Exports\StaffExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class StaffController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Staff::with(['user.roles']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('user', function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            })->orWhere('specialty', 'like', "%{$search}%");
        }

        if ($request->filled('specialty')) {
            $query->where('specialty', $request->specialty);
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active);
        }

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
                $total, // perPage = total para mostrar todos
                $currentPage,
                ['path' => request()->url(), 'pageName' => 'page']
            );
            $staff->appends($request->query());
        } else {
            $perPage = (int) $perPage;
            if ($perPage < 1) $perPage = 10;
            $staff = $query->orderBy('created_at', 'desc')->paginate($perPage)->withQueryString();
        }

        $perPageValue = $request->get('per_page', 10);
        
        return view('staff.index', compact('staff', 'perPageValue'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $roles = Role::where('is_active', true)->get();
        return view('staff.create', compact('roles'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'nullable|string|max:20',
            'specialty' => 'nullable|string|max:255',
            'license_number' => 'nullable|string|max:100',
            'hire_date' => 'nullable|date',
            'salary' => 'nullable|numeric|min:0',
            'role_id' => 'required|exists:roles,id',
            'is_active' => 'boolean'
        ]);

        DB::transaction(function () use ($request) {
            // Crear usuario
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'email_verified_at' => now(),
            ]);

            // Asignar rol
            $role = Role::find($request->role_id);
            $user->assignRole($role);

            // Crear staff
            Staff::create([
                'user_id' => $user->id,
                'phone' => $request->phone,
                'specialty' => $request->specialty,
                'license_number' => $request->license_number,
                'hire_date' => $request->hire_date,
                'salary' => $request->salary,
                'is_active' => $request->has('is_active'),
            ]);
        });

        return redirect()->route('staff.index')->with('success', 'Personal creado exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Staff $staff)
    {
        $staff->load(['user.roles']);
        return view('staff.show', compact('staff'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Staff $staff)
    {
        $staff->load(['user.roles']);
        $roles = Role::where('is_active', true)->get();
        return view('staff.edit', compact('staff', 'roles'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Staff $staff)
    {
        $validationRules = [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $staff->user_id,
            'phone' => 'nullable|string|max:20',
            'specialty' => 'nullable|string|max:255',
            'license_number' => 'nullable|string|max:100',
            'hire_date' => 'nullable|date',
            'salary' => 'nullable|numeric|min:0',
            'role_id' => 'required|exists:roles,id',
            'is_active' => 'nullable|boolean'
        ];

        // Solo validar contraseña si se proporciona
        if ($request->filled('password')) {
            $validationRules['password'] = 'required|string|min:8|confirmed';
        }

        $request->validate($validationRules);

        DB::transaction(function () use ($request, $staff) {
            $userData = [
                'name' => $request->name,
                'email' => $request->email,
            ];

            // Actualizar contraseña solo si se proporciona
            if ($request->filled('password')) {
                $userData['password'] = Hash::make($request->password);
            }

            // Actualizar usuario
            $staff->user->update($userData);

            // Actualizar rol - usar detach/attach en lugar de syncRoles para evitar problemas de cache
            $staff->user->roles()->detach();
            $staff->user->roles()->attach($request->role_id);

            // Actualizar staff
            $staff->update([
                'phone' => $request->phone,
                'specialty' => $request->specialty,
                'license_number' => $request->license_number,
                'hire_date' => $request->hire_date,
                'salary' => $request->salary,
                'is_active' => $request->has('is_active'),
            ]);
        });

        return redirect()->route('staff.index')->with('success', 'Personal actualizado exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Staff $staff)
    {
        try {
            // Verificar si hay citas asociadas
            $appointmentsCount = $staff->appointments()->count();
            if ($appointmentsCount > 0) {
                return redirect()->route('staff.index')
                    ->with('error', 'No se puede eliminar este personal porque tiene ' . $appointmentsCount . ' citas asociadas. Primero debe reasignar o cancelar las citas.');
            }

            // Verificar si hay historiales médicos asociados
            $medicalRecordsCount = $staff->medicalRecords()->count();
            if ($medicalRecordsCount > 0) {
                return redirect()->route('staff.index')
                    ->with('error', 'No se puede eliminar este personal porque tiene ' . $medicalRecordsCount . ' historiales médicos asociados.');
            }

            DB::transaction(function () use ($staff) {
                $staff->delete();
                $staff->user->delete();
            });

            return redirect()->route('staff.index')->with('success', 'Personal eliminado exitosamente.');

        } catch (\Exception $e) {
            \Log::error('Error deleting staff member', ['staff_id' => $staff->id, 'error' => $e->getMessage()]);
            
            return redirect()->route('staff.index')
                ->with('error', 'No se puede eliminar este personal porque tiene registros asociados en el sistema.');
        }
    }

    /**
     * Exportar personal a Excel
     */
    public function exportExcel(Request $request)
    {
        $filters = $request->only(['search', 'specialty', 'is_active']);
        
        $filename = 'personal_' . now()->format('Y-m-d_H-i-s') . '.xlsx';
        
        return Excel::download(new StaffExport($filters), $filename);
    }

    /**
     * Exportar personal a PDF
     */
    public function exportPdf(Request $request)
    {
        $filters = $request->only(['search', 'specialty', 'is_active']);
        
        // Obtener personal con filtros aplicados
        $query = Staff::with(['user.roles']);

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->whereHas('user', function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            })->orWhere('specialty', 'like', "%{$search}%");
        }

        if (!empty($filters['specialty'])) {
            $query->where('specialty', $filters['specialty']);
        }

        if (!empty($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        $staff = $query->orderBy('created_at', 'desc')->get();
        
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
}
