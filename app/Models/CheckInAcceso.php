<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CheckInAcceso extends Model
{
    use HasFactory;

    protected $table = 'checkin_accesos';

    protected $fillable = [
        'usuario_id',
        'area_id',
        'fecha_acceso',
        'hora_acceso',
        'ip_address',
        'user_agent',
        'dispositivo_tipo',
        'navegador',
        'latitud',
        'longitud',
        'nota',
    ];

    protected $casts = [
        'fecha_acceso' => 'date',
    ];

    /**
     * Relación con User
     */
    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    /**
     * Relación con Area
     */
    public function area()
    {
        return $this->belongsTo(Area::class, 'area_id');
    }

    /**
     * Scope para obtener accesos recientes
     */
    public function scopeRecientes($query, $dias = 7)
    {
        return $query->where('fecha_acceso', '>=', now()->subDays($dias))
                     ->orderBy('fecha_acceso', 'desc')
                     ->orderBy('hora_acceso', 'desc');
    }

    /**
     * Scope para obtener accesos de un usuario
     */
    public function scopePorUsuario($query, $usuarioId)
    {
        return $query->where('usuario_id', $usuarioId)
                     ->orderBy('fecha_acceso', 'desc')
                     ->orderBy('hora_acceso', 'desc');
    }

    /**
     * Scope para obtener accesos de un área
     */
    public function scopePorArea($query, $areaId)
    {
        return $query->where('area_id', $areaId)
                     ->orderBy('fecha_acceso', 'desc')
                     ->orderBy('hora_acceso', 'desc');
    }

    /**
     * Scope para obtener accesos de hoy
     */
    public function scopeDeHoy($query)
    {
        return $query->whereDate('fecha_acceso', today());
    }

    /**
     * Obtener último acceso del usuario
     */
    public static function ultimoAcceso($usuarioId)
    {
        return self::where('usuario_id', $usuarioId)
                   ->orderBy('fecha_acceso', 'desc')
                   ->orderBy('hora_acceso', 'desc')
                   ->first();
    }

    /**
     * Registrar un nuevo check-in
     */
    public static function registrar($usuarioId, $areaId, $ip = null, $userAgent = null)
    {
        return self::create([
            'usuario_id' => $usuarioId,
            'area_id' => $areaId,
            'fecha_acceso' => now()->toDateString(),
            'hora_acceso' => now()->toTimeString(),
            'ip_address' => $ip ?? request()->ip(),
            'user_agent' => $userAgent ?? request()->header('User-Agent'),
            'dispositivo_tipo' => self::detectarDispositivo(),
            'navegador' => self::detectarNavegador(),
        ]);
    }

    /**
     * Detectar tipo de dispositivo
     */
    private static function detectarDispositivo()
    {
        $userAgent = request()->header('User-Agent');
        
        if (preg_match('/Mobile/', $userAgent)) {
            return 'Móvil';
        } elseif (preg_match('/Tablet/', $userAgent)) {
            return 'Tablet';
        }
        
        return 'Escritorio';
    }

    /**
     * Detectar navegador
     */
    private static function detectarNavegador()
    {
        $userAgent = request()->header('User-Agent');
        
        if (preg_match('/Chrome/', $userAgent)) {
            return 'Chrome';
        } elseif (preg_match('/Safari/', $userAgent)) {
            return 'Safari';
        } elseif (preg_match('/Firefox/', $userAgent)) {
            return 'Firefox';
        } elseif (preg_match('/Edge/', $userAgent)) {
            return 'Edge';
        }
        
        return 'Otro';
    }

    /**
     * Contar accesos en un período
     */
    public static function contarAccesosPeriodo($usuarioId, $inicio, $fin)
    {
        return self::where('usuario_id', $usuarioId)
                   ->whereBetween('fecha_acceso', [$inicio, $fin])
                   ->count();
    }

    /**
     * Obtener estadísticas por área
     */
    public static function estadisticasPorArea($areaId, $dias = 30)
    {
        return self::where('area_id', $areaId)
                   ->where('fecha_acceso', '>=', now()->subDays($dias)->toDateString())
                   ->select('usuario_id')
                   ->distinct()
                   ->count();
    }
}
