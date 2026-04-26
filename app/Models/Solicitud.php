<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Solicitud extends Model
{
    protected $table = 'solicitudes';

    protected $casts = [
        'fecha_limite' => 'date',
        'fecha_finalizacion' => 'datetime',
    ];
    protected $fillable = [
        'proceso_ingreso_id',
        'area_id',
        'puesto_trabajo_id',
        'tipo',
        'fecha_limite',
        'fecha_finalizacion',
        'estado',
        'observaciones'
    ];

    public function proceso()
    {
        return $this->belongsTo(ProcesoIngreso::class, 'proceso_ingreso_id');
    }

    public function area()
    {
        return $this->belongsTo(Area::class);
    }

    public function detalleTecnologia()
    {
        return $this->hasOne(DetalleTecnologia::class);
    }

    public function detalleUniforme()
    {
        return $this->hasOne(DetalleUniforme::class);
    }

    public function detalleBienes()
    {
        return $this->hasOne(DetalleBienes::class);
    }

    public function puestoTrabajo()
    {
        return $this->belongsTo(PuestoTrabajo::class);
    }

    public function cursos()
    {
        return $this->belongsToMany(Curso::class, 'solicitud_curso');
    }

    /**
     * Obtener los estados posibles para una solicitud
     */
    public static function obtenerEstados()
    {
        return [
            'Pendiente' => 'Pendiente',
            'En Proceso' => 'En Proceso',
            'Finalizada' => 'Finalizada',
        ];
    }

    /**
     * Verificar si la solicitud está completa
     */
    public function estaCompleta()
    {
        // Si es una solicitud de TI, necesita detalles técnicos
        if ($this->tipo === 'Tecnología') {
            return $this->detalleTecnologia()->exists();
        }

        // Si es una solicitud de uniformes, necesita tallas
        if ($this->tipo === 'Dotación') {
            return $this->detalleUniforme()->exists();
        }

        return true;
    }

    /**
     * Registrar entrega de insumo
     */
    public function marcarEntregado($observaciones = null)
    {
        $this->update([
            'estado' => 'Entregado',
            'observaciones' => $observaciones,
        ]);
        return $this;
    }

    /**
     * Marcar como en proceso
     */
    public function marcarEnProceso()
    {
        if ($this->estado === 'Pendiente') {
            $this->update(['estado' => 'En Proceso']);
        }
        return $this;
    }
}