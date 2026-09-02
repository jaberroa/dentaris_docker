<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'address',
        'birth_date',
        'gender',
        'specialty',
        'license_number',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'birth_date' => 'date',
            'is_active' => 'boolean',
            'google2fa_enabled' => 'boolean',
            'is_locked' => 'boolean',
            'backup_codes' => 'array',
            'last_login_at' => 'datetime',
            'google2fa_enabled_at' => 'datetime',
            'locked_until' => 'datetime',
        ];
    }

    /**
     * Relación uno a uno con staff
     */
    public function staff()
    {
        return $this->hasOne(Staff::class);
    }

    /**
     * Relación muchos a muchos con roles
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }

    /**
     * Verificar si el usuario tiene un rol específico
     */
    public function hasRole($role)
    {
        if (is_string($role)) {
            return $this->roles->contains('name', $role);
        }
        return $this->roles->contains($role);
    }

    /**
     * Verificar si el usuario tiene alguno de los roles especificados
     */
    public function hasAnyRole($roles)
    {
        if (is_array($roles)) {
            foreach ($roles as $role) {
                if ($this->hasRole($role)) {
                    return true;
                }
            }
        } else {
            return $this->hasRole($roles);
        }
        return false;
    }

    /**
     * Verificar si el usuario es doctor
     */
    public function isDoctor()
    {
        return $this->hasRole('doctor');
    }

    /**
     * Verificar si el usuario es admin
     */
    public function isAdmin()
    {
        return $this->hasRole('admin');
    }

    /**
     * Asignar un rol al usuario
     */
    public function assignRole($role)
    {
        if (is_string($role)) {
            $role = Role::where('name', $role)->first();
        }
        
        if ($role && !$this->hasRole($role)) {
            $this->roles()->attach($role->id);
        }
        
        return $this;
    }

    /**
     * Sincronizar roles del usuario
     */
    public function syncRoles($roles)
    {
        $roleIds = [];
        
        foreach ($roles as $role) {
            if (is_string($role)) {
                $role = Role::where('name', $role)->first();
                if ($role) {
                    $roleIds[] = $role->id;
                }
            } elseif (is_numeric($role)) {
                $roleIds[] = (int)$role;
            } elseif (is_object($role) && isset($role->id)) {
                $roleIds[] = $role->id;
            }
        }
        
        $this->roles()->sync($roleIds);
        return $this;
    }

    /**
     * Verificar si el usuario tiene un permiso específico
     */
    public function hasPermission($permission)
    {
        // Si el usuario es admin, tiene todos los permisos
        if ($this->isAdmin()) {
            return true;
        }

        // Verificar permisos en todos los roles del usuario
        foreach ($this->roles as $role) {
            if ($role->hasPermission($permission)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Método can() requerido por el middleware de autorización de Laravel
     */
    public function can($permission, $arguments = [])
    {
        if ($arguments !== [] && $arguments !== null) {
            return parent::can($permission, $arguments);
        }

        return $this->hasPermission($permission);
    }

    /**
     * Get the security audit logs for the user
     */
    public function securityAuditLogs()
    {
        return $this->hasMany(SecurityAuditLog::class);
    }
}
