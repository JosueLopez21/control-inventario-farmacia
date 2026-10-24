<?php

namespace App\Http\Controllers;

use App\Models\MovimientoInventario;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MovimientoController extends Controller
{
    private const CARACTERES_ACENTUADOS = 'áàäâãéèëêíìïîóòöôõúùüûñç';

    private const CARACTERES_SIN_ACENTO = 'aaaaaeeeeiiiiooooouuuunc';

    public function index(Request $request): View
    {
        $q = trim((string) $request->query('q'));
        $tipo = (string) $request->query('tipo');
        $ventaId = $request->integer('venta') ?: null;
        $desde = $this->fecha($request->query('desde'));
        $hasta = $this->fecha($request->query('hasta'));

        $consulta = MovimientoInventario::query()
            ->with('usuario:id,name')
            ->when($q !== '', function ($query) use ($q) {
                $termino = '%'.addcslashes($this->normalizar($q), '%_\\').'%';
                $nombre = "translate(lower(medicamento_nombre), '".self::CARACTERES_ACENTUADOS."', '".self::CARACTERES_SIN_ACENTO."')";

                $query->where(function ($sub) use ($termino, $nombre) {
                    $sub->whereRaw("{$nombre} LIKE ?", [$termino])
                        ->orWhereRaw('lower(numero_lote) LIKE ?', [$termino]);
                });
            })
            ->when(array_key_exists($tipo, MovimientoInventario::TIPOS), fn ($query) => $query->where('tipo', $tipo))
            ->when($ventaId, fn ($query) => $query->where('venta_id', $ventaId))
            ->when($desde, fn ($query) => $query->where('created_at', '>=', $desde.' 00:00:00'))
            ->when($hasta, fn ($query) => $query->where('created_at', '<=', $hasta.' 23:59:59'));

        $totales = (clone $consulta)
            ->selectRaw('count(*) as movimientos, coalesce(sum(case when cantidad < 0 then -cantidad else 0 end), 0) as salidas, coalesce(sum(case when cantidad > 0 then cantidad else 0 end), 0) as entradas')
            ->first();

        $movimientos = $consulta
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('movimientos.index', [
            'movimientos' => $movimientos,
            'totales' => $totales,
            'q' => $q,
            'tipo' => $tipo,
            'ventaId' => $ventaId,
            'desde' => $desde,
            'hasta' => $hasta,
        ]);
    }

    private function fecha(mixed $valor): ?string
    {
        $valor = (string) $valor;

        return preg_match('/^\d{4}-\d{2}-\d{2}$/', $valor) === 1 && strtotime($valor) !== false ? $valor : null;
    }

    private function normalizar(string $texto): string
    {
        return strtr(mb_strtolower($texto), [
            'á' => 'a', 'à' => 'a', 'ä' => 'a', 'â' => 'a', 'ã' => 'a',
            'é' => 'e', 'è' => 'e', 'ë' => 'e', 'ê' => 'e',
            'í' => 'i', 'ì' => 'i', 'ï' => 'i', 'î' => 'i',
            'ó' => 'o', 'ò' => 'o', 'ö' => 'o', 'ô' => 'o', 'õ' => 'o',
            'ú' => 'u', 'ù' => 'u', 'ü' => 'u', 'û' => 'u',
            'ñ' => 'n', 'ç' => 'c',
        ]);
    }
}
