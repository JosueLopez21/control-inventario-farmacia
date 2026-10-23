# Guía de despliegue en Render y Neon

Esta guía describe cómo publicar el sistema en **Render** usando el `Dockerfile` del repositorio y una base de datos **PostgreSQL en Neon**. La rama prevista para el despliegue es `feature/despliegue-docker`.

> Importante: el contenedor ejecuta las migraciones al arrancar, pero **nunca** ejecuta `migrate:fresh` ni un seed automático. Los datos de demostración se cargan manualmente y solo si se necesitan.

---

## 1. Requisitos

- Repositorio del proyecto en GitHub.
- Cuenta gratuita en [Render](https://render.com) y cuenta gratuita en [Neon](https://neon.tech).
- Rama `feature/despliegue-docker` publicada en GitHub.
- Opcional para probar en local: Docker Desktop con WSL 2 o Hyper-V habilitado.

El proyecto ya incluye:

- `Dockerfile` con PHP 8.4, Apache y las extensiones `pdo_pgsql`, `pgsql`, `mbstring`, `bcmath`, `zip` y `opcache`.
- `docker/entrypoint.sh`, que valida `APP_KEY`, ejecuta `php artisan migrate --force` y genera las cachés de configuración, rutas y vistas.
- `render.yaml` con el Blueprint del servicio web.
- Endpoint de salud en `/up`.

---

## 2. Crear la base de datos en Neon

1. Crear un proyecto en Neon. Elegir la región más cercana a Render; por ejemplo, **AWS US West 2 (Oregon)** si el servicio de Render está en `oregon`.
2. Abrir **Connection Details** y copiar la **conexión directa** (Direct connection), no la conexión agrupada (*pooled*). El host de la conexión directa no termina en `-pooler`.
3. Guardar estos datos:
   - Host: `ep-xxxxxxxxxxxx.us-west-2.aws.neon.tech`
   - Puerto: `5432`
   - Base de datos: `neondb` (o el nombre que se haya creado)
   - Usuario: el usuario del proyecto
   - Contraseña: usar **Show password** y copiarla completa
4. La cadena de conexión tendrá una forma similar a:

   ```text
   postgresql://usuario:password@ep-xxxxxxxxxxxx.us-west-2.aws.neon.tech/neondb?sslmode=require
   ```

5. En Render se usan las variables por separado; **no** se debe pegar la cadena completa en `DATABASE_URL`.

> Neon exige TLS. Por eso el proyecto usa `DB_SSLMODE=require` en producción. Si la contraseña contiene caracteres especiales, no hay que codificarla para Render: se pega tal cual en `DB_PASSWORD`.

---

## 3. Crear el servicio en Render

1. En Render, elegir **New → Blueprint**.
2. Conectar el repositorio y seleccionar la rama `feature/despliegue-docker`.
3. Render detectará `render.yaml`. Revisar que el servicio sea:
   - Tipo: **Web Service**
   - Runtime: **Docker**
   - Plan: **Free**
   - Health check: `/up`
4. Aplicar el Blueprint.
5. Completar las variables marcadas con `sync: false` en el panel del servicio:

| Variable | Valor |
| :--- | :--- |
| `APP_KEY` | Generar con `php artisan key:generate --show` y pegar el valor completo, incluido el prefijo `base64:` |
| `APP_URL` | `https://<nombre-del-servicio>.onrender.com` |
| `DB_HOST` | Host directo de Neon, sin `postgresql://` |
| `DB_DATABASE` | Nombre de la base, por ejemplo `neondb` |
| `DB_USERNAME` | Usuario de Neon |
| `DB_PASSWORD` | Contraseña de Neon |
| `SEED_ADMIN_PASSWORD` | Opcional; contraseña fuerte si se sembrarán datos demo |
| `SEED_FARMACEUTICO_PASSWORD` | Opcional; contraseña fuerte si se sembrarán datos demo |
| `SEED_DOCTOR_PASSWORD` | Opcional; contraseña fuerte si se sembrarán datos demo |
| `DEMO_ACCESS` | `false` en producción; `true` solo para demostraciones controladas |

`APP_KEY` debe tener el formato `base64:...` que produce Laravel. No usar un valor aleatorio sin ese prefijo. No reemplazarla después de la primera puesta en marcha salvo que se acepte invalidar sesiones y datos cifrados.

`DEMO_ACCESS=true` habilita botones que inician sesión como farmacéutico o doctor sin contraseña. En producción debe quedar en `false`; si se habilita para una feria, desactivarlo al terminar y verificar que las cuentas demo sigan siendo de prueba.

### Variables que ya define el Blueprint

- `APP_ENV=production`
- `APP_DEBUG=false`
- `LOG_CHANNEL=stderr`
- `LOG_LEVEL=info`
- `DB_CONNECTION=pgsql`
- `DB_PORT=5432`
- `DB_SSLMODE=require`
- `SESSION_DRIVER=database`
- `SESSION_SECURE_COOKIE=true`
- `CACHE_STORE=database`
- `QUEUE_CONNECTION=database`

Render inyecta `PORT` automáticamente. Apache escucha en `${PORT}` y el contenedor expone el puerto `10000` por defecto.

---

## 4. Primer despliegue

1. Guardar las variables. Render iniciará el build y el despliegue.
2. Revisar los logs. El entrypoint debe mostrar:
   - Validación correcta de `APP_KEY`.
   - Migraciones ejecutadas o ya aplicadas.
   - Cachés de configuración, rutas y vistas generadas.
   - Arranque de Apache.
3. Cuando el health check esté en verde, abrir:

   ```text
   https://<nombre-del-servicio>.onrender.com/up
   ```

   Debe responder `200 OK`.
4. Abrir `/bienvenida` y comprobar que la portada, el mini reto FEFO y los estilos locales cargan. Abrir `/login` y verificar la pantalla de acceso.
5. Iniciar sesión con un usuario existente o crear los usuarios demo (ver sección 6). Con `DEMO_ACCESS=false`, los botones demo no deben aparecer.

> En el plan gratuito el servicio puede suspenderse por inactividad y tardar en responder al primer acceso. Es normal que la primera petición después de un tiempo de espera sea lenta.

---

## 5. Migraciones y arranque

El archivo `docker/entrypoint.sh` ejecuta, en cada arranque:

```bash
php artisan config:clear
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
exec apache2-foreground
```

No se ejecuta `migrate:fresh`, `db:seed` ni ningún borrado de datos. Si una migración falla, el contenedor no arranca y el error aparece en los logs de Render.

Para ejecutar una migración manualmente desde la Shell de Render:

```bash
php artisan migrate --force
```

---

## 6. Carga opcional de datos de demostración

La base recién creada queda vacía. Para cargar los usuarios demo:

1. Definir `SEED_ADMIN_PASSWORD`, `SEED_FARMACEUTICO_PASSWORD` y `SEED_DOCTOR_PASSWORD` con contraseñas fuertes **antes** de sembrar.
2. Abrir la **Shell** del servicio en Render.
3. Ejecutar solo los usuarios:

   ```bash
   php artisan db:seed --class=UserSeeder --force
   ```

4. Para cargar también categorías, laboratorios, medicamentos, lotes y ventas de ejemplo:

   ```bash
   php artisan db:seed --force
   ```

   El seed de feria genera 10 categorías, 12 laboratorios, 300 medicamentos, 750 lotes y 500 ventas con sus detalles, incluyendo escenarios de vencimiento y stock bajo.

> `UserSeeder` usa `updateOrCreate`: si se vuelve a ejecutar, restablece las contraseñas de los tres usuarios demo. No ejecutarlo en una base con usuarios reales sin revisar antes el seeder.

En una instalación real se recomienda no usar las cuentas de demostración: crear usuarios propios, asignar roles y eliminar o desactivar las cuentas demo.

---

## 7. Verificación posterior al despliegue

Comprobar en este orden:

1. `GET /up` responde `200`.
2. `GET /bienvenida` responde `200` y carga CSS, iconos y JavaScript locales.
3. `GET /login` responde `200`; un invitado que entra a `/` es redirigido a `/bienvenida`.
4. El login de un usuario activo redirige al dashboard.
5. El dashboard muestra datos sin errores 500 y el modo presentación funciona.
6. Catálogo, inventario, ventas, alertas y reportes cargan según el rol; el POS táctil y el formulario clásico están disponibles.
7. Con `DEMO_ACCESS=false`, las rutas `/demo/*` no permiten iniciar sesión.
8. En los logs de Render aparece `LOG_CHANNEL=stderr` y no hay errores de conexión a PostgreSQL.
9. Las URLs generadas usan `https://` y las cookies de sesión son seguras.

Pruebas locales antes de publicar:

```bash
php artisan test
```

Resultado esperado: **50 pruebas aprobadas** (230 aserciones).

---

## 8. Probar el contenedor en local

Con Docker Desktop iniciado (WSL 2 o Hyper-V):

```bash
docker compose up --build
```

Abrir `http://localhost:8000`. La base de PostgreSQL del compose queda expuesta en `localhost:5433`.

Detener el entorno:

```bash
docker compose down
```

Eliminar también el volumen de la base local:

```bash
docker compose down -v
```

Si Compose indica que falta `APP_KEY`, generar una clave y exportarla antes de levantar:

```powershell
$env:APP_KEY = php artisan key:generate --show
docker compose up --build
```

En Linux/macOS:

```bash
export APP_KEY="$(php artisan key:generate --show)"
docker compose up --build
```

---

## 9. Solución de problemas

### El servicio devuelve 500

- Revisar los logs de Render.
- Confirmar que `APP_KEY` existe y no se cambió después del primer despliegue.
- Verificar `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` y `DB_SSLMODE=require`.
- Confirmar que se usó la conexión directa de Neon, no la pooled.

### `SQLSTATE[08006]` o error de conexión

- La base puede estar suspendida por inactividad; reintentar el acceso.
- Verificar que el host no incluya `postgresql://` ni la contraseña.
- Revisar que el usuario y la base existan en Neon.
- Si se cambió la contraseña en Neon, actualizar `DB_PASSWORD` en Render y redesplegar.

### `relation "sessions" does not exist` o tablas faltantes

- Revisar si las migraciones fallaron durante el arranque.
- Ejecutar desde la Shell:

  ```bash
  php artisan migrate --force
  ```

### El servicio responde 502 o el health check falla

- Confirmar en los logs que Apache arrancó y que escucha en el puerto indicado por `PORT`.
- No fijar `PORT` manualmente en Render; la plataforma lo inyecta.
- Revisar que el `Dockerfile` no haya cambiado el `Listen` de Apache.

### Bucle de redirecciones o enlaces con `http://`

- El proyecto confía en el proxy de Render y fuerza HTTPS en producción.
- Verificar que `APP_ENV=production` y `APP_URL` empiecen con `https://`.
- Limpiar cachés y redesplegar:

  ```bash
  php artisan optimize:clear
  ```

### Los archivos subidos desaparecen

El plan gratuito de Render usa almacenamiento efímero. No usar el disco local para archivos permanentes; configurar un almacenamiento externo si se necesita.

### El primer acceso es lento

Es el comportamiento esperado del plan gratuito de Render cuando el servicio estuvo inactivo.

---

## 10. Seguridad y mantenimiento

- Mantener `APP_DEBUG=false` en producción.
- No subir `.env`, contraseñas, cadenas de conexión ni respaldos con datos reales al repositorio.
- Usar contraseñas fuertes para Neon y para los usuarios del sistema.
- Cambiar o desactivar las cuentas demo antes de usar datos reales.
- Rotar la contraseña de Neon si se sospecha una filtración.
- Revisar los logs de Render después de cada despliegue.
- Usar el historial de despliegues de Render para volver a una versión anterior si es necesario.
- Neon permite crear ramas y restaurar datos; revisar la retención del plan contratado.

---

## 11. Lista de comprobación final

- [ ] Rama correcta publicada en GitHub.
- [ ] Proyecto de Neon creado y conexión directa copiada.
- [ ] Blueprint de Render aplicado con plan Free.
- [ ] `APP_URL` configurada con HTTPS.
- [ ] Variables `DB_*` completas y `DB_SSLMODE=require`.
- [ ] `APP_KEY` configurada manualmente con el prefijo `base64:`.
- [ ] `DEMO_ACCESS=false` en producción.
- [ ] Health check `/up` en verde.
- [ ] Portada `/bienvenida`, login y dashboard funcionando.
- [ ] Datos demo cargados solo si se necesitan.
- [ ] Cuentas demo cambiadas o eliminadas en producción.
- [ ] Logs sin errores y respaldo de Neon verificado.
