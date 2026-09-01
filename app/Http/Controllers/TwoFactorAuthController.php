<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use PragmaRX\Google2FALaravel\Support\Authenticator;
use App\Services\SecurityAuditService;
use App\Models\User;
use Google2FA;

class TwoFactorAuthController extends Controller
{
    protected $securityAuditService;

    public function __construct(SecurityAuditService $securityAuditService)
    {
        $this->securityAuditService = $securityAuditService;
    }

    /**
     * Show 2FA setup page
     */
    public function showSetup()
    {
        $user = Auth::user();
        
        if ($user->google2fa_enabled) {
            return redirect()->route('dashboard')->with('info', '2FA ya está habilitado.');
        }

        // Generate secret key
        $secretKey = Google2FA::generateSecretKey();
        $user->update(['google2fa_secret' => $secretKey]);

        // Generate QR code
        $qrCodeUrl = Google2FA::getQRCodeUrl(
            config('app.name'),
            $user->email,
            $secretKey
        );

        // Generate backup codes
        $backupCodes = $this->generateBackupCodes();
        $user->update(['backup_codes' => $backupCodes]);

        return view('auth.2fa.setup', compact('qrCodeUrl', 'secretKey', 'backupCodes'));
    }

    /**
     * Enable 2FA
     */
    public function enable(Request $request)
    {
        $request->validate([
            'code' => 'required|string|size:6',
        ]);

        $user = Auth::user();
        $secretKey = $user->google2fa_secret;

        if (!Google2FA::verifyKey($secretKey, $request->code)) {
            $this->securityAuditService->logFailedLogin(
                $user->email,
                $request,
                'Invalid 2FA code during setup'
            );

            return back()->withErrors(['code' => 'Código 2FA inválido.']);
        }

        // Enable 2FA
        $user->update([
            'google2fa_enabled' => true,
            'google2fa_enabled_at' => now(),
        ]);

        $this->securityAuditService->log2FAEnabled($user, $request);

        return redirect()->route('dashboard')
            ->with('success', 'Autenticación de dos factores habilitada exitosamente.');
    }

    /**
     * Show 2FA verification page
     */
    public function showVerification()
    {
        if (!session('2fa_required')) {
            return redirect()->route('login');
        }

        return view('auth.2fa.verify');
    }

    /**
     * Verify 2FA code
     */
    public function verify(Request $request)
    {
        $request->validate([
            'code' => 'required|string|size:6',
        ]);

        $user = Auth::user();
        $secretKey = $user->google2fa_secret;

        if (Google2FA::verifyKey($secretKey, $request->code)) {
            // 2FA successful
            session()->forget('2fa_required');
            session()->put('2fa_verified', true);

            $this->securityAuditService->logSuccessfulLogin($user, $request);

            return redirect()->intended(route('dashboard'))
                ->with('success', 'Autenticación exitosa.');
        }

        // Check backup codes
        if ($this->verifyBackupCode($user, $request->code)) {
            session()->forget('2fa_required');
            session()->put('2fa_verified', true);

            $this->securityAuditService->logSuccessfulLogin($user, $request);

            return redirect()->intended(route('dashboard'))
                ->with('success', 'Autenticación exitosa con código de respaldo.');
        }

        $this->securityAuditService->logFailedLogin(
            $user->email,
            $request,
            'Invalid 2FA code during verification'
        );

        return back()->withErrors(['code' => 'Código 2FA inválido.']);
    }

    /**
     * Disable 2FA
     */
    public function disable(Request $request)
    {
        $request->validate([
            'password' => 'required|string',
            'code' => 'required|string|size:6',
        ]);

        $user = Auth::user();

        // Verify password
        if (!Hash::check($request->password, $user->password)) {
            return back()->withErrors(['password' => 'Contraseña incorrecta.']);
        }

        // Verify 2FA code
        if (!Google2FA::verifyKey($user->google2fa_secret, $request->code)) {
            return back()->withErrors(['code' => 'Código 2FA inválido.']);
        }

        // Disable 2FA
        $user->update([
            'google2fa_enabled' => false,
            'google2fa_secret' => null,
            'backup_codes' => null,
        ]);

        $this->securityAuditService->log2FADisabled($user, $request);

        return redirect()->route('profile')
            ->with('success', 'Autenticación de dos factores deshabilitada.');
    }

