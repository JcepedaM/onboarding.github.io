<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PuestoTrabajo extends Model
{
    protected $table = 'puestos_trabajo';

    protected $fillable = [
        'numero_puesto',
        'piso',
        'seccion',
        'capacidad',
        'estado',
        'ubicacion_x',
        'ubicacion_y',
        'descripcion',
        'equipamiento',
        'notas'
    ];

    protected $casts = [
        'equipamiento' => 'array',
        'ubicacion_x' => 'integer',
        'ubicacion_y' => 'integer',
    ];

    /**
     * Estados disponibles
     */
    public static function obtenerEstados()
    {
        return [
            'Disponible' => 'Disponible',
            'Asignado' => 'Asignado',
            'En Mantenimiento' => 'En Mantenimiento',
            'Bloqueado' => 'Bloqueado',
        ];
    }

    /**
     * Relación con solicitudes de Servicios Generales
     */
    public function solicitudes()
    {
        return $this->hasMany(Solicitud::class, 'puesto_trabajo_id');
    }

    /**
     * Verificar si el puesto está disponible
     */
    public function estaDisponible()
    {
        return $this->estado === 'Disponible' && !$this->solicitudes()->where('estado', '!=', 'Cancelado')->exists();
    }

    /**
     * Obtener puesto ocupado por empleado
     */
    public function empleadoActual()
    {
        return $this->solicitudes()
            ->where('estado', '!=', 'Cancelado')
            ->latest()
            ->first();
    }
}
