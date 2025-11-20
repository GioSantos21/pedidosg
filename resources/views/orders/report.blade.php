<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Pedido #{{ $order->id }}</title>

    <style>
        /* Estilos generales para impresión (Dompdf compatible) */
        body {
            font-family: ui-sans-serif, system-ui, sans-serif;
            font-size: 9.5pt;
            color: #333;
            margin: 0;
            padding: 0;
        }

        .report-container {
            max-width: 800px;
            margin: 0 auto;
            /* Eliminamos el borde de Dompdf para una impresión más limpia */
            padding: 30px;
        }

        /* --- Estilo de la Cabecera de la Empresa --- */
        .header-section {
            border-bottom: 2px solid #333;
            padding-bottom: 45px;
            margin-bottom: 15px;
        }

        .header-left {
            float: left;
            width: 70%;
            color: #000;
        }

        .header-right {
            float: right;
            width: 25%;
            text-align: right;
        }

        /* --- Estilos de Tablas de Metadatos (Sección 1 y 2) --- */
        .data-table-layout {
            width: 100%;
            border-collapse: collapse;
            /* Quita los bordes de la tabla de layout */
            margin-bottom: 15px;
        }

        .data-table-layout td {
            padding: 2px 0;
            border: none;
            line-height: 1.3;
        }

        .data-table-layout strong {
            display: inline-block;
            min-width: 160px;
            /* Asegura la alineación de las etiquetas */
            font-weight: bold;
        }

        /* --- Estilos de la Tabla de Contenido (Sección 3) --- */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        .items-table th,
        .items-table td {
            border: 1px solid #000;
            padding: 6px 8px;
            text-align: left;
            font-size: 9pt;
        }

        .items-table th {
            background-color: #522d6d;
            color: #fff;
            border: 1px solid #fff;
            text-align: center;
            font-weight: bold;
        }

        .text-right {
            text-align: right !important;
        }

        .total-row td {
            border-top: 2px solid #000 !important;
            font-weight: bold;
            padding-top: 8px;
        }
    </style>
</head>

<body>

    <div class="report-container">

        <div class="header-section">
            <div class="header-left">
                <h1 style="font-size: 14pt; margin: 0;">INVERSIONES ANTHONYS S.A. DE C.V.</h1>
                <p style="margin-top: 5px;">Tipo de Pedido: **INSUMOS** (Ejemplo estático)</p>
            </div>
            <div class="header-right">
                @php
                    $logoPath = public_path('images/logo-letraA-fondoMorado.svg');
                @endphp
                @if (file_exists($logoPath))
                    <img src="{{ $logoPath }}" alt="Logo AN"
                        style="height: 40px; display: inline-block;">
                @else
                    <span style="font-size:12px;">Logo AN</span>
                @endif
            </div>
        </div>

        <table class="data-table-layout">
            <tr>
                <td style="width: 50%;">
                    <strong>Número de Pedido:</strong> #{{ $order->id }}
                </td>
                <td style="width: 50%;">
                    <strong>Fecha en que se tomó:</strong>
                    {{ $order->requested_at->translatedFormat('l, d \d\e F \d\e Y') }}
                </td>
            </tr>
            <tr>
                <td>
                    <strong>Usuario que realizó el pedido:</strong> {{ $order->user->name ?? 'N/A' }}
                </td>
                <td>
                    <strong>Hora en que se tomó:</strong> {{ $order->requested_at->format('H:i:s') }}
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <strong>Descripción:</strong> {{ $order->notes ?? 'N/A' }}
                </td>
            </tr>
        </table>

        <h2 style="font-size: 11pt; margin-top: 20px; border-bottom: 1px solid #ddd;">Datos de Pedido:
            #{{ $order->id }}</h2>
        <table class="data-table-layout">
            <tr>
                <td style="width: 50%;">
                    <strong>Procesado por Bodega:</strong> **{{ $order->status !== 'Pendiente' ? 'SI' : 'NO' }}**
                </td>
                <td style="width: 50%;">
                    <strong>Fecha en que se Confirmo:</strong>
                    {{ $order->completed_at ? $order->completed_at->format('d/m/Y H:i') : 'N/A' }}
                </td>
            </tr>
            <tr>
                <td>
                    <strong>Número de Envío:</strong> {{ $numero_envio }}
                </td>
                <td>
                    <strong>Usuario que confirmó el pedido:</strong> {{ $usuario_confirmacion }}
                </td>
            </tr>
        </table>

        <h2 style="font-size: 11pt; margin-top: 30px;">Productos Solicitados</h2>

        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 30px;">#</th>
                    <th style="width: 15%;">CÓDIGO</th>
                    <th style="width: 45%;">DESCRIPCIÓN DE PRODUCTO</th>
                    <th style="width: 10%;">INVENTARIO</th>
                    <th style="width: 15%;">CANTIDAD SOLICITADA</th>
                    <th style="width: 15%;">CANTIDAD FACTURADA</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $total_solicitada = 0;
                    $total_facturada = 0;
                    // El total de inventario se deja en 0.00, ya que es stock en tiempo real y no histórico del pedido.
                @endphp
                @foreach ($order->orderItems as $index => $item)
                    @php
                        $cantidad_facturada = 0;
                        $total_solicitada += $item->quantity;
                        $total_facturada += $cantidad_facturada;
                    @endphp
                    <tr>
                        <td style="text-align: center;">{{ $index + 1 }}</td>
                        <td>{{ $item->product->product_code ?? 'N/A' }}</td>
                        <td>{{ $item->product->name }} ({{ $item->product->unit }})</td>
                        <td class="text-right">0.00</td>
                        <td class="text-right">{{ number_format($item->quantity, 2) }}</td>
                        <td class="text-right">{{ number_format($cantidad_facturada, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="total-row">
                    <td colspan="3"
                        style="text-align: right; border-left: none !important; border-bottom: none !important; padding-right: 15px; font-size: 10pt;">
                        **TOTAL:**
                    </td>
                    <td class="text-right" style="padding-top: 10px;">
                        **0.00**
                    </td>
                    <td class="text-right" style="padding-top: 10px;">
                        **{{ number_format($total_solicitada, 2) }}**
                    </td>
                    <td class="text-right" style="padding-top: 10px;">
                        **{{ number_format($total_facturada, 2) }}**
                    </td>
                </tr>
            </tfoot>
        </table>

        <div style="margin-top: 30px; font-size: 10pt;">
            <p><strong>Cantidad de Items Solicitados:</strong> {{ $order->orderItems->count() }} productos
                seleccionados</p>
            <p><strong>Cantidad de Items Facturados:</strong> {{ 0 }} (Ejemplo estático)</p>
        </div>

    </div>

</body>

</html>
