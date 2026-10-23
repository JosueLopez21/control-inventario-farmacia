# Sistema de Control de Inventario de una Farmacia

Sistema web con autenticación por roles, dashboard, gestión de inventario, ventas con despacho **FEFO**, alertas y reportes. Incluye una portada simple con foto, un juego educativo de ruleta, un asesor de síntomas, catálogo en tarjetas, un punto de venta táctil con buscador inteligente, animaciones de venta exitosa y de lote registrado, un selector de medicamento con búsqueda al registrar lotes, y un respaldo accesible sin JavaScript.

**Práctico N° 2 — Migraciones, Modelos Eloquent, Factories, Seeders y Consultas en Tinker**  
**Práctico N° 3 — Sistema web con Laravel: autenticación, roles y operación**  
**Materia:** Tecnología Web I  
**Universidad:** Universidad Privada Domingo Savio (UPDS) — Sede Tarija  
**Docente:** Ing. Yanet Colque Alarcon  
**Fecha de Entrega:** 19 de octubre de 2026  

### 👥 Integrantes del Equipo:
* **Nahuel Fernando Martínez Mariscal**
* **Estip Josué López Farfan**

---

## 📖 1. Descripción del Caso de Estudio

El sistema gestiona integralmente el inventario de una farmacia comercial, modelando las relaciones entre familias terapéuticas, laboratorios fabricantes, medicamentos, lotes físicos de abastecimiento y ventas transaccionales.

### ⚙️ Reglas de Negocio Obligatorias Implementadas:
1. **Regla #1 (Alerta de Vencimiento a 30 Días):** Detección proactiva de lotes que vencen dentro de los próximos 30 días para priorizar su rotación mediante política **FEFO** (*First Expired, First Out*).
2. **Regla #2 (Alerta de Stock Mínimo):** Monitoreo continuo del stock disponible de lotes **únicamente vigentes** frente al umbral `stock_minimo` de cada fármaco para disparar alertas de reorden.
3. **Regla #3 (Bloqueo Estricto de Venta de Lotes Vencidos):** La capa transaccional (`VentaService`) aísla e impide cualquier dispensación de lotes cuya fecha de caducidad sea inferior a la fecha actual, manteniendo intacto el stock en auditoría.
4. **Política FEFO de Despacho:** Al registrar una venta, el sistema consume primero el lote vigente con fecha de expiración más cercana antes de descontar del siguiente lote.

---

## 🏗️ 2. Arquitectura de Base de Datos (PostgreSQL)

El esquema relacional cuenta con 6 tablas principales con claves foráneas, restricciones de unicidad e índices optimizados:

* **`categorias`**: Familias terapéuticas (Analgésicos, Antibióticos, etc.).
* **`laboratorios`**: Empresas fabricantes internacionales y nacionales.
* **`medicamentos`**: Fármacos con nombre comercial, principio activo, presentación, precio y stock mínimo. Restricciones CHECK: `precio_venta > 0` y `stock_minimo >= 0`.
* **`lotes`**: Existencias físicas por número de lote único (`numero_lote UNIQUE`), fecha de caducidad y cantidad disponible. Restricción CHECK: `cantidad_stock >= 0`.
* **`ventas`**: Cabecera transaccional con fecha y monto total (`monto_total >= 0`).
* **`detalles_venta`**: Renglones de venta asociados al lote específico dispensado con políticas `ON DELETE CASCADE` en venta y `ON DELETE RESTRICT` en lote. Restricciones CHECK: `cantidad > 0` y `precio_unitario > 0`.

---

## 🛠️ 3. Requisitos del Sistema

* **PHP:** ^8.3 (probado y compatible con PHP 8.4 y 8.5) con extensiones `pdo_pgsql`, `mbstring`, `openssl`.
* **Gestor de dependencias:** Composer 2.x
* **Base de datos:** PostgreSQL 17 / 18
* **Framework:** Laravel 13

---

## 🚀 4. Instalación y Puesta en Marcha

1. **Clonar el repositorio:**
   ```bash
   git clone https://github.com/JosueLopez21/control-inventario-farmacia.git
   cd control-inventario-farmacia
   ```

2. **Instalar dependencias de PHP:**
   ```bash
   composer install
   ```

3. **Crear la base de datos y configurar el entorno:**
   ```bash
   psql -U postgres -c "CREATE DATABASE farmacia_inventario;"
   cp .env.example .env
   ```
   Ajustar las credenciales de PostgreSQL en el archivo `.env`:
   ```env
   DB_CONNECTION=pgsql
   DB_HOST=127.0.0.1
   DB_PORT=5432
   DB_DATABASE=farmacia_inventario
   DB_USERNAME=postgres
   DB_PASSWORD=tu_contraseña
   ```
   *(En Windows puedes crear la base con pgAdmin usando la misma sentencia SQL).*

4. **Generar la clave de aplicación:**
   ```bash
   php artisan key:generate
   ```

5. **Ejecutar migraciones y sembrar datos de prueba:**
   ```bash
   php artisan migrate:fresh --seed
   ```
   *(Este comando crea las 6 tablas con sus restricciones CHECK e inserta 3 usuarios, 10 categorías, 12 laboratorios, 60 principios activos, 1000 medicamentos, 2000 lotes y 1000 ventas con sus detalles. Incluye 200 lotes vencidos, 200 próximos a vencer, 80 medicamentos con stock bajo y 30 agotados para probar las alertas).*

