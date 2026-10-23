<?php

/**
 * Catálogo base de la farmacia.
 *
 * Cada principio activo declara cinco presentaciones coherentes. Los productos
 * de venta libre (otc) se combinan con cuatro laboratorios; el resto, con tres.
 * El seeder genera exactamente 1000 medicamentos con esa distribución.
 */
return [
    'medicamentos' => [
        // Analgésicos y antiinflamatorios de venta libre.
        ['principio_activo' => 'Paracetamol', 'marca' => 'Panadol', 'categoria' => 'Analgésicos', 'otc' => true, 'formas' => [
            'Tabletas 500 mg x 20',
            'Tabletas 1 g x 10',
            'Jarabe 120 mg/5 mL x 120 mL',
            'Gotas 100 mg/mL x 20 mL',
            'Supositorios 300 mg x 6',
        ]],
        ['principio_activo' => 'Ibuprofeno', 'marca' => 'Advil', 'categoria' => 'Analgésicos', 'otc' => true, 'formas' => [
            'Tabletas 400 mg x 20',
            'Tabletas 600 mg x 20',
            'Suspensión 100 mg/5 mL x 120 mL',
            'Cápsulas blandas 200 mg x 20',
            'Gel tópico 5% x 40 g',
        ]],
        ['principio_activo' => 'Naproxeno sódico', 'marca' => 'Apronax', 'categoria' => 'Analgésicos', 'otc' => true, 'formas' => [
            'Tabletas 250 mg x 20',
            'Tabletas 500 mg x 20',
            'Tabletas recubiertas 550 mg x 14',
            'Suspensión 125 mg/5 mL x 120 mL',
            'Gel tópico 10% x 30 g',
        ]],
        ['principio_activo' => 'Diclofenaco sódico', 'marca' => 'Voltaren', 'categoria' => 'Analgésicos', 'otc' => true, 'formas' => [
            'Tabletas 50 mg x 20',
            'Tabletas retard 100 mg x 20',
            'Gel tópico 1% x 30 g',
            'Gotas oftálmicas 0.1% x 10 mL',
            'Supositorios 100 mg x 10',
        ]],
        ['principio_activo' => 'Ácido acetilsalicílico', 'marca' => 'Aspirina', 'categoria' => 'Analgésicos', 'otc' => false, 'formas' => [
            'Tabletas 500 mg x 20',
            'Tabletas 100 mg x 30',
            'Tabletas efervescentes 500 mg x 20',
            'Tabletas recubiertas 325 mg x 24',
            'Polvo soluble 500 mg x 10',
        ]],
        ['principio_activo' => 'Tramadol', 'marca' => 'Tramal', 'categoria' => 'Analgésicos', 'otc' => false, 'formas' => [
            'Cápsulas 50 mg x 20',
            'Tabletas de liberación prolongada 100 mg x 20',
            'Solución inyectable 50 mg/mL x 5 ampollas',
            'Gotas 100 mg/mL x 10 mL',
            'Tabletas 37.5 mg x 20',
        ]],

        // Antibióticos: siempre requieren receta.
        ['principio_activo' => 'Amoxicilina', 'marca' => 'Amoxil', 'categoria' => 'Antibióticos', 'otc' => false, 'formas' => [
            'Cápsulas 500 mg x 21',
            'Suspensión 250 mg/5 mL x 100 mL',
            'Suspensión 500 mg/5 mL x 100 mL',
            'Tabletas 875 mg x 14',
            'Cápsulas 250 mg x 20',
        ]],
        ['principio_activo' => 'Azitromicina', 'marca' => 'Zitromax', 'categoria' => 'Antibióticos', 'otc' => false, 'formas' => [
            'Tabletas 500 mg x 3',
            'Suspensión 200 mg/5 mL x 30 mL',
            'Tabletas 250 mg x 6',
            'Suspensión 100 mg/5 mL x 30 mL',
            'Tabletas 600 mg x 4',
        ]],
        ['principio_activo' => 'Ciprofloxacino', 'marca' => 'Ciproflox', 'categoria' => 'Antibióticos', 'otc' => false, 'formas' => [
            'Tabletas 500 mg x 10',
            'Tabletas 750 mg x 10',
            'Solución inyectable 200 mg/100 mL x 1 vial',
            'Gotas óticas 0.3% x 10 mL',
            'Tabletas 250 mg x 12',
        ]],
        ['principio_activo' => 'Claritromicina', 'marca' => 'Klaricid', 'categoria' => 'Antibióticos', 'otc' => false, 'formas' => [
            'Tabletas 500 mg x 14',
            'Suspensión 250 mg/5 mL x 60 mL',
            'Tabletas 250 mg x 14',
            'Suspensión 125 mg/5 mL x 60 mL',
            'Tabletas de liberación prolongada 500 mg x 7',
        ]],
        ['principio_activo' => 'Cefalexina', 'marca' => 'Keflex', 'categoria' => 'Antibióticos', 'otc' => false, 'formas' => [
            'Cápsulas 500 mg x 20',
            'Suspensión 250 mg/5 mL x 100 mL',
            'Cápsulas 250 mg x 20',
            'Tabletas 500 mg x 12',
            'Suspensión 125 mg/5 mL x 100 mL',
        ]],
        ['principio_activo' => 'Metronidazol', 'marca' => 'Flagyl', 'categoria' => 'Antibióticos', 'otc' => false, 'formas' => [
            'Tabletas 500 mg x 20',
            'Suspensión 125 mg/5 mL x 120 mL',
            'Óvulos vaginales 500 mg x 10',
            'Solución inyectable 500 mg/100 mL x 1 vial',
            'Crema vaginal 10% x 30 g',
        ]],

        // Antiinflamatorios con receta.
        ['principio_activo' => 'Dexametasona', 'marca' => 'Decadron', 'categoria' => 'Antiinflamatorios', 'otc' => false, 'formas' => [
            'Tabletas 4 mg x 10',
            'Solución inyectable 4 mg/mL x 5 ampollas',
            'Jarabe 0.5 mg/5 mL x 120 mL',
            'Gotas oftálmicas 0.1% x 10 mL',
            'Crema 0.1% x 30 g',
        ]],
        ['principio_activo' => 'Prednisona', 'marca' => 'Prednisona', 'categoria' => 'Antiinflamatorios', 'otc' => false, 'formas' => [
            'Tabletas 20 mg x 20',
            'Tabletas 5 mg x 20',
            'Tabletas 50 mg x 10',
            'Tabletas 10 mg x 20',
            'Tabletas 40 mg x 10',
        ]],
        ['principio_activo' => 'Ketorolaco', 'marca' => 'Ketorolac', 'categoria' => 'Antiinflamatorios', 'otc' => false, 'formas' => [
            'Tabletas 10 mg x 20',
            'Solución inyectable 30 mg/mL x 5 ampollas',
            'Gotas oftálmicas 0.5% x 5 mL',
            'Tabletas 20 mg x 10',
            'Solución inyectable 60 mg/2 mL x 5 ampollas',
        ]],
        ['principio_activo' => 'Piroxicam', 'marca' => 'Feldene', 'categoria' => 'Antiinflamatorios', 'otc' => false, 'formas' => [
            'Cápsulas 20 mg x 20',
            'Tabletas 40 mg x 10',
            'Gel tópico 0.5% x 30 g',
            'Cápsulas 10 mg x 20',
            'Solución inyectable 40 mg/2 mL x 3 ampollas',
        ]],

        // Antihipertensivos con receta.
        ['principio_activo' => 'Losartán potásico', 'marca' => 'Cozaar', 'categoria' => 'Antihipertensivos', 'otc' => false, 'formas' => [
            'Tabletas 50 mg x 30',
            'Tabletas 100 mg x 30',
            'Tabletas recubiertas 25 mg x 30',
            'Tabletas 50 mg x 60',
            'Tabletas 100 mg x 60',
        ]],
        ['principio_activo' => 'Enalapril maleato', 'marca' => 'Renitec', 'categoria' => 'Antihipertensivos', 'otc' => false, 'formas' => [
            'Tabletas 10 mg x 30',
            'Tabletas 20 mg x 30',
            'Tabletas 5 mg x 30',
            'Tabletas 10 mg x 60',
            'Tabletas 20 mg x 60',
        ]],
        ['principio_activo' => 'Amlodipino besilato', 'marca' => 'Norvasc', 'categoria' => 'Antihipertensivos', 'otc' => false, 'formas' => [
            'Tabletas 5 mg x 30',
            'Tabletas 10 mg x 30',
            'Tabletas 2.5 mg x 30',
            'Tabletas 5 mg x 60',
            'Tabletas 10 mg x 60',
        ]],
        ['principio_activo' => 'Valsartán', 'marca' => 'Diovan', 'categoria' => 'Antihipertensivos', 'otc' => false, 'formas' => [
            'Tabletas 80 mg x 30',
            'Tabletas 160 mg x 30',
            'Tabletas 320 mg x 30',
            'Tabletas 80 mg x 60',
            'Tabletas 160 mg x 60',
        ]],
        ['principio_activo' => 'Atenolol', 'marca' => 'Tenormin', 'categoria' => 'Antihipertensivos', 'otc' => false, 'formas' => [
            'Tabletas 50 mg x 30',
            'Tabletas 100 mg x 30',
            'Tabletas 25 mg x 30',
            'Tabletas 50 mg x 60',
            'Tabletas 100 mg x 60',
        ]],
        ['principio_activo' => 'Hidroclorotiazida', 'marca' => 'Hidroclor', 'categoria' => 'Antihipertensivos', 'otc' => false, 'formas' => [
            'Tabletas 25 mg x 30',
            'Tabletas 50 mg x 30',
            'Tabletas 12.5 mg x 30',
            'Tabletas 25 mg x 60',
            'Tabletas 50 mg x 60',
        ]],

        // Vitaminas y suplementos.
        ['principio_activo' => 'Ácido ascórbico', 'marca' => 'Vitamina C', 'categoria' => 'Vitaminas y Suplementos', 'otc' => true, 'formas' => [
            'Tabletas 500 mg x 30',
            'Tabletas efervescentes 1 g x 10',
            'Cápsulas 500 mg x 60',
            'Solución oral 100 mg/mL x 120 mL',
            'Tabletas masticables 250 mg x 60',
        ]],
        ['principio_activo' => 'Complejo vitamínico B', 'marca' => 'Complejo B', 'categoria' => 'Vitaminas y Suplementos', 'otc' => true, 'formas' => [
            'Tabletas x 30',
            'Cápsulas x 30',
            'Solución inyectable x 3 ampollas',
            'Jarabe x 120 mL',
            'Tabletas x 60',
        ]],
        ['principio_activo' => 'Vitamina D3', 'marca' => 'Vitamina D', 'categoria' => 'Vitaminas y Suplementos', 'otc' => true, 'formas' => [
            'Cápsulas blandas 1000 UI x 30',
            'Gotas 1000 UI/mL x 10 mL',
            'Tabletas 2000 UI x 30',
            'Cápsulas blandas 400 UI x 60',
            'Solución oral 5000 UI/mL x 10 mL',
        ]],
        ['principio_activo' => 'Carbonato de calcio', 'marca' => 'Caltrate', 'categoria' => 'Vitaminas y Suplementos', 'otc' => false, 'formas' => [
            'Tabletas 600 mg x 30',
            'Tabletas masticables 500 mg x 60',
            'Tabletas efervescentes 1 g x 20',
            'Cápsulas 500 mg x 30',
            'Polvo oral 1 g x 30 sobres',
        ]],
        ['principio_activo' => 'Omega 3', 'marca' => 'Omega 3', 'categoria' => 'Vitaminas y Suplementos', 'otc' => false, 'formas' => [
            'Cápsulas blandas 1000 mg x 30',
            'Cápsulas blandas 500 mg x 60',
            'Cápsulas blandas 1000 mg x 60',
            'Solución oral 500 mg/mL x 120 mL',
            'Cápsulas blandas 2000 mg x 30',
        ]],
        ['principio_activo' => 'Zinc', 'marca' => 'Zinc', 'categoria' => 'Vitaminas y Suplementos', 'otc' => true, 'formas' => [
            'Tabletas 10 mg x 30',
            'Tabletas 20 mg x 30',
            'Jarabe 5 mg/5 mL x 120 mL',
            'Cápsulas 15 mg x 60',
            'Tabletas efervescentes 10 mg x 20',
        ]],
        ['principio_activo' => 'Magnesio', 'marca' => 'Magnesio', 'categoria' => 'Vitaminas y Suplementos', 'otc' => true, 'formas' => [
            'Tabletas 250 mg x 30',
            'Cápsulas 400 mg x 30',
            'Polvo oral 300 mg x 30 sobres',
            'Tabletas efervescentes 250 mg x 20',
            'Solución oral 100 mg/mL x 120 mL',
        ]],
        ['principio_activo' => 'Hierro', 'marca' => 'Ferrum', 'categoria' => 'Vitaminas y Suplementos', 'otc' => false, 'formas' => [
            'Tabletas 100 mg x 30',
            'Cápsulas 200 mg x 30',
            'Jarabe 50 mg/5 mL x 120 mL',
            'Tabletas masticables 100 mg x 30',
            'Solución inyectable 100 mg/2 mL x 5 ampollas',
        ]],

        // Antihistamínicos.
        ['principio_activo' => 'Loratadina', 'marca' => 'Claritin', 'categoria' => 'Antihistamínicos', 'otc' => true, 'formas' => [
            'Tabletas 10 mg x 10',
            'Jarabe 5 mg/5 mL x 60 mL',
            'Tabletas 10 mg x 30',
            'Tabletas masticables 5 mg x 10',
            'Solución oral 5 mg/5 mL x 120 mL',
        ]],
        ['principio_activo' => 'Cetirizina', 'marca' => 'Zyrtec', 'categoria' => 'Antihistamínicos', 'otc' => true, 'formas' => [
            'Tabletas 10 mg x 10',
            'Jarabe 5 mg/5 mL x 60 mL',
            'Tabletas 10 mg x 30',
            'Gotas 10 mg/mL x 10 mL',
            'Tabletas masticables 10 mg x 10',
        ]],
        ['principio_activo' => 'Clorfeniramina', 'marca' => 'Clorfen', 'categoria' => 'Antihistamínicos', 'otc' => false, 'formas' => [
            'Tabletas 4 mg x 20',
            'Jarabe 2 mg/5 mL x 120 mL',
            'Cápsulas de liberación prolongada 8 mg x 10',
            'Solución inyectable 10 mg/mL x 5 ampollas',
            'Tabletas 4 mg x 30',
        ]],
        ['principio_activo' => 'Bilastina', 'marca' => 'Bilastina', 'categoria' => 'Antihistamínicos', 'otc' => false, 'formas' => [
            'Tabletas 20 mg x 10',
            'Tabletas 20 mg x 30',
            'Tabletas 10 mg x 10',
            'Solución oral 2.5 mg/mL x 120 mL',
            'Tabletas masticables 20 mg x 10',
        ]],

        // Antidiabéticos con receta.
        ['principio_activo' => 'Metformina', 'marca' => 'Glucophage', 'categoria' => 'Antidiabéticos', 'otc' => false, 'formas' => [
            'Tabletas 850 mg x 30',
            'Tabletas 500 mg x 30',
            'Tabletas de liberación prolongada 1000 mg x 30',
            'Tabletas 1000 mg x 30',
            'Solución oral 500 mg/5 mL x 120 mL',
        ]],
        ['principio_activo' => 'Glibenclamida', 'marca' => 'Daonil', 'categoria' => 'Antidiabéticos', 'otc' => false, 'formas' => [
            'Tabletas 5 mg x 30',
            'Tabletas 2.5 mg x 30',
            'Tabletas 5 mg x 60',
            'Tabletas micronizadas 5 mg x 30',
            'Tabletas 10 mg x 30',
        ]],
        ['principio_activo' => 'Sitagliptina', 'marca' => 'Januvia', 'categoria' => 'Antidiabéticos', 'otc' => false, 'formas' => [
            'Tabletas 100 mg x 28',
            'Tabletas 50 mg x 28',
            'Tabletas 25 mg x 28',
            'Tabletas 100 mg x 56',
            'Tabletas 50 mg x 56',
        ]],
        ['principio_activo' => 'Empagliflozina', 'marca' => 'Jardiance', 'categoria' => 'Antidiabéticos', 'otc' => false, 'formas' => [
            'Tabletas 10 mg x 30',
            'Tabletas 25 mg x 30',
            'Tabletas 10 mg x 60',
            'Tabletas 25 mg x 60',
            'Tabletas 12.5 mg x 30',
        ]],
        ['principio_activo' => 'Insulina glargina', 'marca' => 'Lantus', 'categoria' => 'Antidiabéticos', 'otc' => false, 'formas' => [
            'Solución inyectable 100 UI/mL x 10 mL',
            'Pluma precargada 100 UI/mL x 3 mL',
            'Solución inyectable 100 UI/mL x 5 viales',
            'Pluma precargada 300 UI/mL x 3 mL',
            'Solución inyectable 100 UI/mL x 1 vial',
        ]],
        ['principio_activo' => 'Pioglitazona', 'marca' => 'Actos', 'categoria' => 'Antidiabéticos', 'otc' => false, 'formas' => [
            'Tabletas 15 mg x 30',
            'Tabletas 30 mg x 30',
            'Tabletas 45 mg x 30',
            'Tabletas 15 mg x 60',
            'Tabletas 30 mg x 60',
        ]],

        // Gastrointestinales: varios de venta libre para el asesor.
        ['principio_activo' => 'Omeprazol', 'marca' => 'Losec', 'categoria' => 'Gastrointestinales', 'otc' => true, 'formas' => [
            'Cápsulas 20 mg x 14',
            'Cápsulas 20 mg x 30',
            'Cápsulas 40 mg x 14',
            'Tabletas gastrorresistentes 20 mg x 28',
            'Suspensión oral 2 mg/mL x 100 mL',
        ]],
        ['principio_activo' => 'Hidróxido de aluminio y magnesio', 'marca' => 'Maalox', 'categoria' => 'Gastrointestinales', 'otc' => true, 'formas' => [
            'Suspensión oral x 180 mL',
            'Tabletas masticables x 20',
            'Suspensión oral x 360 mL',
            'Tabletas masticables x 40',
            'Polvo oral x 30 sobres',
        ]],
        ['principio_activo' => 'Loperamida', 'marca' => 'Imodium', 'categoria' => 'Gastrointestinales', 'otc' => true, 'formas' => [
            'Cápsulas 2 mg x 12',
            'Tabletas 2 mg x 20',
            'Solución oral 1 mg/5 mL x 60 mL',
            'Cápsulas 2 mg x 24',
            'Tabletas 2 mg x 12',
        ]],
        ['principio_activo' => 'Sales de rehidratación oral', 'marca' => 'Suero Oral', 'categoria' => 'Gastrointestinales', 'otc' => true, 'formas' => [
            'Polvo oral x 1 sobre',
            'Polvo oral x 10 sobres',
            'Solución oral x 500 mL',
            'Solución oral x 1 L',
            'Polvo oral x 30 sobres',
        ]],
        ['principio_activo' => 'Butilbromuro de hioscina', 'marca' => 'Buscapina', 'categoria' => 'Gastrointestinales', 'otc' => true, 'formas' => [
            'Tabletas 10 mg x 20',
            'Solución inyectable 20 mg/mL x 5 ampollas',
            'Gotas 10 mg/mL x 20 mL',
            'Tabletas 10 mg x 40',
            'Supositorios 10 mg x 6',
        ]],
        ['principio_activo' => 'Metoclopramida', 'marca' => 'Plasil', 'categoria' => 'Gastrointestinales', 'otc' => false, 'formas' => [
            'Tabletas 10 mg x 20',
            'Jarabe 5 mg/5 mL x 120 mL',
            'Solución inyectable 10 mg/2 mL x 5 ampollas',
            'Gotas 10 mg/mL x 20 mL',
            'Supositorios 10 mg x 6',
        ]],
        ['principio_activo' => 'Sucralfato', 'marca' => 'Sucralfato', 'categoria' => 'Gastrointestinales', 'otc' => false, 'formas' => [
            'Tabletas 1 g x 20',
            'Suspensión oral 1 g/5 mL x 200 mL',
            'Sobres 1 g x 30',
            'Tabletas 500 mg x 30',
            'Suspensión oral 2 g/10 mL x 200 mL',
        ]],
        ['principio_activo' => 'Ranitidina', 'marca' => 'Zantac', 'categoria' => 'Gastrointestinales', 'otc' => false, 'formas' => [
            'Tabletas 150 mg x 20',
            'Tabletas 300 mg x 10',
            'Jarabe 15 mg/mL x 120 mL',
            'Solución inyectable 25 mg/mL x 5 ampollas',
            'Tabletas 150 mg x 40',
        ]],
        ['principio_activo' => 'Domperidona', 'marca' => 'Motilium', 'categoria' => 'Gastrointestinales', 'otc' => false, 'formas' => [
            'Tabletas 10 mg x 20',
            'Suspensión oral 1 mg/mL x 100 mL',
            'Tabletas 10 mg x 30',
            'Supositorios 30 mg x 6',
            'Tabletas masticables 10 mg x 20',
        ]],

        // Dermatológicos.
        ['principio_activo' => 'Clotrimazol', 'marca' => 'Canesten', 'categoria' => 'Dermatológicos', 'otc' => true, 'formas' => [
            'Crema 1% x 20 g',
            'Crema 1% x 30 g',
            'Óvulos vaginales 500 mg x 6',
            'Solución tópica 1% x 30 mL',
            'Polvo tópico 1% x 30 g',
        ]],
        ['principio_activo' => 'Hidrocortisona', 'marca' => 'Cortizol', 'categoria' => 'Dermatológicos', 'otc' => true, 'formas' => [
            'Crema 1% x 15 g',
            'Crema 1% x 30 g',
            'Pomada 1% x 20 g',
            'Loción 1% x 60 mL',
            'Crema 0.5% x 30 g',
        ]],
        ['principio_activo' => 'Ketoconazol', 'marca' => 'Ketoconazol', 'categoria' => 'Dermatológicos', 'otc' => false, 'formas' => [
            'Crema 2% x 30 g',
            'Champú 2% x 120 mL',
            'Tabletas 200 mg x 10',
            'Óvulos vaginales 400 mg x 5',
            'Gel tópico 2% x 30 g',
        ]],
        ['principio_activo' => 'Ácido fusídico', 'marca' => 'Fucidin', 'categoria' => 'Dermatológicos', 'otc' => false, 'formas' => [
            'Crema 2% x 15 g',
            'Pomada 2% x 15 g',
            'Crema 2% x 30 g',
            'Solución tópica 2% x 30 mL',
            'Crema 2% x 20 g',
        ]],

        // Broncodilatadores y respiratorios.
        ['principio_activo' => 'Salbutamol', 'marca' => 'Ventolin', 'categoria' => 'Broncodilatadores', 'otc' => false, 'formas' => [
            'Inhalador 100 mcg x 200 dosis',
            'Solución para nebulizar 5 mg/mL x 20 mL',
            'Jarabe 2 mg/5 mL x 120 mL',
            'Tabletas 4 mg x 30',
            'Inhalador 100 mcg x 100 dosis',
        ]],
        ['principio_activo' => 'Budesonida', 'marca' => 'Pulmicort', 'categoria' => 'Broncodilatadores', 'otc' => false, 'formas' => [
            'Suspensión para nebulizar 0.5 mg/2 mL x 20',
            'Suspensión para nebulizar 1 mg/2 mL x 20',
            'Inhalador 200 mcg x 200 dosis',
            'Polvo para inhalación 200 mcg x 120 dosis',
            'Solución nasal 100 mcg x 150 dosis',
        ]],
        ['principio_activo' => 'Montelukast', 'marca' => 'Singulair', 'categoria' => 'Broncodilatadores', 'otc' => false, 'formas' => [
            'Tabletas 10 mg x 30',
            'Tabletas masticables 5 mg x 30',
            'Tabletas masticables 4 mg x 30',
            'Tabletas 10 mg x 60',
            'Gránulos 4 mg x 30 sobres',
        ]],
        ['principio_activo' => 'Formoterol', 'marca' => 'Foradil', 'categoria' => 'Broncodilatadores', 'otc' => false, 'formas' => [
            'Cápsulas para inhalación 12 mcg x 60',
            'Inhalador 12 mcg x 60 dosis',
            'Polvo para inhalación 12 mcg x 30 dosis',
            'Cápsulas para inhalación 12 mcg x 30',
            'Solución para nebulizar 20 mcg/2 mL x 20',
        ]],
        ['principio_activo' => 'Ipratropio', 'marca' => 'Atrovent', 'categoria' => 'Broncodilatadores', 'otc' => false, 'formas' => [
            'Inhalador 20 mcg x 200 dosis',
            'Solución para nebulizar 250 mcg/mL x 20 mL',
            'Solución nasal 0.03% x 15 mL',
            'Solución para nebulizar 500 mcg/2 mL x 20',
            'Inhalador 40 mcg x 200 dosis',
        ]],
        ['principio_activo' => 'Ambroxol', 'marca' => 'Mucosolvan', 'categoria' => 'Broncodilatadores', 'otc' => true, 'formas' => [
            'Jarabe 15 mg/5 mL x 120 mL',
            'Jarabe 30 mg/5 mL x 120 mL',
            'Cápsulas 75 mg x 20',
            'Solución oral 30 mg/5 mL x 100 mL',
            'Sobres 60 mg x 20',
        ]],
        ['principio_activo' => 'Dextrometorfano', 'marca' => 'Romilar', 'categoria' => 'Broncodilatadores', 'otc' => true, 'formas' => [
            'Jarabe 15 mg/5 mL x 120 mL',
            'Jarabe 30 mg/5 mL x 120 mL',
            'Gotas 30 mg/mL x 20 mL',
            'Cápsulas 15 mg x 20',
            'Solución oral 15 mg/5 mL x 100 mL',
        ]],
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
