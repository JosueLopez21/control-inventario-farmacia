# Diagrama Entidad-Relación — Control de Inventario de Farmacia

Este diagrama representa la estructura de base de datos relacional implementada en PostgreSQL para el sistema de control de inventario de farmacia.

## Diagrama Mermaid

```mermaid
erDiagram
    categorias ||--o{ medicamentos : "clasifica (1 a N, RESTRICT)"
    laboratorios ||--o{ medicamentos : "fabrica (1 a N, RESTRICT)"
    medicamentos ||--o{ lotes : "posee (1 a N, RESTRICT)"
    ventas ||--|{ detalles_venta : "contiene (1 a 1..N, CASCADE)"
    lotes ||--o{ detalles_venta : "se dispensa (1 a N, RESTRICT)"

    categorias {
        bigint id PK "Clave primaria auto-incremental"
        string nombre UK "UNIQUE, INDEX - Nombre de la categoría"
        text descripcion "Descripción terapéutica opcional"
        timestamp created_at
        timestamp updated_at
    }

    laboratorios {
        bigint id PK "Clave primaria auto-incremental"
        string nombre UK "UNIQUE, INDEX - Nombre del laboratorio"
        string pais "INDEX - País de origen"
        string direccion "Dirección física"
        string telefono "Teléfono de contacto"
        string email "Correo corporativo"
        timestamp created_at
        timestamp updated_at
    }

    medicamentos {
        bigint id PK "Clave primaria auto-incremental"
        bigint categoria_id FK "FK -> categorias.id (RESTRICT)"
        bigint laboratorio_id FK "FK -> laboratorios.id (RESTRICT)"
        string nombre_comercial "INDEX - Nombre de marca (ej. Panadol)"
        string principio_activo "INDEX - Fármaco base (ej. Paracetamol)"
        string presentacion "Formato y dosis (ej. Tabletas 500mg x 20)"
        decimal precio_venta "CHECK: precio_venta > 0"
        integer stock_minimo "CHECK: stock_minimo >= 0, default 10"
        timestamp created_at
        timestamp updated_at
    }

    lotes {
        bigint id PK "Clave primaria auto-incremental"
        bigint medicamento_id FK "FK -> medicamentos.id (RESTRICT)"
        string numero_lote UK "UNIQUE - Código de lote (ej. LOT-00001)"
        date fecha_vencimiento "INDEX - Fecha de expiración (para FEFO)"
        integer cantidad_stock "CHECK: cantidad_stock >= 0"
        timestamp created_at
        timestamp updated_at
    }

    ventas {
        bigint id PK "Clave primaria auto-incremental"
        timestamp fecha_venta "INDEX - Fecha y hora de transacción"
        decimal monto_total "CHECK: monto_total >= 0"
        timestamp created_at
        timestamp updated_at
    }

    detalles_venta {
        bigint id PK "Clave primaria auto-incremental"
        bigint venta_id FK "FK -> ventas.id (CASCADE)"
        bigint lote_id FK "FK -> lotes.id (RESTRICT)"
        integer cantidad "CHECK: cantidad > 0"
        decimal precio_unitario "CHECK: precio_unitario > 0"
        timestamp created_at
        timestamp updated_at
    }
```

---

## Cómo exportar el Diagrama a imagen PNG (para el Word)

Tienes tres opciones sencillas:

### Opción 1: Mermaid Live Editor (La más rápida y recomendada)
1. Entra a [https://mermaid.live](https://mermaid.live).
2. Copia el bloque de código `erDiagram` de arriba y pégalo en el editor izquierdo.
3. En la parte inferior derecha, haz clic en **Actions** > **Download PNG** (o haz clic en el botón de captura de pantalla).
4. Guarda la imagen y pégala en tu documento Word.

### Opción 2: PlantUML Server
1. Abre el archivo [docs/diagrama_er.puml](file:///c:/laragon/www/Control%20de%20inventario%20de%20una%20farmacia/docs/diagrama_er.puml).
2. Entra a [http://www.plantuml.com/plantuml/uml/](http://www.plantuml.com/plantuml/uml/).
3. Pega el contenido y haz clic en **PNG** para descargar la imagen en alta definición.

### Opción 3: DBeaver / pgAdmin (Directo de tu PostgreSQL)
* En **DBeaver**: Haz clic derecho sobre tu base de datos `farmacia_inventario` > `Ver Diagrama ER` > Clic derecho > `Guardar diagrama como imagen (PNG)`.
* En **pgAdmin 4**: Haz clic derecho sobre `farmacia_inventario` > `Generate ERD` > Clic en el botón de cámara/guardar como imagen.
