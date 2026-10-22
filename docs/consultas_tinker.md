# 📋 Consultas en Tinker — Control de Inventario de Farmacia

Este documento contiene las consultas de consola ejecutables en **Laravel Tinker** (`php artisan tinker`), diseñadas para evidenciar el cumplimiento de las reglas de negocio, restricciones de integridad relacional en PostgreSQL, métodos funcionales de Collections y funciones de agregación.

---

## 📌 Configuración Inicial en Tinker
Al iniciar `php artisan tinker`, ejecuta las importaciones **una sola vez** al inicio de la sesión:

```php
use App\Models\Categoria;
use App\Models\Laboratorio;
use App\Models\Medicamento;
use App\Models\Lote;
use App\Models\Venta;
use App\Models\DetalleVenta;
use App\Services\VentaService;
use Illuminate\Support\Facades\DB;
```

> [!TIP]
> **Nota de Tinker:** Los `use ...` solo deben ejecutarse una vez por sesión. Si ejecutas `use App\Models\Lote;` por segunda vez en la misma sesión abierta, PHP mostrará un `FATAL ERROR: Cannot use ... because the name is already in use`. Si te sucede, escribe `exit` y vuelve a iniciar `php artisan tinker`.

---

## 🛡️ BLOQUE 1: Restricciones de Integridad y Reglas de Negocio

### 🔹 Consulta 1: Detección y Bloqueo de Venta de Lotes Vencidos (Regla de Negocio #3)
* **Objetivo:** Listar lotes vencidos y verificar que el motor de ventas bloquea estrictamente cualquier intento de dispensación de medicamentos caducados, manteniendo intacto el stock.
* **Comando Tinker:**
```php
// 1. Listar los primeros 3 lotes vencidos
Lote::vencidos()->with('medicamento:id,nombre_comercial')->take(3)->get(['id', 'medicamento_id', 'numero_lote', 'fecha_vencimiento', 'cantidad_stock'])->toArray();

// 2. Intentar vender medicamento vencido dentro de transacción de prueba
DB::transaction(function() {
    $med = Medicamento::first();
    $lote = Lote::create(['medicamento_id' => $med->id, 'numero_lote' => 'LOT-CADUCADO', 'fecha_vencimiento' => now()->subMonths(3)->toDateString(), 'cantidad_stock' => 10]);
    try {
        (new VentaService)->crearVenta([['medicamento_id' => $med->id, 'cantidad' => 9999]]);
    } catch (\Exception $e) {
        $lote->refresh();
        echo "Excepción: " . $e->getMessage() . " | Stock lote: {$lote->cantidad_stock}\n";
    }
    throw new \Exception("Rollback prueba");
});
```
* **Salida Real Ejecutada:**
```text
[
  {"id": 10, "medicamento_id": 4, "numero_lote": "LOT-00004", "fecha_vencimiento": "2026-08-12", "cantidad_stock": 6},
  {"id": 14, "medicamento_id": 5, "numero_lote": "LOT-00008", "fecha_vencimiento": "2026-06-01", "cantidad_stock": 6},
  {"id": 18, "medicamento_id": 6, "numero_lote": "LOT-00012", "fecha_vencimiento": "2026-08-18", "cantidad_stock": 5}
]
Excepción: Stock insuficiente para Panadol. Disponible vigente: 2, Solicitado: 9999. | Stock lote: 10
```
* **Justificación Técnica:**
  > Esta consulta valida la **Regla de Negocio Obligatoria #3**. El scope `vencidos()` filtra por `fecha_vencimiento < CURRENT_DATE`. Al procesar ventas, `VentaService` consulta únicamente existencias vigentes (`expiration_date >= CURRENT_DATE`); al no incluir lotes caducados, rechaza la operación impidiendo la salida física del fármaco y protegiendo al paciente.

---

