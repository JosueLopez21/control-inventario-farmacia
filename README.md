# Sistema de Control de Inventario de una Farmacia

**Práctico N° 2 — Migraciones, Modelos Eloquent, Factories, Seeders y Consultas en Tinker**  
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

3. **Configurar el entorno:**
   Copiar la plantilla de variables de entorno:
   ```bash
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

4. **Generar la clave de aplicación:**
   ```bash
   php artisan key:generate
   ```

5. **Ejecutar migraciones y sembrar datos de prueba:**
   ```bash
   php artisan migrate:fresh --seed
   ```
   *(Este comando creará las 6 tablas con sus restricciones CHECK e insertará 10 categorías, 12 laboratorios, 30 medicamentos, 120 lotes y 25 ventas)*.

---

## 🧪 5. Pruebas Automatizadas y Consultas en Tinker

### Ejecución de Pruebas Unitarias (PHPUnit)
Verifica las 3 reglas de negocio y la política FEFO:
```bash
php artisan test
```

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

## 📚 6. Documentación Adicional en la Carpeta `/docs`

* 📊 **Diagrama Entidad-Relación:** [docs/diagrama_er.md](docs/diagrama_er.md) (formato Mermaid y exportación PNG) y [docs/diagrama_er.puml](docs/diagrama_er.puml) (PlantUML).
* 📋 **Consultas de Tinker Detalladas:** [docs/consultas_tinker.md](docs/consultas_tinker.md) (las 12 consultas con comandos, salidas reales y justificaciones técnicas de integridad).
* 📸 **Guía para Toma de Capturas:** [docs/guia_capturas.md](docs/guia_capturas.md) (instrucciones paso a paso de qué comandos correr y qué capturar para el informe en Word).
* 💾 **Respaldo SQL de la Base de Datos:** [database/farmacia_inventario.sql](database/farmacia_inventario.sql) (dump completo con estructura y datos para restaurar en cualquier PostgreSQL).
