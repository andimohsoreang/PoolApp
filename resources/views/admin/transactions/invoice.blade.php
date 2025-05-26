<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #{{ $transaction->transaction_code }}</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            font-size: 14px;
            color: #333;
        }
        .invoice-container {
            width: 100%;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            box-sizing: border-box;
        }
        .invoice-header {
            margin-bottom: 20px;
            border-bottom: 2px solid #ddd;
            padding-bottom: 20px;
        }
        .invoice-title {
            font-size: 24px;
            color: #2f3542;
            margin: 0;
            margin-bottom: 5px;
        }
        .invoice-subtitle {
            color: #5d6a7d;
            margin: 0;
        }
        .company-details {
            text-align: right;
        }
        .company-name {
            font-size: 18px;
            font-weight: bold;
            margin: 0;
        }
        .invoice-body {
            margin-bottom: 20px;
        }
        .customer-details, .invoice-details {
            margin-bottom: 20px;
            width: 50%;
            float: left;
        }
        .section-title {
            font-size: 16px;
            margin-bottom: 10px;
            color: #2f3542;
            border-bottom: 1px solid #eee;
            padding-bottom: 5px;
        }
        .details-row {
            margin-bottom: 5px;
        }
        .details-row strong {
            font-weight: 600;
        }
        .invoice-items {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            clear: both;
        }
        .invoice-items th {
            background-color: #f2f2f2;
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        .invoice-items td {
            padding: 10px;
            border-bottom: 1px solid #eee;
        }
        .invoice-summary {
            float: right;
            width: 30%;
            margin-top: 20px;
        }
        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
        }
        .summary-row.total {
            font-weight: bold;
            font-size: 16px;
            border-top: 1px solid #ddd;
            padding-top: 5px;
            margin-top: 5px;
        }
        .invoice-footer {
            margin-top: 30px;
            border-top: 1px solid #ddd;
            padding-top: 10px;
            text-align: center;
            clear: both;
            font-size: 12px;
            color: #777;
        }
        .text-right {
            text-align: right;
        }
        .clearfix::after {
            content: "";
            clear: both;
            display: table;
        }
        .print-button {
            background-color: #3498db;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 4px;
            cursor: pointer;
            margin-bottom: 20px;
            font-size: 14px;
        }
        .paid-stamp {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-30deg);
            color: rgba(46, 204, 113, 0.2);
            font-size: 80px;
            font-weight: bold;
            text-align: center;
            z-index: -1;
        }
        .signature-area {
            margin-top: 50px;
            display: flex;
            justify-content: space-between;
        }
        .signature-box {
            width: 45%;
            text-align: center;
        }
        .signature-line {
            border-top: 1px solid #ddd;
            margin-top: 50px;
            margin-bottom: 10px;
        }
        @media print {
            .print-button {
                display: none;
            }
            body {
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
            }
        }
    </style>
</head>
<body>
    <div class="invoice-container">
        <button onclick="window.print()" class="print-button">Print Invoice</button>

        @if($transaction->status == 'paid' || $transaction->status == 'completed')
            <div class="paid-stamp">PAID</div>
        @endif

        <div class="invoice-header">
            <div style="float: left; width: 50%;">
                <h1 class="invoice-title">INVOICE</h1>
                <h3 class="invoice-subtitle">{{ $transaction->transaction_code }}</h3>
            </div>
            <div class="company-details" style="float: right; width: 50%;">
                <h2 class="company-name">Pool Open System</h2>
                <p>Jl. Contoh No. 123<br>
                   Kota, Provinsi<br>
                   Indonesia<br>
                   Telp: (021) 1234567</p>
            </div>
            <div style="clear: both;"></div>
        </div>

        <div class="invoice-body">
            <div class="customer-details">
                <h3 class="section-title">Customer</h3>
                <div class="details-row"><strong>Nama:</strong> {{ $transaction->customer->name }}</div>
                <div class="details-row"><strong>Telepon:</strong> {{ $transaction->customer->phone }}</div>
                @if($transaction->customer->email)
                    <div class="details-row"><strong>Email:</strong> {{ $transaction->customer->email }}</div>
                @endif
            </div>

            <div class="invoice-details">
                <h3 class="section-title">Detail Transaksi</h3>
                <div class="details-row"><strong>Tanggal:</strong> {{ $transaction->created_at->format('d F Y') }}</div>
                <div class="details-row"><strong>Status:</strong> {{ ucfirst($transaction->status) }}</div>
                <div class="details-row"><strong>Tipe:</strong> {{ $transaction->transaction_type == 'walk_in' ? 'Walk In' : 'Reservation' }}</div>
            </div>

            <div class="clearfix"></div>

            <table class="invoice-items">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Deskripsi</th>
                        <th>Durasi</th>
                        <th>Harga Per Jam</th>
                        <th class="text-right">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $transactionDetail = $transaction->details->first();
                        $pricePerHour = $transactionDetail ? $transactionDetail->price_per_hour : 0;
                        $subtotal = $transactionDetail ? $transactionDetail->subtotal : $transaction->total_price;
                    @endphp
                    <tr>
                        <td>Sewa Meja Billiard</td>
                        <td>Meja {{ $transaction->table->table_number }} ({{ $transaction->table->room->name }})</td>
                        <td>{{ number_format($transaction->duration_hours, 2) }} jam</td>
                        <td>Rp {{ number_format($pricePerHour, 0, ',', '.') }}</td>
                        <td class="text-right">Rp {{ number_format($subtotal, 0, ',', '.') }}</td>
                    </tr>
                </tbody>
            </table>

            <div class="invoice-summary">
                <div class="summary-row">
                    <span>Subtotal:</span>
                    <span>Rp {{ number_format($subtotal, 0, ',', '.') }}</span>
                </div>

                <div class="summary-row total">
                    <span>Total:</span>
                    <span>Rp {{ number_format($transaction->total_price, 0, ',', '.') }}</span>
                </div>

                @if($transaction->payment && $transaction->payment->status == 'paid')
                <div class="summary-row">
                    <span>Dibayar:</span>
                    <span>Rp {{ number_format($transaction->payment->amount_paid, 0, ',', '.') }}</span>
                </div>
                <div class="summary-row">
                    <span>Kembalian:</span>
                    <span>Rp {{ number_format($transaction->payment->change_amount, 0, ',', '.') }}</span>
                </div>
                <div class="summary-row">
                    <span>Metode Pembayaran:</span>
                    <span>{{ $transaction->payment->payment_method == 'cash' ? 'Cash' : 'E-Payment' }}</span>
                </div>
                @endif
            </div>

            <div class="clearfix"></div>

            <div class="signature-area">
                <div class="signature-box">
                    <div class="signature-line"></div>
                    <p>Kasir</p>
                </div>
                <div class="signature-box">
                    <div class="signature-line"></div>
                    <p>Customer</p>
                </div>
            </div>
        </div>

        <div class="invoice-footer">
            <p>Terima kasih atas kunjungan Anda. Kami menantikan kunjungan Anda kembali.</p>
            <p>Invoice ini dihasilkan secara digital dan sah tanpa tanda tangan.</p>
        </div>
    </div>
</body>
</html>