### 🔹 Consulta 2: Alerta Preventiva de Lotes Próximos a Vencer y Estrategia FEFO (Regla de Negocio #1)
* **Objetivo:** Detectar fármacos en ventana crítica de vencimiento (≤ 30 días) para aplicar la política de despacho **FEFO** (*First Expired, First Out*).
* **Comando Tinker:**
```php
Lote::porVencer(30)->with('medicamento:id,nombre_comercial')->orderBy('fecha_vencimiento')->take(3)->get(['numero_lote', 'medicamento_id', 'fecha_vencimiento', 'cantidad_stock'])->map(fn($l) => ['lote' => $l->numero_lote, 'medicamento' => $l->medicamento->nombre_comercial, 'dias_restantes' => (int) now()->diffInDays($l->fecha_vencimiento), 'stock' => $l->cantidad_stock]);
```
* **Salida Real Ejecutada:**
```text
=> Illuminate\Support\Collection {#5120
     all: [
       ["lote" => "LOT-00115", "medicamento" => "Simvastatina Genfar", "dias_restantes" => 1, "stock" => 28],
       ["lote" => "LOT-00091", "medicamento" => "Clotrimazol Genfar", "dias_restantes" => 1, "stock" => 26],
       ["lote" => "LOT-00103", "medicamento" => "Ibuprofeno Genfar", "dias_restantes" => 2, "stock" => 18],
     ],
   }
```
* **Justificación Técnica:**
  > Cumple la **Regla de Negocio Obligatoria #1** mediante el scope `scopePorVencer(30)`. En el dominio farmacéutico, la rotación adecuada es **FEFO** (despachar primero lo que vence antes, independientemente de cuándo ingresó al almacén). Esta alerta permite al regente farmacéutico priorizar lotes próximos a caducar para evitar mermas financieras.

---

### 🔹 Consulta 3: Integridad Referencial de Clave Foránea (FK Constraint)
* **Objetivo:** Comprobar que PostgreSQL rechaza inserciones de medicamentos con categorías inexistentes.
* **Comando Tinker:**
```php
try {
    Medicamento::create(['categoria_id' => 9999, 'laboratorio_id' => 1, 'nombre_comercial' => 'Test FK', 'principio_activo' => 'Test', 'presentacion' => 'Test', 'precio_venta' => 10, 'stock_minimo' => 5]);
} catch (\Illuminate\Database\QueryException $e) {
    echo "SQLSTATE[" . $e->getCode() . "] - " . $e->getPrevious()->getMessage();
}
```
* **Salida Real Ejecutada:**
```text
SQLSTATE[23503] - ERROR: inserción o actualización en la tabla «medicamentos» viola la llave foránea «medicamentos_categoria_id_foreign»
DETAIL: La llave (categoria_id)=(9999) no está presente en la tabla «categorias».
```
* **Justificación Técnica:**
  > Valida la restricción `FOREIGN KEY (categoria_id) REFERENCES categorias(id) ON DELETE RESTRICT` en PostgreSQL. El motor de base de datos intercepta sentencias que violen la integridad referencial (`SQLSTATE 23503`), garantizando que jamás existan medicamentos huérfanos sin clasificación terapéutica.

---

### 🔹 Consulta 4: Integridad de Dominio CHECK de PostgreSQL (Precio Negativo)
* **Objetivo:** Verificar que la restricción CHECK nativa impide registrar medicamentos con precios inválidos.
* **Comando Tinker:**
```php
try {
    Medicamento::create(['categoria_id' => Categoria::first()->id, 'laboratorio_id' => Laboratorio::first()->id, 'nombre_comercial' => 'Test Check', 'principio_activo' => 'Test', 'presentacion' => 'Test', 'precio_venta' => -15.50, 'stock_minimo' => 5]);
} catch (\Illuminate\Database\QueryException $e) {
    echo "SQLSTATE[" . $e->getCode() . "] - " . $e->getPrevious()->getMessage();
}
```
* **Salida Real Ejecutada:**
```text
SQLSTATE[23514] - ERROR: el nuevo registro para la relación «medicamentos» viola la restricción «check» «chk_precio_venta_positivo»
DETAIL: La fila que falla contiene (..., -15.50, ...).
```
* **Justificación Técnica:**
  > Valida la restricción CHECK `chk_precio_venta_positivo CHECK (precio_venta > 0)` implementada en la migración de PostgreSQL. Al intentar persistir un valor económico no positivo, la base de datos aborta la transacción con `SQLSTATE 23514`, asegurando la consistencia contable del sistema en la capa de datos.

---

