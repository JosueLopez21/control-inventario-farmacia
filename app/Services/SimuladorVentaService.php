<?php

namespace App\Services;

use App\Models\Medicamento;
use App\Models\Venta;
use Illuminate\Support\Collection;
use RuntimeException;

class SimuladorVentaService
{
    public function __construct(private readonly VentaService $ventas)
    {
    }

    /**
     * Genera una venta demostrativa con entre uno y tres medicamentos con stock vigente.
     */
    public function crear(): Venta
    {
        $candidatos = Medicamento::query()
            ->whereHas('lotes', fn ($query) => $query
                ->where('fecha_vencimiento', '>=', now()->toDateString())
                ->where('cantidad_stock', '>', 0))
            ->withSum(['lotes as stock_vigente' => fn ($query) => $query
                ->where('fecha_vencimiento', '>=', now()->toDateString())], 'cantidad_stock')
            ->inRandomOrder()
            ->limit(3)
            ->get()
            ->filter(fn (Medicamento $medicamento) => (int) ($medicamento->stock_vigente ?? 0) > 0)
            ->values();

        if ($candidatos->isEmpty()) {
            throw new RuntimeException('No hay medicamentos con stock vigente para simular una venta.');
        }

        $items = $this->items($candidatos);

        return $this->ventas->crearVenta($items);
    }

    /**
     * @param  Collection<int, Medicamento>  $candidatos
     * @return array<int, array{medicamento_id: int, cantidad: int}>
     */
    private function items(Collection $candidatos): array
    {
        $cantidadMedicamentos = random_int(1, min(3, $candidatos->count()));

        return $candidatos
            ->take($cantidadMedicamentos)
            ->map(function (Medicamento $medicamento) {
                $stock = (int) $medicamento->stock_vigente;

                return [
                    'medicamento_id' => $medicamento->id,
                    'cantidad' => random_int(1, min(3, $stock)),
                ];
            })
            ->all();
    }
}
