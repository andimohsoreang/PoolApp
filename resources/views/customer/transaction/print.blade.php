<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nota Transaksi #{{ $transaction->id }}</title>
    <style>
        @page {
            size: 80mm 297mm;
            margin: 0;
        }
        body {
            font-family: 'Arial', sans-serif;
            font-size: 12px;
            line-height: 1.4;
            margin: 0;
            padding: 10px;
            color: #000;
        }
        .receipt {
            width: 100%;
            max-width: 80mm;
            margin: 0 auto;
        }
        .header {
            text-align: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px dashed #000;
        }
        .header h1 {
            font-size: 16px;
            margin: 0 0 5px 0;
            font-weight: bold;
        }
        .header p {
            margin: 0;
            font-size: 11px;
        }
        .info-section {
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px dashed #000;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
        }
        .info-label {
            font-weight: bold;
        }
        .info-value {
            text-align: right;
        }
        .items {
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px dashed #000;
        }
        .item {
            margin-bottom: 5px;
        }
        .item-row {
            display: flex;
            justify-content: space-between;
        }
        .total-section {
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px dashed #000;
        }
        .total-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
        }
        .total-label {
            font-weight: bold;
        }
        .total-value {
            font-weight: bold;
            text-align: right;
        }
        .footer {
            text-align: center;
            font-size: 11px;
            margin-top: 15px;
        }
        .status-badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 11px;
            font-weight: bold;
            margin-top: 5px;
        }
        .status-success { background: #e6f7ed; color: #06783a; }
        .status-pending { background: #fff4de; color: #905911; }
        .status-cancelled { background: #ffeced; color: #b91c1c; }
        .qr-code {
            text-align: center;
            margin: 15px 0;
        }
        .qr-code img {
            max-width: 120px;
            height: auto;
        }
        @media print {
            body {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="receipt">
        <div class="header">
            <h1>POOL OPEN SYSTEM</h1>
            <p>Jl. Contoh No. 123, Makassar</p>
            <p>Telp: (0411) 123456</p>
        </div>

        <div class="info-section">
            <div class="info-row">
                <span class="info-label">No. Transaksi:</span>
                <span class="info-value">TRX-{{ $transaction->id }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Tanggal:</span>
                <span class="info-value">{{ $transaction->created_at->timezone('Asia/Makassar')->format('d/m/Y H:i') }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Status:</span>
                <span class="info-value">
                    <span class="status-badge status-{{ $transaction->status }}">
                        {{ ucfirst($transaction->status) }}
                    </span>
                </span>
            </div>
        </div>

        <div class="items">
            <div class="item">
                <div class="item-row">
                    <span>Reservasi Meja {{ $transaction->table->table_number }}</span>
                </div>
                <div class="item-row">
                    <span>{{ $transaction->table->room->name }}</span>
                </div>
                <div class="item-row">
                    <span>{{ $transaction->start_time->timezone('Asia/Makassar')->format('d/m/Y H:i') }} - {{ $transaction->end_time->timezone('Asia/Makassar')->format('H:i') }}</span>
                </div>
                @if($transaction->duration_hours)
                <div class="item-row">
                    <span>{{ $transaction->duration_hours }} jam × Rp {{ number_format($transaction->price_per_hour, 0, ',', '.') }}</span>
                </div>
                @endif
                @if($transaction->discount > 0)
                <div class="item-row">
                    <span class="text-danger">Diskon: - Rp {{ number_format($transaction->discount, 0, ',', '.') }}</span>
                </div>
                @endif
            </div>
        </div>

        <div class="total-section">
            <div class="total-row">
                <span class="total-label">Total:</span>
                <span class="total-value">Rp {{ number_format($transaction->total_price, 0, ',', '.') }}</span>
            </div>
        </div>

        <div class="qr-code">
            {!! QrCode::size(120)->generate(route('customer.transaction.show', $transaction->id)) !!}
        </div>

        <div class="footer">
            <p>Terima kasih atas kunjungan Anda</p>
            <p>Nota ini adalah bukti transaksi yang sah</p>
            <p>www.poolopensystem.com</p>
        </div>
    </div>

    <div class="no-print" style="text-align: center; margin-top: 20px;">
        <button onclick="window.print()" style="padding: 10px 20px; background: #6246ea; color: white; border: none; border-radius: 5px; cursor: pointer;">
            Cetak Nota
        </button>
    </div>
</body>
</html>