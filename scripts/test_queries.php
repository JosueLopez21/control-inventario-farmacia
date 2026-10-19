<?php

require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Categoria;
use App\Models\DetalleVenta;
use App\Models\Laboratorio;
use App\Models\Lote;
use App\Models\Medicamento;
use App\Models\Venta;
use Illuminate\Support\Facades\DB;

echo "\n" . str_repeat('=', 65) . "\n";
echo "  VERIFICACIÓN DE CONSULTAS EN ESPAÑOL - FARMACIA INVENTARIO\n";
echo str_repeat('=', 65) . "\n\n";

// Consulta 1: Lotes vencidos
echo "📌 CONSULTA 1: Lotes vencidos (Regla #3)\n";
echo str_repeat('-', 50) . "\n";
$vencidos = Lote::vencidos()->with('medicamento:id,nombre_comercial')->get(['id', 'medicamento_id', 'numero_lote', 'fecha_vencimiento', 'cantidad_stock']);
foreach ($vencidos->take(5) as $l) {
    echo "  Lote {$l->numero_lote} | {$l->medicamento->nombre_comercial} | Venció: {$l->fecha_vencimiento->format('Y-m-d')} | Stock: {$l->cantidad_stock}\n";
}
echo "  ... Total vencidos: {$vencidos->count()}\n\n";

// Consulta 2: Lotes próximos a vencer
echo "📌 CONSULTA 2: Lotes próximos a vencer - 30 días (Regla #1)\n";
echo str_repeat('-', 50) . "\n";
$porVencer = Lote::porVencer(30)->with('medicamento:id,nombre_comercial')->orderBy('fecha_vencimiento')->get(['id', 'medicamento_id', 'numero_lote', 'fecha_vencimiento', 'cantidad_stock']);
foreach ($porVencer->take(5) as $l) {
    $dias = now()->diffInDays($l->fecha_vencimiento);
    echo "  ⚠️ {$l->numero_lote} | {$l->medicamento->nombre_comercial} | Vence en {$dias} días | Stock: {$l->cantidad_stock}\n";
}
echo "  ... Total por vencer: {$porVencer->count()}\n\n";

// Consulta 3: FK inválida
echo "📌 CONSULTA 3: Integridad FK — Categoría inexistente\n";
echo str_repeat('-', 50) . "\n";
try {
    Medicamento::create([
        'categoria_id' => 9999,
        'laboratorio_id' => 1,
        'nombre_comercial' => 'Prueba FK',
        'principio_activo' => 'Test',
        'presentacion' => 'Test',
        'precio_venta' => 10.00,
        'stock_minimo' => 5,
    ]);
    echo "  ❌ ERROR: No debería llegar aquí\n";
} catch (\Exception $e) {
    echo "  ✅ FK validada por PostgreSQL: " . class_basename($e) . "\n";
}
echo "\n";

// Consulta 4: CHECK constraint
echo "📌 CONSULTA 4: CHECK constraint — Precio negativo\n";
echo str_repeat('-', 50) . "\n";
try {
    Medicamento::create([
        'categoria_id' => 1,
        'laboratorio_id' => 1,
        'nombre_comercial' => 'Precio Negativo',
        'principio_activo' => 'Test',
        'presentacion' => 'Test',
        'precio_venta' => -10.00,
        'stock_minimo' => 5,
    ]);
    echo "  ❌ ERROR: No debería llegar aquí\n";
} catch (\Exception $e) {
    echo "  ✅ CHECK validada por PostgreSQL: " . class_basename($e) . "\n";
}
echo "\n";

// Consulta 5: Stock bajo mínimo
echo "📌 CONSULTA 5: Medicamentos bajo stock mínimo (Regla #2)\n";
echo str_repeat('-', 50) . "\n";
$bajoStock = Medicamento::with('categoria:id,nombre')
    ->withSum(['lotes as stock_vigente' => fn ($q) => $q->where('fecha_vencimiento', '>=', now()->toDateString())], 'cantidad_stock')
    ->get()
    ->filter(fn ($m) => ($m->stock_vigente ?? 0) < $m->stock_minimo);

foreach ($bajoStock->take(5) as $m) {
    echo "  🔴 {$m->nombre_comercial} | {$m->categoria->nombre} | Stock: " . ($m->stock_vigente ?? 0) . " | Mín: {$m->stock_minimo}\n";
}
echo "  ... Total bajo mínimo: {$bajoStock->count()}\n\n";

// Consulta 6: groupBy + map (Collections)
echo "📌 CONSULTA 6: Medicamentos por categoría (Collections: groupBy + map)\n";
echo str_repeat('-', 50) . "\n";
Medicamento::with('categoria:id,nombre')->get()
    ->groupBy(fn ($m) => $m->categoria->nombre)
    ->map(fn ($meds, $cat) => ['categoria' => $cat, 'cantidad' => $meds->count()])
    ->each(function ($info) {
        echo "  📦 {$info['categoria']}: {$info['cantidad']} medicamentos\n";
    });
echo "\n";

// Consulta 7: filter + sortBy (Collections)
echo "📌 CONSULTA 7: Lotes con stock > 50, ordenados (Collections: filter + sortBy)\n";
echo str_repeat('-', 50) . "\n";
$filtrados = Lote::with('medicamento:id,nombre_comercial')->get()
    ->filter(fn ($l) => $l->cantidad_stock > 50)
    ->sortBy('fecha_vencimiento')
    ->values();
foreach ($filtrados->take(5) as $l) {
    echo "  📋 {$l->numero_lote} | {$l->medicamento->nombre_comercial} | Stock: {$l->cantidad_stock} | Vence: {$l->fecha_vencimiento->format('Y-m-d')}\n";
}
echo "  ... Total filtrados: {$filtrados->count()}\n\n";

// Consulta 8: count
echo "📌 CONSULTA 8: Resumen cuantitativo de lotes (Agregación: count)\n";
echo str_repeat('-', 50) . "\n";
echo "  📊 Total:              " . Lote::count() . "\n";
echo "  📊 Vigentes con stock: " . Lote::where('fecha_vencimiento', '>=', now()->toDateString())->where('cantidad_stock', '>', 0)->count() . "\n";
echo "  📊 Próximos a vencer:  " . Lote::porVencer()->count() . "\n";
echo "  📊 Vencidos:           " . Lote::vencidos()->count() . "\n";
echo "  📊 Sin stock:          " . Lote::where('cantidad_stock', 0)->count() . "\n\n";

// Consulta 9: sum + avg
echo "📌 CONSULTA 9: Estadísticas de ventas (Agregación: sum, avg)\n";
echo str_repeat('-', 50) . "\n";
echo "  💰 Total ventas:       " . Venta::count() . "\n";
echo "  💰 Monto total:        $" . number_format(Venta::sum('monto_total'), 2) . "\n";
echo "  💰 Promedio/venta:     $" . number_format(Venta::avg('monto_total'), 2) . "\n";
echo "  💰 Unidades vendidas:  " . DetalleVenta::sum('cantidad') . "\n\n";

// Consulta 10: Top 5 medicamentos más vendidos
echo "📌 CONSULTA 10: Top 5 medicamentos más vendidos (Agregación + Join)\n";
echo str_repeat('-', 50) . "\n";
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

echo "\n" . str_repeat('=', 65) . "\n";
echo "  ✅ TODAS LAS CONSULTAS EJECUTADAS EXITOSAMENTE EN ESPAÑOL\n";
echo str_repeat('=', 65) . "\n";
