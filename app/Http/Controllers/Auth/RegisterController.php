<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class RegisterController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(RegisterRequest $request): RedirectResponse
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'address' => $request->address,
            'birth_date' => $request->birth_date,
            'gender' => $request->gender,
            'is_active' => true,
        ]);

        // Asignar rol por defecto
        $defaultRole = \App\Models\Role::where('name', 'receptionist')->first();
        if ($defaultRole) {
            $user->roles()->attach($defaultRole);
        }

        event(new Registered($user));

        Auth::login($user);

        // Registrar actividad
        activity()
            ->causedBy($user)
            ->log('Usuario se registró en el sistema');

        return redirect(route('dashboard'));
    }
}





