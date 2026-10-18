<?php

namespace App\Services;

use App\Models\DetalleVenta;
use App\Models\Lote;
use App\Models\Medicamento;
use App\Models\Venta;
use Exception;
use Illuminate\Support\Facades\DB;

class VentaService
{
    /**
     * Registra una venta descontando stock según estrategia FIFO
     * y bloqueando estrictamente lotes vencidos.
     *
     * @param  array  $items  Lista de items: [['medicamento_id' => int, 'cantidad' => int], ...]
     * @return Venta
     *
     * @throws Exception
     */
    public function crearVenta(array $items): Venta
    {
        return DB::transaction(function () use ($items) {
            $venta = Venta::create([
                'fecha_venta' => now(),
                'monto_total' => 0,
            ]);

            $montoTotal = 0;

            foreach ($items as $item) {
                $medicamento = Medicamento::findOrFail($item['medicamento_id']);
                $cantidadRequerida = (int) $item['cantidad'];

                if ($cantidadRequerida <= 0) {
                    throw new Exception("La cantidad para {$medicamento->nombre_comercial} debe ser mayor a 0.");
                }

                // Regla #3: Bloquear estrictamente la venta de lotes vencidos.
                // FIFO: Obtener solo lotes vigentes ordenados por fecha de vencimiento ascendente.
                $lotesDisponibles = Lote::where('medicamento_id', $medicamento->id)
                    ->where('fecha_vencimiento', '>=', now()->toDateString())
                    ->where('cantidad_stock', '>', 0)
                    ->orderBy('fecha_vencimiento', 'asc')
                    ->lockForUpdate()
                    ->get();

                $stockTotalDisponible = $lotesDisponibles->sum('cantidad_stock');

                if ($stockTotalDisponible < $cantidadRequerida) {
                    throw new Exception(
                        "Stock insuficiente para {$medicamento->nombre_comercial}. " .
                        "Disponible vigente: {$stockTotalDisponible}, Solicitado: {$cantidadRequerida}."
                    );
                }

                // Descontar por FIFO
                foreach ($lotesDisponibles as $lote) {
                    if ($cantidadRequerida <= 0) {
                        break;
                    }

                    // Bloqueo de seguridad: No vender vencidos
                    if ($lote->fecha_vencimiento->lt(now()->startOfDay())) {
                        throw new Exception("Bloqueo de seguridad: El lote {$lote->numero_lote} está vencido.");
                    }

                    $descontar = min($lote->cantidad_stock, $cantidadRequerida);

                    DetalleVenta::create([
                        'venta_id' => $venta->id,
                        'lote_id' => $lote->id,
                        'cantidad' => $descontar,
                        'precio_unitario' => $medicamento->precio_venta,
                    ]);

                    $lote->decrement('cantidad_stock', $descontar);
                    $montoTotal += $descontar * $medicamento->precio_venta;
                    $cantidadRequerida -= $descontar;
                }
            }

            $venta->update(['monto_total' => $montoTotal]);

            return $venta->load('detalles.lote.medicamento');
        });
    }
}
