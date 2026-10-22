# Manual de Uso — FarmaSalud

Sistema web de control de inventario para farmacia desarrollado con Laravel 13 y PostgreSQL.

---

## 1. Requisitos

- PHP 8.3 o superior con las extensiones `pdo_pgsql`, `mbstring` y `openssl`.
- Composer 2.x.
- PostgreSQL 17 o superior.
- Git (opcional, para clonar el repositorio).

---

## 2. Puesta en marcha

### 2.1 Crear la base de datos

Con `psql`:

```bash
psql -U postgres -c "CREATE DATABASE farmacia_inventario;"
```

También puedes crearla desde pgAdmin con el Query Tool usando la misma sentencia SQL.

### 2.2 Instalar dependencias y configurar el entorno

```bash
composer install
copy .env.example .env
php artisan key:generate
```

Edita `.env` con tus credenciales de PostgreSQL:

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=farmacia_inventario
DB_USERNAME=postgres
DB_PASSWORD=tu_contraseña
```

### 2.3 Crear las tablas y cargar los datos de demostración

```bash
php artisan migrate:fresh --seed
```

Este comando crea el esquema completo y siembra:

- 3 usuarios del sistema.
- 10 categorías y 12 laboratorios.
- 1.000 medicamentos, 1.000 lotes y 1.000 ventas con sus detalles.
- Lotes vencidos, próximos a vencer y con stock bajo para probar las alertas.

> `migrate:fresh` elimina las tablas existentes. No lo ejecutes sobre una base con información real.

### 2.4 Iniciar el servidor

```bash
php artisan serve
```

Abre en el navegador: **http://127.0.0.1:8000**

Si el puerto 8000 está ocupado:

```bash
php artisan serve --port=8080
```

---

## 3. Usuarios de prueba

| Rol | Nombre | Correo | Contraseña |
| :--- | :--- | :--- | :--- |
| Administrador | Nahuel Martínez | `admin@farmacia.test` | `Admin2026*` |
| Farmacéutico | Lic. Carla Rojas | `farmaceutica@farmacia.test` | `Farma2026*` |
| Doctor | Dr. Andrés Vargas | `doctor@farmacia.test` | `Doctor2026*` |

> Cambia estas contraseñas antes de usar el sistema con datos reales.

---

## 4. Roles y permisos

| Módulo | Administrador | Farmacéutico | Doctor |
| :--- | :---: | :---: | :---: |
| Dashboard | Sí | Sí | Sí |
| Catálogo de medicamentos | Ver y gestionar | Ver y gestionar | Solo lectura |
| Lotes | Ver y gestionar | Ver y gestionar | Solo lectura |
| Categorías y laboratorios | Ver y gestionar | Ver y gestionar | Sin acceso |
| Ventas | Sí | Sí | Sin acceso |
| Alertas | Sí | Sí | Sí |
| Reportes | Sí | Sí | Sin acceso |
| Usuarios | Sí | Sin acceso | Sin acceso |
| Eliminar registros | Sí | Sin acceso | Sin acceso |
| Cambiar su contraseña | Sí | Sí | Sí |

Reglas adicionales:

- Un usuario con `activo = false` no puede iniciar sesión.
- El administrador no puede desactivar su propia cuenta.
- El módulo de usuarios permite crear, editar, restablecer contraseña y activar/desactivar cuentas.

---

## 5. Recorrido de uso en 6 pasos

### Paso 1 — Iniciar sesión

Entra a `http://127.0.0.1:8000`, ingresa el correo y la contraseña de la tabla anterior. El sistema redirige al dashboard según el rol.

### Paso 2 — Revisar el dashboard

Consulta los indicadores del día: ventas, ingresos, ticket promedio, medicamentos con stock bajo y lotes próximos a vencer. El menú lateral muestra solo los módulos permitidos para tu rol.

### Paso 3 — Registrar un medicamento y su lote

