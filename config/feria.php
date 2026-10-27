<?php

return [
    'demo_access' => env('DEMO_ACCESS', false),

    // Muestra en la pantalla de acceso los usuarios de demostración (útil en la feria).
    // En Render se apaga con la variable FERIA_MOSTRAR_USUARIOS=false.
    'mostrar_usuarios' => env('FERIA_MOSTRAR_USUARIOS', true),

    // Usuarios base del sistema. El seeder y la pantalla de acceso leen esta misma lista,
    // por eso las contraseñas mostradas coinciden con las sembradas.
    'usuarios' => [
        [
            'name' => 'Nahuel Martínez',
            'email' => 'admin@farmacia.test',
            'password' => env('SEED_ADMIN_PASSWORD') ?: '1234',
            'rol' => 'administrador',
            'rol_etiqueta' => 'Administrador',
            'descripcion' => 'Acceso total',
            'icono' => 'bi-shield-check',
        ],
        [
            'name' => 'Lic. Carla Rojas',
            'email' => 'farmaceutica@farmacia.test',
            'password' => env('SEED_FARMACEUTICO_PASSWORD') ?: 'password',
            'rol' => 'farmaceutico',
            'rol_etiqueta' => 'Farmacéutico',
            'descripcion' => 'Inventario y ventas',
            'icono' => 'bi-capsule',
        ],
        [
            'name' => 'Dr. Andrés Vargas',
            'email' => 'doctor@farmacia.test',
            'password' => env('SEED_DOCTOR_PASSWORD') ?: 'password',
            'rol' => 'doctor',
            'rol_etiqueta' => 'Doctor',
            'descripcion' => 'Consulta del catálogo',
            'icono' => 'bi-heart-pulse',
        ],
    ],

    'categorias' => [
        'analgesicos' => [
            'icono' => 'bi-capsule-pill',
            'color' => '#e11d48',
            'suave' => '#ffe4e6',
        ],
        'antibioticos' => [
            'icono' => 'bi-bug',
            'color' => '#2563eb',
            'suave' => '#dbeafe',
        ],
        'antiinflamatorios' => [
            'icono' => 'bi-bandaid',
            'color' => '#ea580c',
            'suave' => '#ffedd5',
        ],
        'antihipertensivos' => [
            'icono' => 'bi-heart-pulse',
            'color' => '#7c3aed',
            'suave' => '#ede9fe',
        ],
        'vitaminas-y-suplementos' => [
            'icono' => 'bi-sun',
            'color' => '#ca8a04',
            'suave' => '#fef9c3',
        ],
        'antihistaminicos' => [
            'icono' => 'bi-flower1',
            'color' => '#0d9488',
            'suave' => '#ccfbf1',
        ],
        'antidiabeticos' => [
            'icono' => 'bi-droplet',
            'color' => '#4f46e5',
            'suave' => '#e0e7ff',
        ],
        'gastrointestinales' => [
            'icono' => 'bi-activity',
            'color' => '#16a34a',
            'suave' => '#dcfce7',
        ],
        'dermatologicos' => [
            'icono' => 'bi-moisture',
            'color' => '#db2777',
            'suave' => '#fce7f3',
        ],
        'broncodilatadores' => [
            'icono' => 'bi-lungs',
            'color' => '#0891b2',
            'suave' => '#cffafe',
        ],
        'antiparasitarios' => [
            'icono' => 'bi-bug',
            'color' => '#65a30d',
            'suave' => '#ecfccb',
        ],
        'default' => [
            'icono' => 'bi-capsule',
            'color' => '#0f766e',
            'suave' => '#ccfbf1',
        ],
    ],
];
