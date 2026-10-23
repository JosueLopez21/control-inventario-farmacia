<?php

/**
 * Catálogo base para la demostración de feria.
 *
 * Cada principio activo genera cinco presentaciones comerciales, sin sufijos
 * numéricos en los nombres visibles. Los 60 principios activos se distribuyen
 * en las 10 categorías terapéuticas existentes.
 */
return [
    'variantes' => ['', 'Forte', 'Plus', 'Infantil', 'Max'],

    'medicamentos' => [
        ['principio_activo' => 'Paracetamol', 'marca' => 'Panadol', 'categoria' => 'Analgésicos'],
        ['principio_activo' => 'Ibuprofeno', 'marca' => 'Advil', 'categoria' => 'Analgésicos'],
        ['principio_activo' => 'Ácido acetilsalicílico', 'marca' => 'Aspirina', 'categoria' => 'Analgésicos'],
        ['principio_activo' => 'Diclofenaco sódico', 'marca' => 'Voltaren', 'categoria' => 'Analgésicos'],
        ['principio_activo' => 'Naproxeno sódico', 'marca' => 'Apronax', 'categoria' => 'Analgésicos'],
        ['principio_activo' => 'Tramadol', 'marca' => 'Tramal', 'categoria' => 'Analgésicos'],

        ['principio_activo' => 'Amoxicilina', 'marca' => 'Amoxil', 'categoria' => 'Antibióticos'],
        ['principio_activo' => 'Azitromicina', 'marca' => 'Zitromax', 'categoria' => 'Antibióticos'],
        ['principio_activo' => 'Ciprofloxacino', 'marca' => 'Ciproflox', 'categoria' => 'Antibióticos'],
        ['principio_activo' => 'Claritromicina', 'marca' => 'Klaricid', 'categoria' => 'Antibióticos'],
        ['principio_activo' => 'Cefalexina', 'marca' => 'Keflex', 'categoria' => 'Antibióticos'],
        ['principio_activo' => 'Metronidazol', 'marca' => 'Flagyl', 'categoria' => 'Antibióticos'],

        ['principio_activo' => 'Dexametasona', 'marca' => 'Decadron', 'categoria' => 'Antiinflamatorios'],
        ['principio_activo' => 'Prednisona', 'marca' => 'Prednisona', 'categoria' => 'Antiinflamatorios'],
        ['principio_activo' => 'Ketorolaco', 'marca' => 'Ketorolac', 'categoria' => 'Antiinflamatorios'],
        ['principio_activo' => 'Piroxicam', 'marca' => 'Feldene', 'categoria' => 'Antiinflamatorios'],
        ['principio_activo' => 'Meloxicam', 'marca' => 'Mobic', 'categoria' => 'Antiinflamatorios'],
        ['principio_activo' => 'Indometacina', 'marca' => 'Indocid', 'categoria' => 'Antiinflamatorios'],

        ['principio_activo' => 'Losartán potásico', 'marca' => 'Cozaar', 'categoria' => 'Antihipertensivos'],
        ['principio_activo' => 'Enalapril maleato', 'marca' => 'Renitec', 'categoria' => 'Antihipertensivos'],
        ['principio_activo' => 'Amlodipino besilato', 'marca' => 'Norvasc', 'categoria' => 'Antihipertensivos'],
        ['principio_activo' => 'Valsartán', 'marca' => 'Diovan', 'categoria' => 'Antihipertensivos'],
        ['principio_activo' => 'Atenolol', 'marca' => 'Tenormin', 'categoria' => 'Antihipertensivos'],
        ['principio_activo' => 'Hidroclorotiazida', 'marca' => 'Hidroclor', 'categoria' => 'Antihipertensivos'],

        ['principio_activo' => 'Ácido ascórbico', 'marca' => 'Vitamina C', 'categoria' => 'Vitaminas y Suplementos'],
        ['principio_activo' => 'Complejo vitamínico B', 'marca' => 'Complejo B', 'categoria' => 'Vitaminas y Suplementos'],
        ['principio_activo' => 'Vitamina D3', 'marca' => 'Vitamina D', 'categoria' => 'Vitaminas y Suplementos'],
        ['principio_activo' => 'Carbonato de calcio', 'marca' => 'Caltrate', 'categoria' => 'Vitaminas y Suplementos'],
        ['principio_activo' => 'Hierro', 'marca' => 'Ferrum', 'categoria' => 'Vitaminas y Suplementos'],
        ['principio_activo' => 'Omega 3', 'marca' => 'Omega 3', 'categoria' => 'Vitaminas y Suplementos'],

        ['principio_activo' => 'Loratadina', 'marca' => 'Claritin', 'categoria' => 'Antihistamínicos'],
        ['principio_activo' => 'Cetirizina', 'marca' => 'Zyrtec', 'categoria' => 'Antihistamínicos'],
        ['principio_activo' => 'Desloratadina', 'marca' => 'Deslorat', 'categoria' => 'Antihistamínicos'],
        ['principio_activo' => 'Fexofenadina', 'marca' => 'Allegra', 'categoria' => 'Antihistamínicos'],
        ['principio_activo' => 'Clorfeniramina', 'marca' => 'Clorfen', 'categoria' => 'Antihistamínicos'],
        ['principio_activo' => 'Bilastina', 'marca' => 'Bilastina', 'categoria' => 'Antihistamínicos'],

        ['principio_activo' => 'Metformina', 'marca' => 'Glucophage', 'categoria' => 'Antidiabéticos'],
        ['principio_activo' => 'Glibenclamida', 'marca' => 'Daonil', 'categoria' => 'Antidiabéticos'],
        ['principio_activo' => 'Sitagliptina', 'marca' => 'Januvia', 'categoria' => 'Antidiabéticos'],
        ['principio_activo' => 'Empagliflozina', 'marca' => 'Jardiance', 'categoria' => 'Antidiabéticos'],
        ['principio_activo' => 'Insulina glargina', 'marca' => 'Lantus', 'categoria' => 'Antidiabéticos'],
        ['principio_activo' => 'Pioglitazona', 'marca' => 'Actos', 'categoria' => 'Antidiabéticos'],

        ['principio_activo' => 'Omeprazol', 'marca' => 'Losec', 'categoria' => 'Gastrointestinales'],
        ['principio_activo' => 'Ranitidina', 'marca' => 'Zantac', 'categoria' => 'Gastrointestinales'],
        ['principio_activo' => 'Metoclopramida', 'marca' => 'Plasil', 'categoria' => 'Gastrointestinales'],
        ['principio_activo' => 'Loperamida', 'marca' => 'Imodium', 'categoria' => 'Gastrointestinales'],
        ['principio_activo' => 'Sucralfato', 'marca' => 'Sucralfato', 'categoria' => 'Gastrointestinales'],
        ['principio_activo' => 'Domperidona', 'marca' => 'Motilium', 'categoria' => 'Gastrointestinales'],

        ['principio_activo' => 'Clotrimazol', 'marca' => 'Canesten', 'categoria' => 'Dermatológicos'],
        ['principio_activo' => 'Hidrocortisona', 'marca' => 'Cortizol', 'categoria' => 'Dermatológicos'],
        ['principio_activo' => 'Mupirocina', 'marca' => 'Bactroban', 'categoria' => 'Dermatológicos'],
        ['principio_activo' => 'Ketoconazol', 'marca' => 'Ketoconazol', 'categoria' => 'Dermatológicos'],
        ['principio_activo' => 'Calamina', 'marca' => 'Caladryl', 'categoria' => 'Dermatológicos'],
        ['principio_activo' => 'Ácido fusídico', 'marca' => 'Fucidin', 'categoria' => 'Dermatológicos'],

        ['principio_activo' => 'Salbutamol', 'marca' => 'Ventolin', 'categoria' => 'Broncodilatadores'],
        ['principio_activo' => 'Budesonida', 'marca' => 'Pulmicort', 'categoria' => 'Broncodilatadores'],
        ['principio_activo' => 'Montelukast', 'marca' => 'Singulair', 'categoria' => 'Broncodilatadores'],
        ['principio_activo' => 'Formoterol', 'marca' => 'Foradil', 'categoria' => 'Broncodilatadores'],
        ['principio_activo' => 'Ipratropio', 'marca' => 'Atrovent', 'categoria' => 'Broncodilatadores'],
        ['principio_activo' => 'Ambroxol', 'marca' => 'Mucosolvan', 'categoria' => 'Broncodilatadores'],
    ],

    'presentaciones' => [
        'Analgésicos' => [
            'Tabletas 500 mg x 20',
            'Tabletas 1 g x 10',
            'Cápsulas 400 mg x 10',
            'Jarabe 120 mL',
            'Tabletas efervescentes x 12',
        ],
        'Antibióticos' => [
            'Cápsulas 500 mg x 21',
            'Tabletas 500 mg x 10',
            'Suspensión 250 mg/5 mL',
            'Tabletas 850 mg x 14',
            'Cápsulas 250 mg x 12',
        ],
        'Antiinflamatorios' => [
            'Tabletas 50 mg x 20',
            'Tabletas 4 mg x 10',
            'Cápsulas 20 mg x 14',
            'Gel tópico 1% x 30 g',
            'Tabletas 500 mg x 10',
        ],
        'Antihipertensivos' => [
            'Tabletas 50 mg x 30',
            'Tabletas 10 mg x 30',
            'Tabletas 5 mg x 30',
            'Tabletas 80 mg x 28',
            'Tabletas 25 mg x 20',
        ],
        'Vitaminas y Suplementos' => [
            'Tabletas x 30',
            'Cápsulas x 30',
            'Solución oral 120 mL',
            'Tabletas efervescentes x 10',
            'Sobres x 15',
        ],
        'Antihistamínicos' => [
            'Tabletas 10 mg x 10',
            'Jarabe 60 mL',
            'Tabletas 5 mg x 20',
            'Cápsulas 120 mg x 10',
            'Tabletas 20 mg x 10',
        ],
        'Antidiabéticos' => [
            'Tabletas 850 mg x 30',
            'Tabletas 5 mg x 30',
            'Tabletas 100 mg x 28',
            'Tabletas 10 mg x 30',
            'Solución inyectable x 5',
        ],
        'Gastrointestinales' => [
            'Cápsulas 20 mg x 14',
            'Tabletas 150 mg x 20',
            'Tabletas 10 mg x 20',
            'Cápsulas 2 mg x 12',
            'Suspensión 200 mg/5 mL',
        ],
        'Dermatológicos' => [
            'Crema 1% x 20 g',
            'Crema 1% x 15 g',
            'Ungüento 2% x 15 g',
            'Crema 2% x 30 g',
            'Loción 120 mL',
        ],
        'Broncodilatadores' => [
            'Inhalador 100 mcg x 200 dosis',
            'Suspensión para nebulizar x 20',
            'Tabletas 10 mg x 30',
            'Inhalador 12 mcg x 60 dosis',
            'Jarabe 15 mg/5 mL',
        ],
    ],

    'precios_base' => [
        'Analgésicos' => 9.50,
        'Antibióticos' => 24.00,
        'Antiinflamatorios' => 14.00,
        'Antihipertensivos' => 18.00,
        'Vitaminas y Suplementos' => 32.00,
        'Antihistamínicos' => 12.00,
        'Antidiabéticos' => 22.00,
        'Gastrointestinales' => 16.00,
        'Dermatológicos' => 15.00,
        'Broncodilatadores' => 28.00,
    ],
];
