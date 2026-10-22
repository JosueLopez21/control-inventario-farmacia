# 📸 Guía para la Toma de Capturas de Pantalla (Evidencias para el Informe)

Esta guía detalla el orden exacto de comandos que debes ejecutar en tu terminal para obtener todas las capturas de pantalla requeridas para el documento de entrega (Word/PDF).

> 💡 **Consejo:** Para capturar un área específica de tu pantalla en Windows presiona **`Windows + Shift + S`**, recorta la terminal donde se vea el comando y el resultado, y pégala directamente en tu Word (`Ctrl + V`).

---

## 📌 PARTE 1: Base de Datos y Pruebas Unitarias

### 📸 Captura 1: Migración y Sembrado de Datos (Seeders)
* **Comando a ejecutar en la terminal:**
  ```bash
  php artisan migrate:fresh --seed
  ```
* **Qué debe verse en la captura:**
  1. La lista de migraciones ejecutándose en azul/verde (`create_categorias_table`, `create_laboratorios_table`, `create_medicamentos_table`, `create_lotes_table`, `create_ventas_table`, `create_detalles_venta_table`).
  2. El bloque final verde con los emojis de conteo:
     * `📦 Categorías: 10`
     * `🏭 Laboratorios: 12`
     * `💊 Medicamentos: 30`
     * `📋 Lotes: 120`
     * `🛒 Ventas: 25`
     * `📝 Detalles: 98`
     * `⚠️ Lotes vencidos: 30`
     * `⏰ Lotes por vencer: 28`

---

### 📸 Captura 2: Suite de Pruebas Automatizadas (PHPUnit Tests)
* **Comando a ejecutar en la terminal:**
  ```bash
  php artisan test
  ```
* **Qué debe verse en la captura:**
  * La barra verde de éxito: `PASS Tests\Feature\ReglasNegocioFarmaciaTest`.
  * Los 3 tests aprobados con check verde:
    * `✓ scope por vencer identifica lotes dentro de 30 dias`
    * `✓ alerta medicamentos por debajo del stock minimo`
    * `✓ venta fefo descuenta del lote mas proximo y bloquea vencidos`
  * El resumen final: `Tests: 24 passed`.

---

## 📌 PARTE 2: Consultas en Laravel Tinker

Inicia la sesión de Tinker en tu terminal:
```bash
php artisan tinker
```
*(Se recomienda importar los modelos una sola vez al entrar para que los comandos sean cortos)*:
```php
use App\Models\Categoria; use App\Models\Laboratorio; use App\Models\Medicamento; use App\Models\Lote; use App\Models\Venta; use App\Models\DetalleVenta; use App\Services\VentaService; use Illuminate\Support\Facades\DB;
```

---

### 📸 Captura 3: Consulta 1 — Detección y Bloqueo de Lotes Vencidos (Regla #3)
* **Comando a ejecutar:**
  ```php
  Lote::vencidos()->with('medicamento:id,nombre_comercial')->take(3)->get(['id', 'medicamento_id', 'numero_lote', 'fecha_vencimiento', 'cantidad_stock'])->toArray();
  ```
* **Qué debe verse:** El arreglo con 3 lotes cuya `fecha_vencimiento` sea del pasado (ej. `2026-08-12`, `2026-06-01`).

---

### 📸 Captura 4: Consulta 2 — Alerta de Lotes por Vencer en 30 Días (Regla #1 / FEFO)
* **Comando a ejecutar:**
  ```php
  Lote::porVencer(30)->with('medicamento:id,nombre_comercial')->orderBy('fecha_vencimiento')->take(3)->get(['numero_lote', 'medicamento_id', 'fecha_vencimiento', 'cantidad_stock'])->map(fn($l) => ['lote' => $l->numero_lote, 'medicamento' => $l->medicamento->nombre_comercial, 'dias_restantes' => (int) now()->diffInDays($l->fecha_vencimiento), 'stock' => $l->cantidad_stock]);
  ```
* **Qué debe verse:** La colección con 3 lotes donde `dias_restantes` sea entre 0 y 30 días, listando el nombre comercial del medicamento y su stock.

---

