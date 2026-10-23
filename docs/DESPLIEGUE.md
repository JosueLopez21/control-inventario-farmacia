# Guía de despliegue en Render y Neon

Esta guía explica, paso a paso, cómo publicar el sistema de control de inventario de farmacia en **Render** usando un **Web Service con Docker** y una base de datos PostgreSQL externa en **Neon**.

> Importante: la guía asume que el repositorio está en GitHub y que la rama `feature/despliegue-docker` ya fue revisada y fusionada (o subida) a la rama que Render va a desplegar. No subas el archivo `.env` ni contraseñas reales al repositorio.

## Índice

1. [Requisitos previos](#1-requisitos-previos)
2. [Crear la base de datos en Neon](#2-crear-la-base-de-datos-en-neon)
3. [Cargar los datos desde tu PC](#3-cargar-los-datos-desde-tu-pc)
4. [Generar la APP_KEY](#4-generar-la-app_key)
5. [Crear el Web Service en Render](#5-crear-el-web-service-en-render)
6. [Variables de entorno de Render](#6-variables-de-entorno-de-render)
7. [Verificar que todo funciona](#7-verificar-que-todo-funciona)
8. [Advertencias del plan gratuito](#8-advertencias-del-plan-gratuito)
9. [Solución de problemas](#9-solución-de-problemas)
10. [Prueba local opcional con Docker](#10-prueba-local-opcional-con-docker)

---

## 1. Requisitos previos

En tu PC necesitas:

- PHP 8.4 o superior con las extensiones `pdo_pgsql` y `mbstring`.
- Composer 2.
- Git.
- Opcional: Docker Desktop, solo si quieres probar la imagen localmente.

Comprueba las versiones:

```powershell
php -v
composer -V
git --version
docker --version
```

También necesitas:

- Una cuenta gratuita en [Neon](https://neon.com).
- Una cuenta gratuita en [Render](https://render.com).
- El proyecto subido a un repositorio **público** de GitHub. Render necesita leer el repositorio para construir la imagen.

---

## 2. Crear la base de datos en Neon

1. Entra a [https://console.neon.com](https://console.neon.com) e inicia sesión.
2. Crea un **proyecto** nuevo. Neon crea automáticamente una base de datos (normalmente llamada `neondb`) y un usuario (normalmente `neondb_owner`).
3. Abre el panel **Connection Details** o **Connect** del proyecto.
4. Selecciona la conexión **Direct connection** (no la de *pooler*).
5. Copia y guarda estos datos en un lugar seguro:
   - **Host**: algo parecido a `ep-xxxxx-xxxxx.us-east-2.aws.neon.tech`.
   - **Database**: el nombre de la base, por ejemplo `neondb`.
   - **User**: el usuario, por ejemplo `neondb_owner`.
   - **Password**: la contraseña que muestra Neon. Si no la ves, usa **Reset password** y guarda la nueva.
   - **Port**: `5432`.

> Usa siempre el **host directo**, no el host que contiene `-pooler`. El pooler de Neon puede interferir con las migraciones, las transacciones y los bloqueos de fila que usa el sistema de ventas. Neon exige SSL, por eso configuraremos `DB_SSLMODE=require`.

Neon también te muestra una cadena de conexión parecida a esta:

```text
postgresql://neondb_owner:TU_PASSWORD@ep-xxxxx-xxxxx.us-east-2.aws.neon.tech/neondb?sslmode=require
```

De esa cadena salen el host, el usuario, la contraseña y el nombre de la base. **No copies esa cadena completa en el repositorio ni en la guía**.

---

## 3. Cargar los datos desde tu PC

Esta operación crea las tablas y carga los datos iniciales directamente en Neon desde tu computadora. Se hace **una sola vez**, antes de abrir la aplicación al público.

> **Cuidado:** `migrate:fresh` borra todas las tablas y datos de la base de datos configurada. Asegúrate de que las variables de entorno de esta sesión apunten a la base de Neon correcta y no a tu base local.

Abre **PowerShell** en la carpeta del proyecto y ejecuta:

```powershell
php artisan config:clear
```

Esto es importante: si existe una configuración cacheada de tu entorno local, Laravel ignoraría las variables de esta sesión.

Ahora define las variables de conexión a Neon. Reemplaza los valores entre comillas por los de tu proyecto:

```powershell
$env:DB_CONNECTION="pgsql"
$env:DB_HOST="ep-xxxxx-xxxxx.us-east-2.aws.neon.tech"
$env:DB_PORT="5432"
$env:DB_DATABASE="neondb"
$env:DB_USERNAME="neondb_owner"
$env:DB_PASSWORD="TU_PASSWORD_DE_NEON"
$env:DB_SSLMODE="require"
```

> Si la contraseña de Neon contiene caracteres especiales como `$`, usa comillas simples en PowerShell, por ejemplo `$env:DB_PASSWORD='mi$clave'`. Las comillas dobles interpolan `$` y podrían alterar la contraseña.

Si la aplicación va a estar en una URL pública, define también contraseñas fuertes para los tres usuarios iniciales. Se usarán al ejecutar el seeder:

```powershell
$env:SEED_ADMIN_PASSWORD="UnaClaveLargaYUnicaParaAdmin2026!"
$env:SEED_FARMACEUTICA_PASSWORD="OtraClaveLargaYUnica2026!"
$env:SEED_DOCTOR_PASSWORD="ClaveDelDoctorLargaYUnica2026!"
```

No uses `1234` ni `password` en una web pública: son credenciales de demostración y cualquier persona podría probarlas. Si no defines estas variables, el seeder usará las contraseñas de demostración (`1234`, `password`, `password`) únicamente para pruebas locales.

Ejecuta las migraciones y el seeder:

```powershell
php artisan migrate:fresh --seed --force
```

Este comando:

- Borra el esquema actual de la base apuntada.
- Crea las tablas.
- Crea 3 usuarios, 10 categorías, 12 laboratorios, 1.000 medicamentos, 1.000 lotes y 1.000 ventas con sus detalles.

Puede tardar varios minutos porque la conexión a Neon es remota.

Comprueba que los datos llegaron:

```powershell
php artisan migrate:status
php artisan db:show --counts
```

En la tabla de conteos debes ver, entre otras:

- `users`: 3
- `categorias`: 10
- `laboratorios`: 12
- `medicamentos`: 1000
- `lotes`: 1000
- `ventas`: 1000

También puedes comprobar el total de usuarios:

```powershell
php artisan tinker --execute="echo App\Models\User::count().PHP_EOL;"
```

Debe imprimir `3`. Los correos de los tres usuarios son:

- `admin@farmacia.test` (rol administrador)
- `farmaceutica@farmacia.test` (rol farmacéutico)
- `doctor@farmacia.test` (rol doctor)

Las contraseñas son las que hayas definido en `SEED_*`; si no definiste ninguna, son las de demostración.

Cuando termines, **cierra esa ventana de PowerShell** para que las credenciales no queden en el entorno. No guardes estas variables en archivos `.env` versionados.

---

## 4. Generar la APP_KEY

Laravel necesita una clave de aplicación para cifrar sesiones y cookies. Genérala en tu PC:

```powershell
php artisan key:generate --show
```

La salida será parecida a:

```text
base64:rSt0...unaCadenaLarga...
```

Copia el valor completo, incluido el prefijo `base64:`. Lo pegarás en la variable `APP_KEY` de Render.

> No cambies la `APP_KEY` después de publicar la aplicación: si la cambias, se invalidan las sesiones y los datos cifrados. Guarda una copia segura de la clave.

---

## 5. Crear el Web Service en Render

Tienes dos formas de crear el servicio.

### Opción A: con el Blueprint (`render.yaml`)

El proyecto incluye `render.yaml` con la configuración del servicio. En Render:

1. Entra a [https://dashboard.render.com](https://dashboard.render.com).
2. Pulsa **New** y elige **Blueprint**.
3. Conecta tu cuenta de GitHub y selecciona el repositorio.
4. Render leerá `render.yaml` y mostrará el servicio `control-inventario-farmacia`.
5. Pulsa **Apply**.
6. Completa las variables marcadas con `sync: false` (las verás en la sección de variables de entorno).
7. Pulsa **Deploy**.

### Opción B: manualmente

1. Entra a [https://dashboard.render.com](https://dashboard.render.com).
2. Pulsa **New** y elige **Web Service**.
3. Conecta GitHub y selecciona el repositorio.
4. Configura:
   - **Name**: `control-inventario-farmacia` (o el nombre que prefieras).
   - **Region**: la más cercana a tus usuarios.
   - **Runtime**: **Docker**.
   - **Instance Type**: **Free**.
   - **Health Check Path**: `/up`.
5. Render detectará el `Dockerfile` de la raíz del repositorio.
6. Antes del primer despliegue, agrega las variables de entorno de la sección siguiente.
7. Pulsa **Create Web Service** o **Deploy**.

El primer build tarda varios minutos: instala Composer, las extensiones de PHP y construye la imagen. Puedes seguir el progreso en **Logs**.

---

## 6. Variables de entorno de Render

En Render, abre tu servicio y ve a **Environment** / **Environment Variables**. Agrega exactamente estas variables:

| Variable | Valor | De dónde sale |
| :--- | :--- | :--- |
| `APP_ENV` | `production` | Valor fijo. |
| `APP_DEBUG` | `false` | Valor fijo. No mostrar errores al público. |
| `APP_URL` | `https://TU-SERVICIO.onrender.com` | La URL que Render asigna al servicio, sin barra final. Si usas dominio propio, usa ese. |
| `APP_KEY` | `base64:...` | Salida de `php artisan key:generate --show`. |
| `LOG_CHANNEL` | `stderr` | Valor fijo. Los logs salen en **Logs** de Render. |
| `DB_CONNECTION` | `pgsql` | Valor fijo. |
| `DB_HOST` | `ep-xxxxx-xxxxx.us-east-2.aws.neon.tech` | Host **directo** de Neon (sin `-pooler`). |
| `DB_PORT` | `5432` | Puerto de Neon. |
| `DB_DATABASE` | `neondb` | Nombre de la base en Neon. |
| `DB_USERNAME` | `neondb_owner` | Usuario de Neon. |
| `DB_PASSWORD` | `TU_PASSWORD_DE_NEON` | Contraseña de Neon. |
| `DB_SSLMODE` | `require` | Neon exige SSL. |
| `SESSION_DRIVER` | `database` | Usa la tabla de sesiones incluida en las migraciones. |
| `CACHE_STORE` | `database` | Usa la tabla de caché incluida en las migraciones. |
| `APP_LOCALE` | `es` | Idioma de la aplicación. |

Opcionalmente puedes agregar:

| Variable | Valor | Uso |
| :--- | :--- | :--- |
| `APP_TIMEZONE` | `America/La_Paz` | Zona horaria de la farmacia. |
| `SEED_ADMIN_PASSWORD` | contraseña fuerte | Solo si vas a ejecutar el seeder desde Render. No es necesaria en el arranque normal. |
| `SEED_FARMACEUTICA_PASSWORD` | contraseña fuerte | Igual que la anterior. |
| `SEED_DOCTOR_PASSWORD` | contraseña fuerte | Igual que la anterior. |

> El `docker/entrypoint.sh` **no ejecuta seeders**. Solo limpia la caché, ejecuta `php artisan migrate --force` y genera las cachés de configuración, rutas y vistas. Los datos iniciales se cargan desde tu PC en el paso 3. Si más adelante necesitas volver a sembrar, hazlo solo sobre una base vacía o revisa antes los seeders: `UserSeeder` usa `updateOrCreate`, pero los demás crean registros nuevos y podrían duplicar datos.

Cada vez que cambies una variable de entorno, Render reiniciará el servicio. El entrypoint volverá a ejecutar las migraciones pendientes.

---

## 7. Verificar que todo funciona

Cuando el despliegue termine y el estado sea **Live**:

1. **Health check:** abre

   ```text
   https://TU-SERVICIO.onrender.com/up
   ```

   Debe responder con un estado HTTP 200. Si Render muestra el servicio como no saludable, revisa los logs.

2. **Login:** abre

   ```text
   https://TU-SERVICIO.onrender.com/login
   ```

   La página debe cargar con Bootstrap e iconos locales (no depende de CDN).

3. **Cada rol:** inicia sesión con los tres usuarios:
   - Administrador: acceso a usuarios, inventario, ventas y reportes.
   - Farmacéutico: inventario, ventas, alertas y reportes; sin gestión de usuarios.
   - Doctor: dashboard, catálogo, lotes y alertas; sin ventas, reportes ni usuarios.

   Comprueba también que el doctor recibe una página **403** al intentar entrar a `/usuarios` o `/ventas`, y que esa página se ve correctamente.

4. **Venta de prueba:**
   - Inicia sesión como administrador o farmacéutico.
   - Ve a **Ventas > Nueva venta**.
   - Elige un medicamento con lotes vigentes.
   - Registra una cantidad pequeña y confirma la venta.
   - Verifica que la venta aparece en el listado y que el stock del lote se descontó.
   - Si hay más de un lote, el sistema debe consumir primero el que vence antes (FEFO).

5. **Formularios y CSRF:** crea o edita una categoría, un laboratorio o un medicamento. Si el formulario funciona, la sesión, el token CSRF y HTTPS están configurados correctamente.

---

## 8. Advertencias del plan gratuito

### Render

- El Web Service del plan **Free** se suspende después de un período de inactividad (normalmente unos 15 minutos sin tráfico).
- La primera petición después de la suspensión tarda cerca de **1 minuto** en despertar el servicio. Durante ese tiempo el navegador puede mostrar un error o quedarse cargando.
- Antes de una exposición o demostración, abre `/up` unos minutos antes para "despertar" el servicio.
- Los planes gratuitos tienen límites de horas de instancia y de ancho de banda. Las condiciones cambian con el tiempo: revisa [render.com/docs/free](https://render.com/docs/free).
- El sistema de archivos del contenedor es efímero. No guardes archivos importantes en `storage/` ni en `public/`: usa un almacenamiento externo si algún día agregas subida de archivos.
- Los logs se envían a la salida estándar (`LOG_CHANNEL=stderr`) y se ven en **Logs** de Render. No se conservan como archivos dentro del contenedor.

### Neon

- El plan gratuito de Neon tiene límites de almacenamiento y de horas de cómputo, y suspende el cómputo tras un período de inactividad. La primera consulta después de la suspensión puede tardar unos segundos.
- Un proyecto gratuito puede ser suficiente para una demostración, pero revisa los límites vigentes en [neon.com/pricing](https://neon.com/pricing).
- Neon exige SSL. Mantén `DB_SSLMODE=require`.
- Usa el host **directo** para las migraciones y para la aplicación. El pooler está pensado para conexiones serverless y puede causar problemas con transacciones y bloqueos.

> Las condiciones de los planes gratuitos de Render y Neon pueden cambiar. Antes de una entrega o exposición, revisa las páginas oficiales de precios y límites.

---

## 9. Solución de problemas

### Error de SSL con Neon

Síntomas: mensajes como `SSL required`, `no pg_hba.conf entry ... no encryption`, `could not connect to server` o errores de certificado.

Solución:

- Verifica que `DB_SSLMODE=require` esté configurado en Render.
- Usa el **host directo** de Neon, no el que contiene `-pooler`.
- No definas `DB_URL` si no estás seguro: una URL de conexión puede sobrescribir las variables `DB_*`.
- Copia de nuevo el host y el usuario desde el panel de Neon.

### "No open ports detected"

Render no detecta ningún puerto abierto y detiene el despliegue.

Causas y solución:

- El contenedor no está escuchando en el puerto que Render espera. Este proyecto usa Apache con `Listen ${PORT}` y `ENV PORT=10000`; no cambies el `Dockerfile` sin mantener esa lógica.
- Revisa los logs del build y del contenedor. Si el entrypoint falla (por ejemplo, por `APP_KEY` o por la base de datos), el contenedor se cierra y Render no encuentra el puerto.
- Asegúrate de que el servicio esté configurado como **Docker** y de que el `Dockerfile` esté en la raíz del repositorio.
- No configures manualmente un puerto distinto en Render salvo que también cambies `PORT` en el servicio.

### Error de APP_KEY

Síntomas: `No application encryption key has been specified`, errores 500 al iniciar sesión o cookies que no funcionan.

Solución:

- Ejecuta `php artisan key:generate --show` y copia el valor completo, con el prefijo `base64:`.
- Pégalo en `APP_KEY` de Render sin comillas adicionales ni espacios.
- Si cambiaste la clave, los usuarios deberán volver a iniciar sesión.
- La clave debe ser la misma entre despliegues. No la generes de nuevo en cada build.

### Página en blanco

- Revisa **Logs** en Render. Con `LOG_CHANNEL=stderr` los errores aparecen allí.
- Comprueba que `APP_KEY`, `APP_URL` y las variables de la base estén completas.
- Verifica que `storage/` y `bootstrap/cache/` tengan permisos de escritura. El `Dockerfile` los asigna a `www-data` y el entrypoint vuelve a ajustarlos.
- Si el problema empezó después de un cambio, prueba un **Manual Deploy > Clear build cache & deploy**.
- Para diagnosticar, puedes poner `APP_DEBUG=true` temporalmente, pero vuelve a `false` antes de dejar la aplicación pública.

### Error 500

- Abre **Logs** en Render y busca la excepción real. Los errores 500 casi siempre dejan un stack trace en los logs.
- Causas frecuentes:
  - No se pudo conectar a Neon (host, usuario, contraseña, SSL).
  - Las migraciones no se ejecutaron (revisa el log del entrypoint).
  - `APP_KEY` ausente o inválida.
  - Una variable de entorno con un valor incorrecto.
- Si cambiaste variables, espera el reinicio del servicio.
- No uses `migrate:fresh` en producción: borra los datos.

### Problemas de HTTPS o CSRF (error 419)

- La aplicación confía en los proxies de Render (`trustProxies(at: '*')`) y fuerza HTTPS en producción (`URL::forceScheme('https')`). No elimines esa configuración.
- `APP_URL` debe empezar por `https://` y coincidir con el dominio que usas.
- `SESSION_DRIVER=database` requiere la tabla `sessions`, creada por las migraciones.
- No cambies `APP_KEY` entre despliegues.
- Si el navegador tiene cookies viejas del dominio, bórralas o prueba en una ventana privada.
- Si usas un dominio propio, configura el dominio en Render y actualiza `APP_URL`.

### La primera petición tarda mucho

Es normal en el plan gratuito: Render despierta el servicio y Neon despierta el cómputo. Abre `/up` y espera. Si el problema persiste, revisa los logs y el estado de la base en Neon.

---

## 10. Prueba local opcional con Docker

Si tienes Docker Desktop instalado, puedes probar la imagen antes de desplegar:

```powershell
docker compose up --build
```

La aplicación quedará en `http://localhost:10000`. El servicio `db` usa PostgreSQL 17 y el entrypoint ejecuta las migraciones.

Para cargar los datos de demostración en ese contenedor (solo la primera vez, sobre una base vacía):

```powershell
docker compose exec app php artisan db:seed --force
```

> No ejecutes el seeder dos veces sobre la misma base: los seeders de catálogo, lotes y ventas crean registros nuevos y duplicarían los datos.

Para detener y borrar los contenedores y el volumen de la base local:

```powershell
docker compose down -v
```

> `docker-compose.yml` es solo para pruebas locales. Render usa el `Dockerfile` y las variables de entorno del panel, no este archivo.

---

## Resumen rápido

1. Crea la base en Neon y copia el host directo, usuario, contraseña y nombre.
2. Desde PowerShell, apunta `DB_*` a Neon y ejecuta `php artisan migrate:fresh --seed --force`.
3. Genera `APP_KEY` con `php artisan key:generate --show`.
4. Crea el Web Service en Render con runtime Docker, plan Free y health check `/up`.
5. Configura las variables de entorno de la sección 6.
6. Verifica `/up`, `/login`, los tres roles y una venta de prueba.
7. Antes de una exposición, despierta el servicio abriendo `/up`.