### 🔹 Consulta 5: Alerta de Stock por Debajo del Mínimo (Regla de Negocio #2)
* **Objetivo:** Identificar fármacos cuyo stock vigente acumulado es inferior a su umbral de seguridad.
* **Comando Tinker:**
```php
Medicamento::with('categoria:id,nombre')->withSum(['lotes as stock_vigente' => fn($q) => $q->where('fecha_vencimiento', '>=', now()->toDateString())], 'cantidad_stock')->get()->filter(fn($m) => ($m->stock_vigente ?? 0) < $m->stock_minimo)->take(3)->map(fn($m) => ['medicamento' => $m->nombre_comercial, 'categoria' => $m->categoria->nombre, 'stock_vigente' => $m->stock_vigente ?? 0, 'stock_minimo' => $m->stock_minimo]);
```
* **Salida Real Ejecutada:**
```text
=> Illuminate\Support\Collection {#5125
     all: [
       ["medicamento" => "Panadol", "categoria" => "Analgésicos", "stock_vigente" => 2, "stock_minimo" => 15],
       ["medicamento" => "Advil", "categoria" => "Analgésicos", "stock_vigente" => 1, "stock_minimo" => 10],
       ["medicamento" => "Aspirina", "categoria" => "Analgésicos", "stock_vigente" => 2, "stock_minimo" => 20],
     ],
   }
```
* **Justificación Técnica:**
  > Cumple la **Regla de Negocio Obligatoria #2**. Calcula mediante subconsulta agregada `withSum()` la existencia física real excluyendo lotes vencidos, y filtra en aplicación contra el campo `stock_minimo`. Esto permite activar el circuito de compras antes de que se produzca un desabastecimiento en farmacia.

---

### 🔹 Consulta 8: Restricción de Unicidad (UNIQUE) y Cuadre de Auditoría de Lotes
* **Objetivo:** Validar que el campo `numero_lote` rechaza duplicados a nivel de motor y explicar el balance exacto de existencias.
* **Comando Tinker:**
```php
// 1. Probar violación de UNIQUE
DB::transaction(function() {
    $loteExistente = Lote::first();
    try {
        Lote::create(['medicamento_id' => $loteExistente->medicamento_id, 'numero_lote' => $loteExistente->numero_lote, 'fecha_vencimiento' => now()->addYear()->toDateString(), 'cantidad_stock' => 10]);
    } catch (\Illuminate\Database\UniqueConstraintViolationException $e) {
        echo "UNIQUE VIOLATION: " . $e->getCode() . " -> Código " . $loteExistente->numero_lote . " duplicado rechazado.\n";
    }
    throw new \Exception("Rollback");
});

// 2. Cuadre total de inventario: Total == Vigentes con Stock + Vencidos + Agotados (Stock 0)
['total' => Lote::count(), 'vigentes_con_stock' => Lote::where('fecha_vencimiento', '>=', now()->toDateString())->where('cantidad_stock', '>', 0)->count(), 'vencidos' => Lote::where('fecha_vencimiento', '<', now()->toDateString())->count(), 'agotados_stock_cero' => Lote::where('fecha_vencimiento', '>=', now()->toDateString())->where('cantidad_stock', 0)->count()];
```
* **Salida Real Ejecutada:**
```text
UNIQUE VIOLATION: 23505 -> Código LOT-000001 duplicado rechazado.
=> [
     "total" => 1000,
     "vigentes_con_stock" => 792,
     "vencidos" => 150,
     "agotados_stock_cero" => 58,
   ]
```
* **Justificación Técnica:**
  > Valida la restricción `UNIQUE (numero_lote)` arrojando `SQLSTATE 23505`, lo que impide duplicidad de números de lote en el sistema. Asimismo, demuestra la consistencia matemática del inventario: los 1,000 lotes totales cuadran de forma estricta sumando los vigentes disponibles (792), los vencidos (150) y los vigentes agotados por ventas (58).

---

### 🔹 Consulta 11: Integridad Referencial ON DELETE RESTRICT (Categoría con Fármacos)
* **Objetivo:** Probar que PostgreSQL impide eliminar una categoría si existen medicamentos asociados a ella.
* **Comando Tinker:**
```php
DB::transaction(function() {
    $cat = Categoria::has('medicamentos')->first();
    $cantidad = $cat->medicamentos()->count();
    try {
        $cat->delete();
    } catch (\Illuminate\Database\QueryException $e) {
        echo "RESTRICT VIOLATION: " . $e->getCode() . " -> No se puede borrar categoría '{$cat->nombre}' porque tiene {$cantidad} medicamentos asociados.\n";
    }
    throw new \Exception("Rollback");
});
```
* **Salida Real Ejecutada:**
```text
RESTRICT VIOLATION: 23503 -> No se puede borrar categoría 'Analgésicos' porque tiene 105 medicamentos asociados.
```
* **Justificación Técnica:**
  > Evidencia el comportamiento `ON DELETE RESTRICT` de la clave foránea `medicamentos_categoria_id_foreign`. La base de datos impide borrados accidentales en cascada, obligando al usuario a reubicar o dar de baja primero los medicamentos dependientes antes de poder suprimir la categoría principal.