1. Ve a **Medicamentos → Nuevo medicamento**.
2. Completa categoría, laboratorio, nombre comercial, principio activo, presentación, precio y stock mínimo.
3. Guarda y luego entra a **Lotes → Nuevo lote**.
4. Registra el número de lote, la fecha de vencimiento y la cantidad disponible.

El número de lote es único y la fecha de vencimiento determina el orden FEFO de despacho.

### Paso 4 — Registrar una venta

1. Ve a **Ventas → Nueva venta**.
2. Busca el medicamento por nombre y agrégalo al carrito con la cantidad deseada.
3. Pulsa **Registrar venta**.

El sistema descuenta primero el lote vigente con vencimiento más cercano (FEFO), bloquea lotes vencidos y rechaza la venta si el stock vigente es insuficiente. Desde el detalle de la venta puedes imprimir el comprobante.

### Paso 5 — Atender las alertas

Abre **Alertas** para revisar:

- Lotes que vencen en los próximos 30 días.
- Lotes ya vencidos.
- Medicamentos cuyo stock vigente está por debajo del mínimo.

Usa esta información para priorizar la rotación y programar reposición.

### Paso 6 — Consultar reportes y administrar usuarios

- **Reportes:** ventas por día y por mes, medicamentos más vendidos, ingresos por categoría y ticket promedio.
- **Usuarios** (solo administrador): alta de cuentas, cambio de rol, activación/desactivación y restablecimiento de contraseña.
- **Mi contraseña:** disponible para todos los usuarios desde el menú superior.

---

## 6. Pruebas automatizadas

Las pruebas usan una base separada llamada `farmacia_inventario_test`, definida en `phpunit.xml`. No modifican los datos de `farmacia_inventario`.

Crear la base de pruebas una sola vez:

```bash
psql -U postgres -c "CREATE DATABASE farmacia_inventario_test;"
```

Ejecutar la suite:

```bash
php artisan test
```

Resultado esperado: **24 pruebas aprobadas**. Cubren autenticación, acceso por rol, CRUD de inventario, restricciones de eliminación y ventas FEFO con bloqueo de vencidos.

---

## 7. Ver los datos en pgAdmin

1. Abre pgAdmin y registra un servidor:
   - **Name:** Farmacia Local
   - **Host:** `127.0.0.1`
   - **Port:** `5432`
   - **Username:** `postgres`
   - **Password:** tu contraseña de PostgreSQL
2. En el árbol lateral abre **Databases → farmacia_inventario → Schemas → public → Tables**.
3. Haz clic derecho sobre una tabla y elige **View/Edit Data → All Rows**.
4. Para revisar los usuarios, abre la tabla **`users`**. Allí verás `name`, `email`, `rol` y `activo`.
5. También puedes usar **Tools → Query Tool**:

```sql
SELECT id, name, email, rol, activo FROM users ORDER BY id;

SELECT count(*) AS total_medicamentos FROM medicamentos;
SELECT count(*) AS total_lotes FROM lotes;
SELECT count(*) AS total_ventas FROM ventas;
```

Si la tabla `users` no aparece, actualiza el árbol con clic derecho sobre **Tables → Refresh**.

---

## 8. Solución de problemas

| Problema | Solución |
| :--- | :--- |
| Error de conexión a PostgreSQL | Verifica host, puerto, usuario y contraseña en `.env`. |
| `could not find driver` | Habilita la extensión `pdo_pgsql` en `php.ini`. |
| La página no refleja cambios | Ejecuta `php artisan config:clear` y `php artisan view:clear`. |
| Puerto 8000 ocupado | Usa `php artisan serve --port=8080`. |
| Las pruebas borran datos | No ocurre: `phpunit.xml` apunta a `farmacia_inventario_test`. Verifica ese archivo si cambiaste la configuración. |

---

## 9. Recomendaciones

- No subir `.env` al repositorio.
- Cambiar las contraseñas de demostración al desplegar.
- Programar respaldos periódicos con `pg_dump`.
- Ejecutar `php artisan test` antes de cada entrega o despliegue.