6. **Iniciar el servidor:**
   ```bash
   php artisan serve
   ```
   Abrir en el navegador: **http://127.0.0.1:8000**

---

## 🌐 5. Acceso al Sistema y Roles

| Rol | Correo | Contraseña | Alcance |
| :--- | :--- | :--- | :--- |
| Administrador | `admin@farmacia.test` | `1234` | Todos los módulos, usuarios y eliminaciones |
| Farmacéutico | `farmaceutica@farmacia.test` | `password` | Inventario, ventas, alertas y reportes |
| Doctor | `doctor@farmacia.test` | `password` | Dashboard, catálogo, lotes (lectura), alertas, juego y asesor en solo lectura |

- El **doctor** no puede acceder a ventas, reportes ni usuarios.
- El **farmacéutico** no puede acceder a la gestión de usuarios ni eliminar registros.
- Los usuarios inactivos no pueden iniciar sesión.
- La portada pública está en **`/bienvenida`**; los invitados que entran a `/` son redirigidos allí. El acceso directo sigue disponible en `/login`.
- El manual completo está en [docs/MANUAL_USO.md](docs/MANUAL_USO.md).

### Experiencia de feria

- **Portada `/bienvenida`:** explicación breve, foto, un único acceso al sistema y botones demo agrupados cuando `DEMO_ACCESS=true`.
- **Juego `/juego`:** ruleta de categorías con cinco giros, preguntas generadas desde el catálogo real, puntaje, mejor marca en `localStorage` y un reto rápido de lotes FEFO.
- **Asesor `/asesor`:** orientación educativa para 18 síntomas leves, productos de venta libre con stock vigente, lote próximo a vencer y advertencias de alarma. El doctor solo consulta; administrador y farmacéutico pueden enviar el producto al POS existente.
- **Catálogo:** tarjetas responsivas, filtros por categoría, búsqueda AJAX, paginación y detalle en modal.
- **Dashboard:** indicadores animados, modo presentación en la barra superior y explicación de FEFO con ayuda **ⓘ** directamente en la pantalla; los botones se redujeron al mínimo.
- **Punto de venta:** buscador inteligente con sugerencias mientras escribes (precio, stock, categoría y avisos de vencimiento o agotado), chips de categorías y tarjetas táctiles; si JavaScript no está disponible, respaldo clásico con carrito tradicional.
- **Animaciones:** confirmación visual al registrar una venta exitosa (con monto comprobante y accesos directos) y al registrar un lote (con el medicamento asociado).
- **Lotes:** al registrar un lote, el medicamento se elige con un buscador (por nombre, principio activo, presentación o laboratorio, con navegación de teclado, tecla `Enter`, etiqueta **Agotado** y botón **Cambiar**).
- **Alertas:** semáforo por prioridad, pestañas y acciones directas para reponer o revisar lotes.
- **Acceso demo opcional:** definir `DEMO_ACCESS=true` en `.env` para mostrar los botones de ingreso demo de **farmacéutico** y **doctor**. Por defecto está deshabilitado (`false`).

---

## 🧪 6. Pruebas Automatizadas y Consultas en Tinker

### Ejecución de Pruebas Unitarias (PHPUnit)
Las pruebas usan la base separada `farmacia_inventario_test` (configurada en `phpunit.xml`) y no modifican los datos de `farmacia_inventario`. Crear la base una sola vez:

```bash
psql -U postgres -c "CREATE DATABASE farmacia_inventario_test;"
```

Verifica las reglas de negocio, la política FEFO, el login y el acceso por roles:

```bash
php artisan test
```

Resultado esperado: **63 pruebas aprobadas** (634 aserciones).

### Ejecución de las Consultas en Tinker
* **Modo interactivo manual:**
  ```bash
  php artisan tinker
  ```
  *(Consultar la guía detallada con los comandos en [docs/consultas_tinker.md](docs/consultas_tinker.md))*.

* **Modo script automatizado:**
  Ejecuta todas las 12 consultas con salida formateada en un solo paso:
  ```bash
  php scripts/tinker_queries.php
  ```

---

## 📚 7. Documentación Adicional en la Carpeta `/docs`

* 📖 **Manual de Uso del Sistema Web:** [docs/MANUAL_USO.md](docs/MANUAL_USO.md) (portada, acceso demo, usuarios, roles, recorrido de uso y administración en pgAdmin).
* 🚀 **Guía de Despliegue en Render y Neon:** [docs/DESPLIEGUE.md](docs/DESPLIEGUE.md) (Docker, PostgreSQL en Neon, variables de entorno, verificación y solución de problemas).
* 📊 **Diagrama Entidad-Relación:** [docs/diagrama_er.md](docs/diagrama_er.md) (formato Mermaid y exportación PNG) y [docs/diagrama_er.puml](docs/diagrama_er.puml) (PlantUML).
* 📋 **Consultas de Tinker Detalladas:** [docs/consultas_tinker.md](docs/consultas_tinker.md) (las 12 consultas con comandos, salidas reales y justificaciones técnicas de integridad).
* 📸 **Guía para Toma de Capturas:** [docs/guia_capturas.md](docs/guia_capturas.md) (instrucciones paso a paso de qué comandos correr y qué capturar para el informe en Word).
* 💾 **Respaldo SQL de la Base de Datos:** [database/farmacia_inventario.sql](database/farmacia_inventario.sql) (dump completo con estructura y datos para restaurar en cualquier PostgreSQL).