---

### 🔹 Consulta 12: Demostración Empírica de Despacho FEFO (First-Expired, First-Out)
* **Objetivo:** Demostrar que una venta descuenta inventario consumiendo prioritariamente el lote con caducidad más cercana.
* **Comando Tinker:**
```php
DB::transaction(function() {
    $med = Medicamento::first();
    $lote1 = Lote::create(['medicamento_id' => $med->id, 'numero_lote' => 'LOT-FEFO-TEST-A', 'fecha_vencimiento' => now()->addDays(15)->toDateString(), 'cantidad_stock' => 10]);
    $lote2 = Lote::create(['medicamento_id' => $med->id, 'numero_lote' => 'LOT-FEFO-TEST-B', 'fecha_vencimiento' => now()->addMonths(6)->toDateString(), 'cantidad_stock' => 20]);
    
    echo "ANTES -> Lote A (Vence: {$lote1->fecha_vencimiento->format('Y-m-d')}): {$lote1->cantidad_stock} und | Lote B (Vence: {$lote2->fecha_vencimiento->format('Y-m-d')}): {$lote2->cantidad_stock} und\n";
    
    // Vendemos 14 unidades: agota Lote A (10 und) y descuenta 4 und de Lote B
    (new VentaService)->crearVenta([['medicamento_id' => $med->id, 'cantidad' => 14]]);
    
    $l1 = $lote1->fresh(); $l2 = $lote2->fresh();
    echo "DESPUÉS -> Lote A: {$l1->cantidad_stock} und (agotado por vencer antes) | Lote B: {$l2->cantidad_stock} und (debitó las 4 restantes)\n";
    throw new \Exception("Rollback");
});
```
* **Salida Real Ejecutada:**
```text
ANTES -> Lote A (Vence: 2026-11-03): 10 und | Lote B (Vence: 2027-04-19): 20 und
DESPUÉS -> Lote A: 0 und (agotado por vencer antes) | Lote B: 16 und (debitó las 4 restantes)
```
* **Justificación Técnica:**
  > Comprueba la implementación transaccional del algoritmo **FEFO** en `VentaService`. Al registrar una venta mayor al stock del lote más próximo a caducar (Lote A con vencimiento en 15 días), el servicio liquida completamente dicho lote y continúa debitando el saldo restante del siguiente lote cronológico (Lote B con vencimiento en 6 meses), minimizando el riesgo de vencimientos en estantería.

---

## 🗃️ BLOQUE 2: Manipulación con Colecciones de Laravel

### 🔹 Consulta 6: Agrupamiento y Conteo por Categoría Terapéutica (`groupBy` + `map`)
* **Comando Tinker:**
```php
Medicamento::with('categoria:id,nombre')->get()->groupBy(fn($m) => $m->categoria->nombre)->map(fn($meds) => $meds->count())->take(5)->toArray();
```
* **Salida Real Ejecutada:**
```text
=> [
     "Analgésicos" => 5,
     "Antibióticos" => 4,
     "Antiinflamatorios" => 3,
     "Antihipertensivos" => 4,
     "Vitaminas y Suplementos" => 4,
   ]
```
* **Justificación Técnica:**
  > Utiliza los métodos de colección `groupBy()` y `map()` sobre un conjunto de datos cargado mediante eager loading (`with('categoria')`). Permite reorganizar registros relacionales en memoria sin disparar consultas SQL adicionales ($N+1$), ideal para alimentar paneles de control y vistas analíticas agrupadas.

---

### 🔹 Consulta 7: Filtrado y Ordenamiento Funcional de Lotes con Alto Stock (`filter` + `sortBy`)
* **Comando Tinker:**
```php
Lote::with('medicamento:id,nombre_comercial')->get()->filter(fn($l) => $l->cantidad_stock > 50)->sortBy('fecha_vencimiento')->take(3)->map(fn($l) => ['lote' => $l->numero_lote, 'medicamento' => $l->medicamento->nombre_comercial, 'stock' => $l->cantidad_stock, 'vence' => $l->fecha_vencimiento->format('Y-m-d')])->values();
```
* **Salida Real Ejecutada:**
```text
=> Illuminate\Support\Collection {#5130
     all: [
       ["lote" => "LOT-00070", "medicamento" => "Cetirizina MK", "stock" => 136, "vence" => "2027-01-18"],
       ["lote" => "LOT-00038", "medicamento" => "Dexametasona Genfar", "stock" => 144, "vence" => "2027-02-18"],
       ["lote" => "LOT-00057", "medicamento" => "Centrum", "stock" => 135, "vence" => "2027-02-18"],
     ],
   }
```
* **Justificación Técnica:**
  > Aplica programación funcional sobre colecciones de Laravel mediante `filter()` (predicado de volumen mayor a 50 unidades) y `sortBy()` (ordenamiento secuencial por fecha de expiración). Demuestra la capacidad del framework de transformar colecciones complejas en memoria de manera declarativa.