    /**
     * Regenerate backup codes
     */
    public function regenerateBackupCodes(Request $request)
    {
        $request->validate([
            'password' => 'required|string',
        ]);

        $user = Auth::user();

        if (!Hash::check($request->password, $user->password)) {
            return back()->withErrors(['password' => 'Contraseña incorrecta.']);
        }

        $backupCodes = $this->generateBackupCodes();
        $user->update(['backup_codes' => $backupCodes]);

        return back()->with('success', 'Códigos de respaldo regenerados.')
            ->with('backup_codes', $backupCodes);
    }

    /**
     * Generate backup codes
     */
    protected function generateBackupCodes(): array
    {
        $codes = [];
        for ($i = 0; $i < 10; $i++) {
            $codes[] = strtoupper(substr(md5(uniqid()), 0, 8));
        }
        return $codes;
    }

    /**
     * Verify backup code
     */
    protected function verifyBackupCode(User $user, string $code): bool
    {
        $backupCodes = $user->backup_codes ?? [];
        
        if (in_array($code, $backupCodes)) {
            // Remove used backup code
            $backupCodes = array_diff($backupCodes, [$code]);
            $user->update(['backup_codes' => array_values($backupCodes)]);
            
            return true;
        }

        return false;
    }

    /**
     * Check if 2FA is required for user
     */
    public static function is2FARequired(User $user): bool
    {
        return $user->google2fa_enabled && !session('2fa_verified');
    }

    /**
     * API: Get 2FA status
     */
    public function getStatus(): JsonResponse
    {
        $user = Auth::user();
        
        return response()->json([
            'enabled' => $user->google2fa_enabled,
            'enabled_at' => $user->google2fa_enabled_at,
            'backup_codes_count' => count($user->backup_codes ?? []),
        ]);
    }

    /**
     * API: Setup 2FA
     */
    public function apiSetup(): JsonResponse
    {
        $user = Auth::user();
        
        if ($user->google2fa_enabled) {
            return response()->json(['error' => '2FA already enabled'], 400);
        }

        $secretKey = Google2FA::generateSecretKey();
        $user->update(['google2fa_secret' => $secretKey]);

        $qrCodeUrl = Google2FA::getQRCodeUrl(
            config('app.name'),
            $user->email,
            $secretKey
        );

        $backupCodes = $this->generateBackupCodes();
        $user->update(['backup_codes' => $backupCodes]);

        return response()->json([
            'secret_key' => $secretKey,
            'qr_code_url' => $qrCodeUrl,
            'backup_codes' => $backupCodes,
        ]);
    }

    /**
     * API: Enable 2FA
     */
    public function apiEnable(Request $request): JsonResponse
    {
        $request->validate([
            'code' => 'required|string|size:6',
        ]);

        $user = Auth::user();
        $secretKey = $user->google2fa_secret;

        if (!Google2FA::verifyKey($secretKey, $request->code)) {
            return response()->json(['error' => 'Invalid 2FA code'], 400);
        }

        $user->update([
            'google2fa_enabled' => true,
            'google2fa_enabled_at' => now(),
        ]);

        $this->securityAuditService->log2FAEnabled($user, $request);

        return response()->json(['message' => '2FA enabled successfully']);
    }

    /**
     * API: Verify 2FA
     */
    public function apiVerify(Request $request): JsonResponse
    {
        $request->validate([
            'code' => 'required|string|size:6',
        ]);

        $user = Auth::user();
        $secretKey = $user->google2fa_secret;

        if (Google2FA::verifyKey($secretKey, $request->code)) {
            session()->put('2fa_verified', true);
            return response()->json(['message' => '2FA verification successful']);
        }

        if ($this->verifyBackupCode($user, $request->code)) {
            session()->put('2fa_verified', true);
            return response()->json(['message' => '2FA verification successful with backup code']);
        }

        return response()->json(['error' => 'Invalid 2FA code'], 400);
    }
}
