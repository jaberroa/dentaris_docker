<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    /**
     * Mostrar formulario de login
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Procesar login
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ], [
            'email.required' => 'El email es obligatorio.',
            'email.email' => 'El email debe ser válido.',
            'password.required' => 'La contraseña es obligatoria.',
        ]);

        $credentials = $request->only('email', 'password');
        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();

            $user = Auth::user();
            
            if (!$user->is_active) {
                Auth::logout();
                return back()->withErrors([
                    'email' => 'Tu cuenta está desactivada. Contacta al administrador.',
                ]);
            }

            // Log de acceso
            activity()
                ->performedOn($user)
                ->log('Usuario inició sesión');

            return redirect()->intended(route('dashboard'))
                ->with('success', '¡Bienvenido, ' . $user->name . '!');
        }

        return back()->withErrors([
            'email' => 'Las credenciales no coinciden con nuestros registros.',
        ])->onlyInput('email');
    }

    /**
     * Mostrar formulario de registro
     */
    public function showRegisterForm()
    {
        $roles = Role::where('is_active', true)->get();
        return view('auth.register', compact('roles'));
    }

    /**
     * Procesar registro
     */
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', 'confirmed', Password::defaults()],
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'birth_date' => 'nullable|date|before:today',
            'gender' => 'nullable|in:male,female,other',
            'specialty' => 'nullable|string|max:255',
            'license_number' => 'nullable|string|max:50',
            'roles' => 'required|array|min:1',
            'roles.*' => 'exists:roles,id',
        ], [
            'name.required' => 'El nombre es obligatorio.',
            'email.required' => 'El email es obligatorio.',
            'email.email' => 'El email debe ser válido.',
            'email.unique' => 'Este email ya está registrado.',
            'password.required' => 'La contraseña es obligatoria.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
            'roles.required' => 'Debe seleccionar al menos un rol.',
            'roles.array' => 'Los roles deben ser válidos.',
        ]);

        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'phone' => $request->phone,
                'address' => $request->address,
                'birth_date' => $request->birth_date,
                'gender' => $request->gender,
                'specialty' => $request->specialty,
                'license_number' => $request->license_number,
                'is_active' => true,
                'email_verified_at' => now(),
            ]);

            // Asignar roles
            $user->roles()->attach($request->roles);

            // Log de registro
            activity()
                ->performedOn($user)
                ->log('Usuario se registró');

            // Login automático
            Auth::login($user);

            return redirect()->route('dashboard')
                ->with('success', '¡Registro exitoso! Bienvenido a Dentaris.');

        } catch (\Exception $e) {
            return back()->withErrors([
                'error' => 'Ocurrió un error durante el registro. Inténtalo de nuevo.',
            ])->withInput($request->except('password', 'password_confirmation'));
        }
    }

    /**
     * Logout
     */
    public function logout(Request $request)
    {
        $user = Auth::user();
        
        // Log de salida
        if ($user) {
            activity()
                ->performedOn($user)
                ->log('Usuario cerró sesión');
        }

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('success', 'Has cerrado sesión correctamente.');
    }

    public function showForgotPasswordForm()
    {
        return view('auth.forgot-password');
    }

    public function sendResetLinkEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ], [
            'email.required' => 'El email es obligatorio.',
            'email.email' => 'El email debe ser válido.',
            'email.exists' => 'No encontramos un usuario con ese email.',
        ]);

        // TODO: Implementar envío de email de recuperación
        return back()->with('success', 'Si el email existe, recibirás un enlace de recuperación.');
    }

    public function showResetPasswordForm(Request $request, $token = null)
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->email,
        ]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => ['required', 'confirmed', Password::defaults()],
        ], [
            'token.required' => 'El token es obligatorio.',
            'email.required' => 'El email es obligatorio.',
            'email.email' => 'El email debe ser válido.',
            'password.required' => 'La contraseña es obligatoria.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
        ]);

        // TODO: Implementar lógica de reset de contraseña
        return redirect()->route('login')
            ->with('success', 'Tu contraseña ha sido restablecida. Puedes iniciar sesión.');
    }

    public function showChangePasswordForm()
    {
        return view('auth.change-password');
    }

    public function changePassword(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'current_password' => 'required|current_password',
            'password' => ['required', 'confirmed', Password::defaults()],
        ], [
            'current_password.required' => 'La contraseña actual es obligatoria.',
            'current_password.current_password' => 'La contraseña actual es incorrecta.',
            'password.required' => 'La nueva contraseña es obligatoria.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
        ]);

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        // Log de cambio de contraseña
        activity()
            ->performedOn($user)
            ->log('Usuario cambió su contraseña');

        return back()->with('success', 'Tu contraseña ha sido actualizada correctamente.');
    }

    public function showProfile()
    {
        $user = Auth::user();
        $user->load('roles');
        return view('auth.profile', compact('user'));
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'birth_date' => 'nullable|date|before:today',
            'gender' => 'nullable|in:male,female,other',
            'specialty' => 'nullable|string|max:255',
            'license_number' => 'nullable|string|max:50',
        ], [
            'name.required' => 'El nombre es obligatorio.',
            'email.required' => 'El email es obligatorio.',
            'email.email' => 'El email debe ser válido.',
            'email.unique' => 'Este email ya está registrado.',
        ]);

        $user->update($request->only([
            'name', 'email', 'phone', 'address', 'birth_date', 'gender', 'specialty', 'license_number'
        ]));

        // Log de actualización de perfil
        activity()
            ->performedOn($user)
            ->log('Usuario actualizó su perfil');

        return back()->with('success', 'Tu perfil ha sido actualizado correctamente.');
    }
}