### 📸 Captura 5: Consulta 3 — Restricción de Clave Foránea (FK Constraint)
* **Comando a ejecutar:**
  ```php
  Medicamento::create(['categoria_id' => 9999, 'laboratorio_id' => 1, 'nombre_comercial' => 'Test', 'principio_activo' => 'Test', 'presentacion' => 'Test', 'precio_venta' => 10, 'stock_minimo' => 5]);
  ```
* **Qué debe verse:** La pantalla roja de error de Laravel con `QueryException`:
  `SQLSTATE[23503]: Foreign key violation: 7 ERROR: inserción o actualización en la tabla «medicamentos» viola la llave foránea «medicamentos_categoria_id_foreign»`.

---

### 📸 Captura 6: Consulta 4 — Restricción de Integridad CHECK (Precio Negativo)
* **Comando a ejecutar:**
  ```php
  Medicamento::create(['categoria_id' => Categoria::first()->id, 'laboratorio_id' => Laboratorio::first()->id, 'nombre_comercial' => 'Test', 'principio_activo' => 'Test', 'presentacion' => 'Test', 'precio_venta' => -10, 'stock_minimo' => 5]);
  ```
* **Qué debe verse:** Pantalla roja de error de Laravel con `QueryException`:
  `SQLSTATE[23514]: Check violation: 7 ERROR: el nuevo registro para la relación «medicamentos» viola la restricción «check» «chk_precio_venta_positivo»`.

---

### 📸 Captura 7: Consulta 5 — Medicamentos Bajo Stock Mínimo (Regla #2)
* **Comando a ejecutar:**
  ```php
  Medicamento::with('categoria:id,nombre')->withSum(['lotes as stock_vigente' => fn($q) => $q->where('fecha_vencimiento', '>=', now()->toDateString())], 'cantidad_stock')->get()->filter(fn($m) => ($m->stock_vigente ?? 0) < $m->stock_minimo)->take(3)->map(fn($m) => ['medicamento' => $m->nombre_comercial, 'categoria' => $m->categoria->nombre, 'stock_vigente' => $m->stock_vigente ?? 0, 'stock_minimo' => $m->stock_minimo]);
  ```
* **Qué debe verse:** Colección con fármacos en déficit (ej. `Panadol` con stock vigente 2 frente a un mínimo de 15).

---

### 📸 Captura 8: Consulta 6 — Agrupación con Collections (`groupBy` + `map`)
* **Comando a ejecutar:**
  ```php
  Medicamento::with('categoria:id,nombre')->get()->groupBy(fn($m) => $m->categoria->nombre)->map(fn($meds) => $meds->count())->take(5)->toArray();
  ```
* **Qué debe verse:** Arreglo asociativo con el conteo de fármacos por familia (ej. `Analgésicos => 5, Antibióticos => 4...`).

---

### 📸 Captura 9: Consulta 7 — Filtrado y Ordenamiento con Collections (`filter` + `sortBy`)
* **Comando a ejecutar:**
  ```php
  Lote::with('medicamento:id,nombre_comercial')->get()->filter(fn($l) => $l->cantidad_stock > 50)->sortBy('fecha_vencimiento')->take(3)->map(fn($l) => ['lote' => $l->numero_lote, 'medicamento' => $l->medicamento->nombre_comercial, 'stock' => $l->cantidad_stock, 'vence' => $l->fecha_vencimiento->format('Y-m-d')])->values();
  ```
* **Qué debe verse:** Colección ordenada cronológicamente de los 3 primeros lotes que tienen más de 50 unidades.

---

### 📸 Captura 10: Consulta 8 — Restricción UNIQUE en Número de Lote y Balance
* **Comando a ejecutar:**
  ```php
  // 1. UNIQUE
  Lote::create(['medicamento_id' => 1, 'numero_lote' => Lote::first()->numero_lote, 'fecha_vencimiento' => now()->addYear()->toDateString(), 'cantidad_stock' => 10]);
  ```
* **Qué debe verse:** Pantalla roja de error con `UniqueConstraintViolationException`:
  `SQLSTATE[23505]: Unique violation: 7 ERROR: llave duplicada viola restricción de unicidad «lotes_numero_lote_unique»`.
