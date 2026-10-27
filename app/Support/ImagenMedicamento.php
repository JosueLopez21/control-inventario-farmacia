<?php

namespace App\Support;

use App\Models\Medicamento;
use Illuminate\Support\Str;

/**
 * Busca la imagen de un medicamento boliviano en public/img/medicamentos.
 *
 * Las imágenes se comparten por forma farmacéutica y laboratorio, con el nombre
 * "{forma}_{laboratorio}.png" (por ejemplo tabletas_inti.png). Si el archivo no existe,
 * devuelve null y las vistas muestran el icono de la categoría.
 */
class ImagenMedicamento
{
    private const CARPETA = 'img/medicamentos';

    private const EXTENSIONES = ['png', 'webp', 'jpg', 'jpeg'];

    private const LABORATORIOS = ['inti', 'lafar', 'cofar', 'vita', 'bago'];

    /** @var array<string, string|null> */
    private static array $cache = [];

    public static function url(?Medicamento $medicamento): ?string
    {
        if (! $medicamento) {
            return null;
        }

        $laboratorio = $medicamento->laboratorio;

        if (! $laboratorio || $laboratorio->pais !== 'Bolivia') {
            return null;
        }

        $clave = self::claveLaboratorio($laboratorio->nombre);
        $forma = self::forma($medicamento->presentacion);

        if (! $clave || ! $forma) {
            return null;
        }

        $base = "{$forma}_{$clave}";

        if (! array_key_exists($base, self::$cache)) {
            self::$cache[$base] = null;

            foreach (self::EXTENSIONES as $extension) {
                $ruta = self::CARPETA."/{$base}.{$extension}";

                if (is_file(public_path($ruta))) {
                    self::$cache[$base] = asset($ruta).'?v='.filemtime(public_path($ruta));
                    break;
                }
            }
        }

        return self::$cache[$base];
    }

    public static function forma(?string $presentacion): ?string
    {
        $texto = Str::lower((string) $presentacion);

        return match (true) {
            str_contains($texto, 'inhalador') => 'inhalador',
            str_contains($texto, 'frasco ampolla'), str_contains($texto, 'frasco 100 ui') => 'frasco-ampolla',
            str_contains($texto, 'ampolla') => 'ampollas',
            str_contains($texto, 'sobre') => 'sobre',
            str_contains($texto, 'gotas') => 'gotas',
            str_contains($texto, 'jarabe'), str_contains($texto, 'suspensi') => 'jarabe',
            str_contains($texto, 'crema'), str_contains($texto, 'gel'), str_contains($texto, 'ungüento'), str_contains($texto, 'pomada') => 'crema',
            str_contains($texto, 'cápsula') => 'capsulas',
            str_contains($texto, 'tableta') => 'tabletas',
            default => null,
        };
    }

    private static function claveLaboratorio(string $nombre): ?string
    {
        $slug = Str::slug($nombre);

        foreach (self::LABORATORIOS as $clave) {
            if (str_contains($slug, $clave)) {
                return $clave;
            }
        }

        return null;
    }
}
