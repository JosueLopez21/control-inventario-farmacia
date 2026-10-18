# 📋 Consultas de Tinker - Control de Inventario de Farmacia (Español)

## Instrucciones de uso
Ejecutar desde la terminal en la raíz del proyecto:
```bash
php artisan tinker
```
Luego copiar y pegar cada consulta dentro de la sesión de Tinker.

---

## Consulta 1: Buscar lotes vencidos (Regla de Negocio #3)
**Tipo:** Restricción de integridad / Regla de negocio
**Objetivo:** Identificar todos los lotes cuya fecha de vencimiento ya pasó. Estos lotes NO deben venderse bajo ninguna circunstancia.

```php
use App\Models\Lote;
Lote::vencidos()->with('medicamento:id,nombre_comercial')->get(['id', 'medicamento_id', 'numero_lote', 'fecha_vencimiento', 'cantidad_stock'])->each(function ($l) { echo "Lote {$l->numero_lote} | {$l->medicamento->nombre_comercial} | Venció: {$l->fecha_vencimiento->format('Y-m-d')} | Stock: {$l->cantidad_stock}\n"; });
```

---

## Consulta 2: Alertar lotes que vencen en los próximos 30 días (Regla de Negocio #1)
**Tipo:** Regla de negocio
**Objetivo:** Listar lotes que están próximos a vencer dentro del rango crítico de 30 días para tomar medidas preventivas.

```php
use App\Models\Lote;
Lote::porVencer(30)->with('medicamento:id,nombre_comercial')->orderBy('fecha_vencimiento')->get(['id', 'medicamento_id', 'numero_lote', 'fecha_vencimiento', 'cantidad_stock'])->each(function ($l) { $dias = now()->diffInDays($l->fecha_vencimiento); echo "⚠️ Lote {$l->numero_lote} | {$l->medicamento->nombre_comercial} | Vence en {$dias} días ({$l->fecha_vencimiento->format('Y-m-d')}) | Stock: {$l->cantidad_stock}\n"; });
```

---

## Consulta 3: Validar integridad referencial — Intentar crear medicamento con categoría inexistente
**Tipo:** Restricción de integridad (FK de PostgreSQL)
**Objetivo:** Demostrar que PostgreSQL rechaza la inserción si el `categoria_id` no existe en la tabla `categorias`.

```php
use App\Models\Medicamento;
try { Medicamento::create(['categoria_id' => 9999, 'laboratorio_id' => 1, 'nombre_comercial' => 'Prueba', 'principio_activo' => 'Prueba', 'presentacion' => 'Prueba', 'precio_venta' => 10.00, 'stock_minimo' => 5]); echo "ERROR: No debería llegar aquí\n"; } catch (\Exception $e) { echo "✅ FK validada por PostgreSQL: " . class_basename($e) . "\n" . $e->getMessage() . "\n"; }
```

---

## Consulta 4: Verificar restricción CHECK — Intentar precio de venta negativo
**Tipo:** Restricción CHECK de PostgreSQL
**Objetivo:** Demostrar que la restricción CHECK `chk_precio_venta_positivo` impide precios menores o iguales a cero.

```php
use App\Models\Medicamento;
try { Medicamento::create(['categoria_id' => 1, 'laboratorio_id' => 1, 'nombre_comercial' => 'Precio Negativo', 'principio_activo' => 'Test', 'presentacion' => 'Test', 'precio_venta' => -15.00, 'stock_minimo' => 5]); echo "ERROR: No debería llegar aquí\n"; } catch (\Exception $e) { echo "✅ Restricción CHECK validada por PostgreSQL: " . class_basename($e) . "\n" . $e->getMessage() . "\n"; }
```

---

## Consulta 5: Medicamentos con stock por debajo del mínimo (Regla de Negocio #2)
**Tipo:** Regla de negocio
**Objetivo:** Identificar medicamentos cuyo stock total vigente está por debajo de su `stock_minimo` para emitir orden de reposición.

```php
use App\Models\Medicamento;
Medicamento::with('categoria:id,nombre')->withSum(['lotes as stock_vigente' => fn($q) => $q->where('fecha_vencimiento', '>=', now()->toDateString())], 'cantidad_stock')->get()->filter(fn($m) => ($m->stock_vigente ?? 0) < $m->stock_minimo)->each(function ($m) { echo "🔴 {$m->nombre_comercial} | Categoría: {$m->categoria->nombre} | Stock vigente: " . ($m->stock_vigente ?? 0) . " | Mínimo: {$m->stock_minimo}\n"; });
```

---

## Consulta 6: Agrupar medicamentos por categoría usando Collections (groupBy + map)
**Tipo:** Métodos de Colecciones Laravel (`groupBy`, `map`)
**Objetivo:** Agrupar medicamentos por su categoría y mostrar la cantidad y los nombres de cada grupo.

