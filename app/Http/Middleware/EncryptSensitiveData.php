<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Symfony\Component\HttpFoundation\Response;

class EncryptSensitiveData
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Solo encriptar en producción o cuando esté habilitado
        if (app()->environment('production') || config('security.encrypt_sensitive_data', false)) {
            $this->encryptResponseData($response);
        }

        return $response;
    }

    /**
     * Encrypt sensitive data in response
     */
    protected function encryptResponseData($response): void
    {
        if ($response->headers->get('Content-Type') === 'application/json') {
            $data = json_decode($response->getContent(), true);
            
            if (is_array($data)) {
                $data = $this->encryptSensitiveFields($data);
                $response->setContent(json_encode($data));
            }
        }
    }

    /**
     * Encrypt sensitive fields in data array
     */
    protected function encryptSensitiveFields(array $data): array
    {
        $sensitiveFields = [
            'email',
            'phone',
            'address',
            'emergency_contact_phone',
            'medical_conditions',
            'allergies',
            'medications',
            'notes',
            'payment_method',
            'card_number',
            'cvv',
            'bank_account',
            'social_security_number',
            'insurance_number',
        ];

        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = $this->encryptSensitiveFields($value);
            } elseif (in_array($key, $sensitiveFields) && !empty($value)) {
                $data[$key] = Crypt::encryptString($value);
            }
        }

        return $data;
    }
}