* **Comando de Balance (opcional para la misma captura):**
  ```php
  ['total' => Lote::count(), 'vigentes_con_stock' => Lote::where('fecha_vencimiento', '>=', now()->toDateString())->where('cantidad_stock', '>', 0)->count(), 'vencidos' => Lote::where('fecha_vencimiento', '<', now()->toDateString())->count(), 'agotados' => Lote::where('fecha_vencimiento', '>=', now()->toDateString())->where('cantidad_stock', 0)->count()];
  ```
* **Qué debe verse:** Arreglo con `total => 120, vigentes_con_stock => 83, vencidos => 30, agotados => 7`.

---

### 📸 Captura 11: Consulta 9 — Estadísticas Agregadas de Ventas (`sum` y `avg`)
* **Comando a ejecutar:**
  ```php
  ['total_ventas' => Venta::count(), 'recaudacion_total' => '$' . number_format(Venta::sum('monto_total'), 2), 'ticket_promedio' => '$' . number_format(Venta::avg('monto_total'), 2), 'unidades_dispensadas' => DetalleVenta::sum('cantidad')];
  ```
* **Qué debe verse:** Arreglo con métricas monetarias (ej. `total_ventas => 25, recaudacion_total => $3,291.50...`).

---

### 📸 Captura 12: Consulta 10 — Top 5 Medicamentos Más Vendidos (JOIN + GROUP BY)
* **Comando a ejecutar:**
  ```php
  DB::table('detalles_venta')->join('lotes', 'detalles_venta.lote_id', '=', 'lotes.id')->join('medicamentos', 'lotes.medicamento_id', '=', 'medicamentos.id')->select('medicamentos.nombre_comercial', DB::raw('SUM(detalles_venta.cantidad) as total_vendido'), DB::raw('SUM(detalles_venta.cantidad * detalles_venta.precio_unitario) as ingreso_total'))->groupBy('medicamentos.nombre_comercial')->orderByDesc('total_vendido')->limit(5)->get()->map(fn($r) => ['medicamento' => $r->nombre_comercial, 'unidades' => $r->total_vendido, 'ingresos' => '$' . number_format($r->ingreso_total, 2)])->toArray();
  ```
* **Qué debe verse:** Arreglo con el ranking de los 5 fármacos con más unidades vendidas y sus ingresos generados.

---

### 📸 Captura 13: Consulta 11 — Restricción ON DELETE RESTRICT (Borrar Categoría)
* **Comando a ejecutar:**
  ```php
  Categoria::has('medicamentos')->first()->delete();
  ```
* **Qué debe verse:** Pantalla roja de error con `QueryException`:
  `SQLSTATE[23001]: Restrict violation: 7 ERROR: update or delete on table "categorias" violates RESTRICT setting of foreign key constraint "medicamentos_categoria_id_foreign"`.

---

### 📸 Captura 14: Consulta 12 — Demostración Empírica de Despacho FEFO
* **Comando a ejecutar:**
  ```php
  DB::transaction(function() { $med = Medicamento::where('nombre_comercial', 'Ciprofloxacino Genfar')->first(); $lotes = $med->lotes()->where('fecha_vencimiento', '>=', now()->toDateString())->orderBy('fecha_vencimiento', 'asc')->take(2)->get(); echo "ANTES -> Lote 1 (" . $lotes[0]->fecha_vencimiento->format('Y-m-d') . "): " . $lotes[0]->cantidad_stock . " | Lote 2 (" . $lotes[1]->fecha_vencimiento->format('Y-m-d') . "): " . $lotes[1]->cantidad_stock . "\n"; (new VentaService)->crearVenta([['medicamento_id' => $med->id, 'cantidad' => $lotes[0]->cantidad_stock + 2]]); $l1 = $lotes[0]->fresh(); $l2 = $lotes[1]->fresh(); echo "DESPUES -> Lote 1: " . $l1->cantidad_stock . " (agotado) | Lote 2: " . $l2->cantidad_stock . " (consumio 2)\n"; throw new \Exception('Rollback'); });
  ```
* **Qué debe verse:** El mensaje de consola mostrando que el Lote 1 se liquidó a 0 unidades y el Lote 2 pasó a tener 2 unidades menos.
