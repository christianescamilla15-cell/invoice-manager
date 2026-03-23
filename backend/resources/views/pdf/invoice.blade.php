<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Factura {{ $invoice->invoice_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 12px;
            color: #333;
            line-height: 1.5;
        }
        .container {
            padding: 30px 40px;
        }
        .header {
            display: table;
            width: 100%;
            margin-bottom: 30px;
            border-bottom: 3px solid #2563EB;
            padding-bottom: 20px;
        }
        .header-left {
            display: table-cell;
            width: 60%;
            vertical-align: top;
        }
        .header-right {
            display: table-cell;
            width: 40%;
            vertical-align: top;
            text-align: right;
        }
        .company-name {
            font-size: 22px;
            font-weight: bold;
            color: #2563EB;
            margin-bottom: 5px;
        }
        .invoice-title {
            font-size: 28px;
            font-weight: bold;
            color: #1E40AF;
        }
        .invoice-number {
            font-size: 14px;
            color: #6B7280;
            margin-top: 5px;
        }
        .info-section {
            display: table;
            width: 100%;
            margin-bottom: 25px;
        }
        .info-block {
            display: table-cell;
            width: 50%;
            vertical-align: top;
        }
        .info-block h3 {
            font-size: 11px;
            text-transform: uppercase;
            color: #6B7280;
            letter-spacing: 1px;
            margin-bottom: 8px;
            border-bottom: 1px solid #E5E7EB;
            padding-bottom: 4px;
        }
        .info-block p {
            margin-bottom: 2px;
        }
        .info-block .label {
            color: #6B7280;
            font-size: 10px;
        }
        .status-badge {
            display: inline-block;
            padding: 3px 12px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: bold;
            color: #fff;
            margin-top: 5px;
        }
        table.items {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
        }
        table.items thead th {
            background-color: #2563EB;
            color: #fff;
            padding: 10px 12px;
            text-align: left;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        table.items thead th:last-child,
        table.items thead th:nth-child(3),
        table.items thead th:nth-child(4) {
            text-align: right;
        }
        table.items tbody td {
            padding: 10px 12px;
            border-bottom: 1px solid #E5E7EB;
        }
        table.items tbody td:last-child,
        table.items tbody td:nth-child(3),
        table.items tbody td:nth-child(4) {
            text-align: right;
        }
        table.items tbody tr:nth-child(even) {
            background-color: #F9FAFB;
        }
        .totals-section {
            display: table;
            width: 100%;
            margin-bottom: 25px;
        }
        .totals-notes {
            display: table-cell;
            width: 55%;
            vertical-align: top;
        }
        .totals-table {
            display: table-cell;
            width: 45%;
            vertical-align: top;
        }
        .totals-table table {
            width: 100%;
            border-collapse: collapse;
        }
        .totals-table td {
            padding: 6px 12px;
        }
        .totals-table td:last-child {
            text-align: right;
            font-weight: 500;
        }
        .totals-table tr.total-row {
            border-top: 2px solid #2563EB;
            font-size: 16px;
            font-weight: bold;
            color: #1E40AF;
        }
        .totals-table tr.total-row td {
            padding-top: 10px;
        }
        .notes-box {
            background-color: #F9FAFB;
            border: 1px solid #E5E7EB;
            border-radius: 6px;
            padding: 12px;
            margin-right: 20px;
        }
        .notes-box h4 {
            font-size: 11px;
            text-transform: uppercase;
            color: #6B7280;
            margin-bottom: 5px;
        }
        .footer {
            text-align: center;
            border-top: 1px solid #E5E7EB;
            padding-top: 15px;
            color: #9CA3AF;
            font-size: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="header-left">
                <div class="company-name">{{ $invoice->user->name ?? 'Invoice Manager' }}</div>
                <div style="color: #6B7280; font-size: 11px;">Sistema de Facturación</div>
            </div>
            <div class="header-right">
                <div class="invoice-title">FACTURA</div>
                <div class="invoice-number">{{ $invoice->invoice_number }}</div>
            </div>
        </div>

        <div class="info-section">
            <div class="info-block">
                <h3>Proveedor</h3>
                <p><strong>{{ $invoice->vendor->business_name }}</strong></p>
                <p><span class="label">RFC:</span> {{ $invoice->vendor->rfc }}</p>
                @if($invoice->vendor->contact_name)
                    <p><span class="label">Contacto:</span> {{ $invoice->vendor->contact_name }}</p>
                @endif
                @if($invoice->vendor->email)
                    <p><span class="label">Email:</span> {{ $invoice->vendor->email }}</p>
                @endif
                @if($invoice->vendor->phone)
                    <p><span class="label">Tel:</span> {{ $invoice->vendor->phone }}</p>
                @endif
                @if($invoice->vendor->address)
                    <p><span class="label">Dirección:</span> {{ $invoice->vendor->address }}</p>
                    <p>{{ $invoice->vendor->city }}, {{ $invoice->vendor->state }} {{ $invoice->vendor->zip_code }}</p>
                @endif
            </div>
            <div class="info-block" style="text-align: right;">
                <h3>Detalles de Factura</h3>
                <p><span class="label">Fecha de Emisión:</span> {{ $invoice->issued_at->format('d/m/Y') }}</p>
                <p><span class="label">Fecha de Vencimiento:</span> {{ $invoice->due_at->format('d/m/Y') }}</p>
                <p><span class="label">Tipo de Impuesto:</span>
                    @switch($invoice->tax_type)
                        @case('iva') IVA 16% @break
                        @case('iva_retention') IVA 16% + Ret. ISR @break
                        @case('exempt') Exento @break
                    @endswitch
                </p>
                @php
                    $statusColors = [
                        'draft' => '#6B7280',
                        'pending' => '#F59E0B',
                        'paid' => '#10B981',
                        'overdue' => '#EF4444',
                        'cancelled' => '#8B5CF6',
                    ];
                    $statusLabels = [
                        'draft' => 'Borrador',
                        'pending' => 'Pendiente',
                        'paid' => 'Pagada',
                        'overdue' => 'Vencida',
                        'cancelled' => 'Cancelada',
                    ];
                @endphp
                <span class="status-badge" style="background-color: {{ $statusColors[$invoice->status] ?? '#6B7280' }}">
                    {{ $statusLabels[$invoice->status] ?? $invoice->status }}
                </span>
            </div>
        </div>

        <table class="items">
            <thead>
                <tr>
                    <th style="width: 10%;">#</th>
                    <th style="width: 40%;">Descripción</th>
                    <th style="width: 15%;">Cantidad</th>
                    <th style="width: 17%;">Precio Unit.</th>
                    <th style="width: 18%;">Importe</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->items as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $item->description }}</td>
                    <td style="text-align: right;">{{ number_format($item->quantity, 2) }}</td>
                    <td style="text-align: right;">${{ number_format($item->unit_price, 2) }}</td>
                    <td style="text-align: right;">${{ number_format($item->amount, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="totals-section">
            <div class="totals-notes">
                @if($invoice->notes)
                <div class="notes-box">
                    <h4>Notas</h4>
                    <p>{{ $invoice->notes }}</p>
                </div>
                @endif
            </div>
            <div class="totals-table">
                <table>
                    <tr>
                        <td>Subtotal:</td>
                        <td>${{ number_format($invoice->subtotal, 2) }} MXN</td>
                    </tr>
                    <tr>
                        <td>IVA (16%):</td>
                        <td>${{ number_format($invoice->tax_amount, 2) }} MXN</td>
                    </tr>
                    @if((float)$invoice->retention_amount > 0)
                    <tr>
                        <td>Retención ISR:</td>
                        <td>-${{ number_format($invoice->retention_amount, 2) }} MXN</td>
                    </tr>
                    @endif
                    <tr class="total-row">
                        <td>Total:</td>
                        <td>${{ number_format($invoice->total, 2) }} MXN</td>
                    </tr>
                </table>
            </div>
        </div>

        @if($invoice->paid_at)
        <div style="text-align: center; margin-bottom: 20px; padding: 10px; background-color: #D1FAE5; border-radius: 6px;">
            <strong style="color: #059669;">PAGADA el {{ $invoice->paid_at->format('d/m/Y H:i') }}</strong>
        </div>
        @endif

        <div class="footer">
            <p>Este documento fue generado por Invoice Manager &mdash; {{ now()->format('d/m/Y H:i') }}</p>
        </div>
    </div>
</body>
</html>
