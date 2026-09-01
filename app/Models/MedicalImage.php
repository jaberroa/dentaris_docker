<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MedicalImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'medical_record_id',
        'image_type',
        'image_name',
        'file_path',
        'file_name',
        'mime_type',
        'file_size',
        'width',
        'height',
        'description',
        'anatomical_location',
        'findings',
        'is_processed',
        'metadata',
        'uploaded_by',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'is_processed' => 'boolean',
        ];
    }

    /**
     * Relación con el registro médico
     */
    public function medicalRecord()
    {
        return $this->belongsTo(MedicalRecord::class);
    }

    /**
     * Relación con el usuario que subió la imagen
     */
    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * Obtener el tamaño del archivo formateado
     */
    public function getFormattedSizeAttribute()
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Obtener las dimensiones de la imagen
     */
    public function getDimensionsAttribute()
    {
        if ($this->width && $this->height) {
            return $this->width . 'x' . $this->height;
        }
        return 'N/A';
    }

    /**
     * Verificar si es una radiografía
     */
    public function isRadiograph()
    {
        return $this->image_type === 'radiografia';
    }

    /**
     * Verificar si es una foto clínica
     */
    public function isClinicalPhoto()
    {
        return $this->image_type === 'foto_clinica';
    }

    /**
     * Scope para imágenes por tipo
     */
    public function scopeByType($query, $type)
    {
        return $query->where('image_type', $type);
    }

    /**
     * Scope para imágenes procesadas
     */
    public function scopeProcessed($query)
    {
        return $query->where('is_processed', true);
    }
}
