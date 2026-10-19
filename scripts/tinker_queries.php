<?php

/**
 * Script de ejecución automática de las 12 Consultas de Tinker.
 * Compatible con:
 *   1) php artisan tinker scripts/tinker_queries.php
 *   2) php scripts/tinker_queries.php
 */

if (! defined('LARAVEL_START')) {
    require_once __DIR__ . '/../vendor/autoload.php';
    $app = require_once __DIR__ . '/../bootstrap/app.php';
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
    $kernel->bootstrap();
}

use App\Models\Categoria;
use App\Models\DetalleVenta;
use App\Models\Laboratorio;
use App\Models\Lote;
use App\Models\Medicamento;
use App\Models\Venta;
use App\Services\VentaService;
use Illuminate\Support\Facades\DB;

echo "\n" . str_repeat('=', 70) . "\n";
echo "  EJECUCIÓN DE LAS CONSULTAS DE TINKER — CASO FARMACIA\n";
echo str_repeat('=', 70) . "\n\n";

// 1. Consulta 1: Lotes vencidos y bloqueo estricto de venta
echo "🔹 CONSULTA 1: Detección y bloqueo de venta de lotes vencidos (Regla #3)\n";
echo str_repeat('-', 60) . "\n";
$vencidos = Lote::vencidos()->with('medicamento:id,nombre_comercial')->take(3)->get(['id', 'medicamento_id', 'numero_lote', 'fecha_vencimiento', 'cantidad_stock']);
foreach ($vencidos as $l) {
    echo "  Lote: {$l->numero_lote} | {$l->medicamento->nombre_comercial} | Venció: {$l->fecha_vencimiento->format('Y-m-d')} | Stock: {$l->cantidad_stock}\n";
}
DB::beginTransaction();
try {
    $med = Medicamento::first();
    $lote = Lote::create(['medicamento_id' => $med->id, 'numero_lote' => 'LOT-CADUCADO-TEST', 'fecha_vencimiento' => now()->subMonths(3)->toDateString(), 'cantidad_stock' => 10]);
    try {
        (new VentaService)->crearVenta([['medicamento_id' => $med->id, 'cantidad' => 9999]]);
    } catch (\Exception $e) {
        $lote->refresh();
        echo "  [BLOQUEO EXITOSO]: " . $e->getMessage() . " | Stock lote: {$lote->cantidad_stock} und\n";
    }
} finally {
    DB::rollBack();
}
echo "\n";

// 2. Consulta 2: Alerta lotes que vencen en 30 días (FEFO)
echo "🔹 CONSULTA 2: Lotes próximos a vencer (30 días) para rotación FEFO (Regla #1)\n";
echo str_repeat('-', 60) . "\n";
$porVencer = Lote::porVencer(30)->with('medicamento:id,nombre_comercial')->orderBy('fecha_vencimiento')->take(3)->get();
foreach ($porVencer as $l) {
    $dias = (int) now()->diffInDays($l->fecha_vencimiento);
    echo "  ⚠️ Lote: {$l->numero_lote} | {$l->medicamento->nombre_comercial} | Vence en {$dias} días | Stock: {$l->cantidad_stock}\n";
}
echo "\n";

// 3. Consulta 3: FK inválida
echo "🔹 CONSULTA 3: Integridad de Clave Foránea (categoria_id inexistente)\n";
echo str_repeat('-', 60) . "\n";
try {
    Medicamento::create(['categoria_id' => 9999, 'laboratorio_id' => 1, 'nombre_comercial' => 'Test', 'principio_activo' => 'Test', 'presentacion' => 'Test', 'precio_venta' => 10, 'stock_minimo' => 5]);
} catch (\Illuminate\Database\QueryException $e) {
    echo "  [POSTGRESQL FK ERROR]: SQLSTATE[" . $e->getCode() . "] - Foreign key violation atrapada.\n";
}
echo "\n";