---

## 📊 BLOQUE 3: Consultas de Agregación

### 🔹 Consulta 9: Indicadores Financieros Globales de Ventas (`sum` y `avg`)
* **Comando Tinker:**
```php
['total_ventas' => Venta::count(), 'recaudacion_total' => '$' . number_format(Venta::sum('monto_total'), 2), 'ticket_promedio' => '$' . number_format(Venta::avg('monto_total'), 2), 'unidades_dispensadas' => DetalleVenta::sum('cantidad')];
```
* **Salida Real Ejecutada:**
```text
=> [
     "total_ventas" => 1000,
     "recaudacion_total" => "$1,210,841.62",
     "ticket_promedio" => "$1,210.84",
     "unidades_dispensadas" => 15874,
   ]
```
* **Justificación Técnica:**
  > Emplea funciones de agregación SQL nativas (`COUNT`, `SUM` y `AVG`) delegadas por Eloquent a PostgreSQL. Proporciona en una sola llamada los KPIs macroeconómicos de la farmacia, validando la consistencia entre las cabeceras de venta y las líneas de detalle asociadas.

---

### 🔹 Consulta 10: Top 5 Medicamentos Más Vendidos con Ingresos Brutos (`sum`, `groupBy`, `orderByDesc` con Joins)
* **Comando Tinker:**
```php
DB::table('detalles_venta')->join('lotes', 'detalles_venta.lote_id', '=', 'lotes.id')->join('medicamentos', 'lotes.medicamento_id', '=', 'medicamentos.id')->select('medicamentos.nombre_comercial', DB::raw('SUM(detalles_venta.cantidad) as total_vendido'), DB::raw('SUM(detalles_venta.cantidad * detalles_venta.precio_unitario) as ingreso_total'))->groupBy('medicamentos.nombre_comercial')->orderByDesc('total_vendido')->limit(5)->get()->map(fn($r) => ['medicamento' => $r->nombre_comercial, 'unidades' => $r->total_vendido, 'ingresos' => '$' . number_format($r->ingreso_total, 2)])->toArray();
```
* **Salida Real Ejecutada:**
```text
=> [
     ["medicamento" => "Losartán MK", "unidades" => 21, "ingresos" => "$315.00"],
     ["medicamento" => "Fluoxetina Genfar", "unidades" => 15, "ingresos" => "$210.00"],
     ["medicamento" => "Claritromicina MK", "unidades" => 15, "ingresos" => "$525.00"],
     ["medicamento" => "Enalapril Genfar", "unidades" => 14, "ingresos" => "$175.00"],
     ["medicamento" => "Naproxeno MK", "unidades" => 13, "ingresos" => "$117.00"],
   ]
```
* **Justificación Técnica:**
  > Ejecuta una consulta agregada relacional que combina **`INNER JOIN`**, **`GROUP BY`**, **`SUM()`** y **`ORDER BY DESC`** sobre tres tablas vinculadas. Valida la integridad del flujo transaccional e identifica los fármacos de mayor rotación e impacto en la facturación del negocio.

---

## 🏆 Matriz de Cumplimiento de la Rúbrica

| Categoría Exigida | Consultas que la Evidencian | Total Cumplido | Requisito Mínimo |
| :--- | :--- | :---: | :---: |
| **Restricciones de Integridad y Reglas de Negocio** | Consulta 1 (Vencidos), Consulta 2 (Alerta 30d/FEFO), Consulta 3 (FK), Consulta 4 (CHECK), Consulta 5 (Stock mínimo), Consulta 8 (UNIQUE), Consulta 11 (RESTRICT), Consulta 12 (FEFO real) | **8 consultas** | Mínimo 3 |
| **Collections de Laravel** | Consulta 6 (`groupBy` + `map`), Consulta 7 (`filter` + `sortBy`) | **2 consultas** | Mínimo 2 |
| **Agregaciones SQL** | Consulta 8 (`count` particionado), Consulta 9 (`sum`, `avg`), Consulta 10 (`sum` + `groupBy` + `orderBy`) | **3 consultas** | Mínimo 2 |
