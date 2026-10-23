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
DEMO_ACCESS=false
```

`DEMO_ACCESS` controla los botones de acceso demo de la portada. Déjalo en `false` salvo que necesites mostrar la aplicación sin escribir credenciales. Para habilitarlo, cambia el valor a `true` y reinicia `php artisan serve`. En producción no lo actives sin supervisión.

### 2.3 Crear las tablas y cargar los datos de demostración

```bash
php artisan migrate:fresh --seed
```

Este comando crea el esquema completo y siembra:

- 3 usuarios del sistema.
- 10 categorías y 12 laboratorios.
- 60 principios activos, 1000 medicamentos, 2000 lotes y 1000 ventas con sus detalles.
- 200 lotes vencidos, 200 próximos a vencer, 80 medicamentos con stock bajo y 30 agotados para probar las alertas.

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
| Administrador | Nahuel Martínez | `admin@farmacia.test` | `1234` |
| Farmacéutico | Lic. Carla Rojas | `farmaceutica@farmacia.test` | `password` |
| Doctor | Dr. Andrés Vargas | `doctor@farmacia.test` | `password` |

> Cambia estas contraseñas antes de usar el sistema con datos reales.

### 3.1 Acceso demo desde la portada

Con `DEMO_ACCESS=true`, la portada muestra botones para entrar como **farmacéutico** o **doctor** sin escribir contraseña. El botón de administrador no se ofrece por seguridad.

- Las rutas demo son `POST /demo/farmaceutico` y `POST /demo/doctor`.
- Si `DEMO_ACCESS=false`, esas rutas responden como no encontradas.
- El acceso demo inicia sesión con el primer usuario activo del rol correspondiente.
- La ruta está limitada a invitados y tiene un límite de intentos por minuto.

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

## 5. Recorrido de uso principal

La interfaz está preparada para pantallas desde 360 px, con controles táctiles de al menos 44 px y respeto por la preferencia de movimiento reducido. Los datos y acciones siguen dependiendo del rol.

### Paso 1 — Portada e inicio de sesión

Entra a `http://127.0.0.1:8000`. Si no hay sesión, el sistema muestra la portada `/bienvenida`, con la presentación, el mini reto FEFO y los accesos demo cuando `DEMO_ACCESS=true`.

Para entrar con credenciales, pulsa **Iniciar sesión** o abre `http://127.0.0.1:8000/login`, ingresa el correo y la contraseña de la tabla anterior. El sistema redirige al dashboard según el rol.

### Paso 2 — Revisar el dashboard

Consulta los indicadores del día: ventas, ingresos, ticket promedio, medicamentos con stock bajo y lotes próximos a vencer. El menú lateral muestra solo los módulos permitidos para tu rol.

El botón de **modo presentación** amplía la interfaz y oculta elementos secundarios para exposiciones. La preferencia se guarda en el navegador y se puede desactivar con el mismo botón.

### Paso 3 — Registrar un medicamento y su lote

1. Ve a **Medicamentos → Nuevo medicamento**.
2. Completa categoría, laboratorio, nombre comercial, principio activo, presentación, precio y stock mínimo.
3. Guarda y luego entra a **Lotes → Nuevo lote**.
4. Registra el número de lote, la fecha de vencimiento y la cantidad disponible.

El número de lote es único y la fecha de vencimiento determina el orden FEFO de despacho.

### Paso 4 — Registrar una venta

1. Ve a **Ventas → Nueva venta**.
2. Busca el medicamento por nombre o principio activo y agrégalo al carrito con la cantidad deseada.
3. Ajusta cantidades, elimina líneas si hace falta y revisa el total.
4. Pulsa **Registrar venta**.

La pantalla táctil muestra el stock disponible y los controles grandes para operar en mostrador. Si JavaScript no está disponible, el sistema redirige al formulario clásico para completar la venta sin perder la operación.

El sistema descuenta primero el lote vigente con vencimiento más cercano (FEFO), bloquea lotes vencidos y rechaza la venta si el stock vigente es insuficiente. Desde el detalle de la venta puedes imprimir el comprobante.

### Paso 5 — Atender las alertas

Abre **Alertas** para revisar:

- Lotes que vencen en los próximos 30 días.
- Lotes ya vencidos.
- Medicamentos cuyo stock vigente está por debajo del mínimo.

El semáforo prioriza lo urgente: rojo para vencidos, ámbar para próximos a vencer y gris para stock bajo. Usa las pestañas y las acciones directas para revisar el lote o registrar reposición. Esta información permite priorizar la rotación y programar compras.

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

Resultado esperado: **50 pruebas aprobadas** (230 aserciones). Cubren autenticación, acceso por rol, CRUD de inventario, restricciones de eliminación, ventas FEFO con bloqueo de vencidos y las pantallas de feria, alertas, POS y portada.

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