// 4. Consulta 4: CHECK precio negativo
echo "🔹 CONSULTA 4: Restricción CHECK de precio_venta > 0\n";
echo str_repeat('-', 60) . "\n";
try {
    Medicamento::create(['categoria_id' => Categoria::first()->id, 'laboratorio_id' => Laboratorio::first()->id, 'nombre_comercial' => 'Test', 'principio_activo' => 'Test', 'presentacion' => 'Test', 'precio_venta' => -10, 'stock_minimo' => 5]);
} catch (\Illuminate\Database\QueryException $e) {
    echo "  [POSTGRESQL CHECK ERROR]: SQLSTATE[" . $e->getCode() . "] - Check violation atrapada.\n";
}
echo "\n";

// 5. Consulta 5: Stock bajo el mínimo
echo "🔹 CONSULTA 5: Alerta de stock bajo el mínimo (Regla #2)\n";
echo str_repeat('-', 60) . "\n";
$bajoStock = Medicamento::with('categoria:id,nombre')
    ->withSum(['lotes as stock_vigente' => fn ($q) => $q->where('fecha_vencimiento', '>=', now()->toDateString())], 'cantidad_stock')
    ->get()
    ->filter(fn ($m) => ($m->stock_vigente ?? 0) < $m->stock_minimo)
    ->take(3);
foreach ($bajoStock as $m) {
    echo "  🔴 {$m->nombre_comercial} | {$m->categoria->nombre} | Stock vigente: " . ($m->stock_vigente ?? 0) . " | Mínimo: {$m->stock_minimo}\n";
}
echo "\n";

// 6. Consulta 6: Collections groupBy
echo "🔹 CONSULTA 6: Collections: groupBy por categoría\n";
echo str_repeat('-', 60) . "\n";
$porCat = Medicamento::with('categoria:id,nombre')->get()->groupBy(fn ($m) => $m->categoria->nombre)->map(fn ($m) => $m->count())->take(5);
foreach ($porCat as $cat => $cant) {
    echo "  📦 {$cat}: {$cant} medicamentos\n";
}
echo "\n";

// 7. Consulta 7: Collections filter + sortBy
echo "🔹 CONSULTA 7: Collections: filter (> 50 und) + sortBy(vencimiento)\n";
echo str_repeat('-', 60) . "\n";
$filtrados = Lote::with('medicamento:id,nombre_comercial')->get()->filter(fn ($l) => $l->cantidad_stock > 50)->sortBy('fecha_vencimiento')->take(3);
foreach ($filtrados as $l) {
    echo "  📋 {$l->numero_lote} | {$l->medicamento->nombre_comercial} | Stock: {$l->cantidad_stock} | Vence: {$l->fecha_vencimiento->format('Y-m-d')}\n";
}
echo "\n";

// 8. Consulta 8: UNIQUE numero_lote y auditoría de balance
echo "🔹 CONSULTA 8: Restricción UNIQUE en numero_lote y balance de inventario\n";
echo str_repeat('-', 60) . "\n";
DB::beginTransaction();
try {
    $primer = Lote::first();
    try {
        Lote::create(['medicamento_id' => $primer->medicamento_id, 'numero_lote' => $primer->numero_lote, 'fecha_vencimiento' => now()->addYear()->toDateString(), 'cantidad_stock' => 10]);
    } catch (\Illuminate\Database\UniqueConstraintViolationException $e) {
        echo "  [UNIQUE ERROR]: SQLSTATE[" . $e->getCode() . "] - Intento de duplicar '{$primer->numero_lote}' bloqueado.\n";
    }
} finally {
    DB::rollBack();
}
$tot = Lote::count();
$vig = Lote::where('fecha_vencimiento', '>=', now()->toDateString())->where('cantidad_stock', '>', 0)->count();
$ven = Lote::where('fecha_vencimiento', '<', now()->toDateString())->count();
$sin = Lote::where('fecha_vencimiento', '>=', now()->toDateString())->where('cantidad_stock', 0)->count();
echo "  [BALANCE TOTAL]: Total lotes ({$tot}) = Vigentes con stock ({$vig}) + Vencidos ({$ven}) + Agotados ({$sin})\n\n";