```php
use App\Models\Medicamento;
Medicamento::with('categoria:id,nombre')->get()->groupBy(fn($m) => $m->categoria->nombre)->map(fn($meds, $cat) => ['categoria' => $cat, 'cantidad' => $meds->count(), 'medicamentos' => $meds->pluck('nombre_comercial')->implode(', ')])->each(function ($info) { echo "📦 {$info['categoria']} ({$info['cantidad']}): {$info['medicamentos']}\n"; });
```

---

## Consulta 7: Filtrar lotes usando Collections (filter + sortBy)
**Tipo:** Métodos de Colecciones Laravel (`filter`, `sortBy`)
**Objetivo:** Filtrar lotes con cantidad superior a 50 unidades y ordenarlos por fecha de vencimiento.

```php
use App\Models\Lote;
Lote::with('medicamento:id,nombre_comercial')->get()->filter(fn($l) => $l->cantidad_stock > 50)->sortBy('fecha_vencimiento')->values()->each(function ($l) { echo "📋 Lote {$l->numero_lote} | {$l->medicamento->nombre_comercial} | Stock: {$l->cantidad_stock} | Vence: {$l->fecha_vencimiento->format('Y-m-d')}\n"; });
```

---

## Consulta 8: Contar lotes por estado — Agregación con count
**Tipo:** Agregación (`count`)
**Objetivo:** Mostrar un resumen cuantitativo del inventario (total, vigentes, por vencer, vencidos, sin stock).

```php
use App\Models\Lote;
$total = Lote::count();
$vigentes = Lote::where('fecha_vencimiento', '>=', now()->toDateString())->where('cantidad_stock', '>', 0)->count();
$porVencer = Lote::porVencer()->count();
$vencidos = Lote::vencidos()->count();
$sinStock = Lote::where('cantidad_stock', 0)->count();
echo "📊 Resumen de Lotes:\n";
echo "   Total:              {$total}\n";
echo "   Vigentes con stock: {$vigentes}\n";
echo "   Próximos a vencer:  {$porVencer}\n";
echo "   Vencidos:           {$vencidos}\n";
echo "   Sin stock:          {$sinStock}\n";
```

---

## Consulta 9: Estadísticas de ventas — Agregación con sum y avg
**Tipo:** Agregación (`sum`, `avg`)
**Objetivo:** Calcular el monto global recaudado, el ticket promedio por venta y el total de unidades dispensadas.

```php
use App\Models\Venta;
use App\Models\DetalleVenta;
$totalVentas = Venta::count();
$montoTotal = Venta::sum('monto_total');
$promedioVenta = Venta::avg('monto_total');
$unidadesVendidas = DetalleVenta::sum('cantidad');
echo "💰 Estadísticas Financieras de Ventas:\n";
echo "   Total de ventas:     {$totalVentas}\n";
echo "   Monto total:         $" . number_format($montoTotal, 2) . "\n";
echo "   Promedio por venta:  $" . number_format($promedioVenta, 2) . "\n";
echo "   Unidades vendidas:   {$unidadesVendidas}\n";
```

---

## Consulta 10: Top 5 medicamentos más vendidos — Agregación con agrupación y joins
**Tipo:** Agregación + Join (`sum`, `groupBy`, `orderByDesc`)
**Objetivo:** Identificar los 5 medicamentos con mayores ventas acumuladas en unidades e ingresos.

```php
use Illuminate\Support\Facades\DB;
DB::table('detalles_venta')->join('lotes', 'detalles_venta.lote_id', '=', 'lotes.id')->join('medicamentos', 'lotes.medicamento_id', '=', 'medicamentos.id')->select('medicamentos.nombre_comercial', DB::raw('SUM(detalles_venta.cantidad) as total_vendido'), DB::raw('SUM(detalles_venta.cantidad * detalles_venta.precio_unitario) as ingreso_total'))->groupBy('medicamentos.nombre_comercial')->orderByDesc('total_vendido')->limit(5)->get()->each(function ($row) { echo "🏆 {$row->nombre_comercial} | Unidades: {$row->total_vendido} | Total: $" . number_format($row->ingreso_total, 2) . "\n"; });
```

---

## Tabla de Mapeo de Requisitos

| # | Categoría | Consulta | Concepto Clave / Regla |
|---|---|---|---|
| **1** | Regla de negocio | Lotes vencidos | Bloqueo de venta (#3) |
| **2** | Regla de negocio | Lotes por vencer | Alerta 30 días (#1) |
| **3** | Integridad FK | FK inválida | Foreign Key Restrict en PostgreSQL |
| **4** | Integridad CHECK | Precio negativo | CHECK Constraint PostgreSQL |
| **5** | Regla de negocio | Stock bajo mínimo | Alerta de stock mínimo (#2) |
| **6** | Colecciones | `groupBy` + `map` | Agrupación de medicamentos por categoría |
| **7** | Colecciones | `filter` + `sortBy` | Filtrado y orden de colecciones |
| **8** | Agregación | `count` | Métricas de lotes por estado |
| **9** | Agregación | `sum` + `avg` | Métricas financieras de ventas |
| **10** | Agregación + Join | `sum` + `groupBy` | Ranking Top 5 más vendidos |
