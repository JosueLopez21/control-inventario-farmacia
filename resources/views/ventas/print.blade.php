<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Venta #{{ $venta->id }} · FarmaSalud</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: "Segoe UI", Arial, sans-serif; color: #111; margin: 24px; font-size: 13px; }
        .encabezado { display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 2px solid #0f766e; padding-bottom: 12px; margin-bottom: 18px; }
        .marca { font-size: 22px; font-weight: 700; color: #0f766e; }
        .subtitulo { color: #555; font-size: 12px; }
        .caja { border: 1px solid #ccc; border-radius: 8px; padding: 10px 14px; }
        table { width: 100%; border-collapse: collapse; margin-top: 16px; }
        th, td { border-bottom: 1px solid #ddd; padding: 8px 6px; text-align: left; }
        th { background: #f2f6f7; font-size: 11px; text-transform: uppercase; letter-spacing: .4px; color: #47615e; }
        .text-end { text-align: right; }
        .text-center { text-align: center; }
        .total { font-size: 16px; font-weight: 700; color: #0f766e; }
        .pie { margin-top: 26px; display: flex; justify-content: space-between; color: #666; font-size: 11px; }
        .firma { margin-top: 48px; border-top: 1px solid #999; width: 220px; text-align: center; padding-top: 6px; }
        @media print {
            body { margin: 8mm; }
            .no-print { display: none !important; }
            @page { margin: 8mm; }
        }
    </style>
</head>
<body>
    <div class="no-print" style="margin-bottom: 14px; text-align: right;">
        <button onclick="window.print()" style="background:#0f766e;color:#fff;border:0;padding:8px 16px;border-radius:8px;cursor:pointer;">Imprimir</button>
    </div>

    <div class="encabezado">
        <div>
            <div class="marca">FarmaSalud</div>
            <div class="subtitulo">Control de inventario de farmacia</div>
            <div class="subtitulo">Tarija · Bolivia</div>
        </div>
        <div class="caja text-end">
            <div><strong>Comprobante de venta</strong></div>
            <div>N.º <strong>#{{ str_pad((string) $venta->id, 6, '0', STR_PAD_LEFT) }}</strong></div>
            <div>Fecha: {{ formato_fecha_hora($venta->fecha_venta) }}</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Medicamento</th>
                <th>Lote</th>
                <th>Vence</th>
                <th class="text-center">Cant.</th>
                <th class="text-end">P. unit.</th>
                <th class="text-end">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($venta->detalles as $detalle)
                <tr>
                    <td>{{ $detalle->lote?->medicamento?->nombre_comercial ?? '—' }}</td>
                    <td>{{ $detalle->lote?->numero_lote ?? '—' }}</td>
                    <td>{{ $detalle->lote ? formato_fecha($detalle->lote->fecha_vencimiento) : '—' }}</td>
                    <td class="text-center">{{ $detalle->cantidad }}</td>
                    <td class="text-end">{{ formato_bs($detalle->precio_unitario) }}</td>
                    <td class="text-end">{{ formato_bs($detalle->subtotal) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div style="display:flex; justify-content:flex-end; margin-top:14px;">
        <div class="text-end">
            <div class="total">Total: {{ formato_bs($venta->monto_total) }}</div>
            <div>Método de pago: <strong>{{ $venta->metodo_pago_etiqueta }}</strong></div>
            @if ($venta->metodo_pago === 'efectivo')
                <div>Recibido: {{ formato_bs($venta->monto_recibido ?? $venta->monto_total) }}</div>
                <div>Cambio: <strong>{{ formato_bs($venta->cambio ?? 0) }}</strong></div>
            @endif
        </div>
    </div>

    <div class="pie">
        <div>Gracias por su compra.</div>
        <div>Atendido por: {{ auth()->user()->name }}</div>
    </div>

    <div class="firma">Firma / Sello</div>
</body>
</html>