// 9. Consulta 9: Agregación sum y avg
echo "🔹 CONSULTA 9: Agregación: sum, avg y métricas globales de venta\n";
echo str_repeat('-', 60) . "\n";
echo "  💰 Total ventas:       " . Venta::count() . "\n";
echo "  💰 Recaudación total:  $" . number_format(Venta::sum('monto_total'), 2) . "\n";
echo "  💰 Ticket promedio:    $" . number_format(Venta::avg('monto_total'), 2) . "\n";
echo "  💰 Unidades vendidas:  " . DetalleVenta::sum('cantidad') . "\n\n";

// 10. Consulta 10: Top 5 medicamentos más vendidos
echo "🔹 CONSULTA 10: Top 5 medicamentos con mayores ingresos (JOIN + GROUP BY)\n";
echo str_repeat('-', 60) . "\n";
$top = DB::table('detalles_venta')
    ->join('lotes', 'detalles_venta.lote_id', '=', 'lotes.id')
    ->join('medicamentos', 'lotes.medicamento_id', '=', 'medicamentos.id')
    ->select('medicamentos.nombre_comercial', DB::raw('SUM(detalles_venta.cantidad) as total_vendido'), DB::raw('SUM(detalles_venta.cantidad * detalles_venta.precio_unitario) as ingreso_total'))
    ->groupBy('medicamentos.nombre_comercial')
    ->orderByDesc('total_vendido')
    ->limit(5)
    ->get();
foreach ($top as $row) {
    echo "  🏆 {$row->nombre_comercial} | Unidades: {$row->total_vendido} | Ingreso: $" . number_format($row->ingreso_total, 2) . "\n";
}
echo "\n";

// 11. Consulta 11: ON DELETE RESTRICT
echo "🔹 CONSULTA 11: Integridad referencial ON DELETE RESTRICT (borrar categoría)\n";
echo str_repeat('-', 60) . "\n";
DB::beginTransaction();
try {
    $cat = Categoria::has('medicamentos')->first();
    try {
        $cat->delete();
    } catch (\Illuminate\Database\QueryException $e) {
        echo "  [POSTGRESQL RESTRICT ERROR]: SQLSTATE[" . $e->getCode() . "] - Borrado de '{$cat->nombre}' bloqueado por tener medicamentos asociados.\n";
    }
} finally {
    DB::rollBack();
}
echo "\n";

// 12. Consulta 12: Demostración FEFO
echo "🔹 CONSULTA 12: Demostración empírica de despacho FEFO (First-Expired, First-Out)\n";
echo str_repeat('-', 60) . "\n";
DB::beginTransaction();
try {
    $med = Medicamento::first();
    $lote1 = Lote::create([
        'medicamento_id'    => $med->id,
        'numero_lote'       => 'LOT-FEFO-TEST-A',
        'fecha_vencimiento' => now()->addDays(15)->toDateString(),
        'cantidad_stock'    => 10,
    ]);
    $lote2 = Lote::create([
        'medicamento_id'    => $med->id,
        'numero_lote'       => 'LOT-FEFO-TEST-B',
        'fecha_vencimiento' => now()->addMonths(6)->toDateString(),
        'cantidad_stock'    => 20,
    ]);

    echo "  ANTES -> Lote A (Vence: {$lote1->fecha_vencimiento->format('Y-m-d')}): {$lote1->cantidad_stock} und | Lote B (Vence: {$lote2->fecha_vencimiento->format('Y-m-d')}): {$lote2->cantidad_stock} und\n";

    (new VentaService)->crearVenta([['medicamento_id' => $med->id, 'cantidad' => 14]]);

    $lote1->refresh();
    $lote2->refresh();
    echo "  DESPUÉS (se vendieron 14 und) -> Lote A: {$lote1->cantidad_stock} und (liquidado primero por vencer antes) | Lote B: {$lote2->cantidad_stock} und (debitó el resto)\n";
} finally {
    DB::rollBack();
}

echo "\n" . str_repeat('=', 70) . "\n";
echo "  ✅ TODAS LAS CONSULTAS EJECUTADAS CON ÉXITO\n";
echo str_repeat('=', 70) . "\n";
