<?php

use Carbon\CarbonInterface;

if (! function_exists('categoria_ui')) {
    /**
     * Devuelve el icono y la paleta visual de una categoría terapéutica.
     *
     * @return array{icono: string, color: string, suave: string}
     */
    function categoria_ui(?string $nombre): array
    {
        $categorias = config('feria.categorias', []);
        $clave = \Illuminate\Support\Str::slug((string) $nombre);

        return $categorias[$clave] ?? $categorias['default'] ?? [
            'icono' => 'bi-capsule',
            'color' => '#0f766e',
            'suave' => '#ccfbf1',
        ];
    }
}

if (! function_exists('formato_bs')) {
    /**
     * Formatea un importe monetario en bolivianos.
     */
    function formato_bs(mixed $valor): string
    {
        return 'Bs '.number_format((float) ($valor ?? 0), 2, ',', '.');
    }
}

if (! function_exists('formato_fecha')) {
    /**
     * Formatea una fecha en formato dd/mm/aaaa.
     */
    function formato_fecha(mixed $fecha, string $formato = 'd/m/Y'): string
    {
        if (blank($fecha)) {
            return '—';
        }

        if (! $fecha instanceof CarbonInterface) {
            $fecha = \Illuminate\Support\Carbon::parse($fecha);
        }

        return $fecha->format($formato);
    }
}

if (! function_exists('formato_fecha_hora')) {
    /**
     * Formatea fecha y hora en formato dd/mm/aaaa HH:mm.
     */
    function formato_fecha_hora(mixed $fecha): string
    {
        return formato_fecha($fecha, 'd/m/Y H:i');
    }
}

if (! function_exists('dias_restantes')) {
    /**
     * Días que faltan para una fecha (negativo si ya pasó).
     */
    function dias_restantes(mixed $fecha): int
    {
        $objetivo = $fecha instanceof CarbonInterface
            ? $fecha->copy()->startOfDay()
            : \Illuminate\Support\Carbon::parse($fecha)->startOfDay();

        return (int) now()->startOfDay()->diffInDays($objetivo, false);
    }
}
