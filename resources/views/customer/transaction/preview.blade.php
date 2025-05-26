@extends('layouts.customer')

@section('title', 'Preview Nota Transaksi #' . $transaction->id)

@section('styles')
<style>
    .page-container {
        padding: 40px 20px;
        min-height: 100vh;
        background: #f8f9fa;
    }
    .receipt-preview {
        max-width: 80mm;
        margin: 0 auto;
        padding: 20px;
        background: white;
        box-shadow: 0 0 20px rgba(0,0,0,0.1);
        border-radius: 8px;
        margin-bottom: 30px;
    }
    .receipt-header {
        text-align: center;
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 1px dashed #000;
    }
    .receipt-header h1 {
        font-size: 18px;
        margin: 0 0 5px 0;
        font-weight: bold;
    }
    .receipt-header p {
        margin: 0;
        font-size: 12px;
    }
    .receipt-info {
        margin-bottom: 20px;
    }
    .info-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 5px;
        font-size: 12px;
    }
    .info-label {
        font-weight: bold;
    }
    .receipt-items {
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 1px dashed #000;
    }
    .item-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 5px;
        font-size: 12px;
    }
    .receipt-total {
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 1px dashed #000;
    }
    .total-row {
        display: flex;
        justify-content: space-between;
        font-weight: bold;
        font-size: 14px;
    }
    .receipt-footer {
        text-align: center;
        font-size: 11px;
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
        padding: 10px;
        background: #f8f9fa;
        border-radius: 8px;
    }
    .qr-code img {
        max-width: 120px;
        height: auto;
    }
    .action-buttons {
        margin-top: 20px;
        text-align: center;
        display: flex;
        justify-content: center;
        gap: 10px;
        flex-wrap: wrap;
    }
    .btn-action {
        padding: 10px 20px;
        border-radius: 5px;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-weight: 500;
        transition: all 0.3s ease;
        border: none;
        color: white;
    }
    .btn-print {
        background: #6246ea;
    }
    .btn-print:hover {
        background: #4f3ab9;
    }
    .btn-download {
        background: #10b981;
    }
    .btn-download:hover {
        background: #059669;
    }
    .btn-share {
        background: #3b82f6;
    }
    .btn-share:hover {
        background: #2563eb;
    }
    .btn-back {
        background: #6c757d;
    }
    .btn-back:hover {
        background: #5a6268;
    }
    .btn-action i {
        font-size: 16px;
    }
    @media print {
        .page-container {
            padding: 0;
            background: white;
        }
        .action-buttons {
            display: none;
        }
    }
</style>
@endsection

@section('content')
<div class="page-container">
    <div class="receipt-preview">
        <div class="receipt-header">
            <h1>POOL OPEN SYSTEM</h1>
            <p>Jl. Contoh No. 123, Makassar</p>
            <p>Telp: (0411) 123456</p>
        </div>

        <div class="receipt-info">
            <div class="info-row">
                <span class="info-label">No. Transaksi:</span>
                <span>TRX-{{ $transaction->id }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Tanggal:</span>
                <span>{{ $transaction->created_at->timezone('Asia/Makassar')->format('d/m/Y H:i') }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Status:</span>
                <span>
                    <span class="status-badge status-{{ $transaction->status }}">
                        {{ ucfirst($transaction->status) }}
                    </span>
                </span>
            </div>
        </div>

        <div class="receipt-items">
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

        <div class="receipt-total">
            <div class="total-row">
                <span>Total:</span>
                <span>Rp {{ number_format($transaction->total_price, 0, ',', '.') }}</span>
            </div>
        </div>

        <div class="qr-code">
            @php
                $qrData = route('customer.transaction.show', $transaction->id);
                try {
                    echo QrCode::size(120)->generate($qrData);
                } catch (\Exception $e) {
                    echo '<div class="text-danger">QR Code tidak tersedia</div>';
                }
            @endphp
        </div>

        <div class="receipt-footer">
            <p>Terima kasih atas kunjungan Anda</p>
            <p>Nota ini adalah bukti transaksi yang sah</p>
            <p>www.poolopensystem.com</p>
        </div>
    </div>

    <div class="action-buttons">
        <a href="{{ route('customer.transaction.print', $transaction->id) }}" class="btn-action btn-print" target="_blank">
            <i class="fas fa-print"></i> Cetak Nota
        </a>
        <button onclick="downloadReceipt()" class="btn-action btn-download">
            <i class="fas fa-download"></i> Download PDF
        </button>
        <button onclick="shareReceipt()" class="btn-action btn-share">
            <i class="fas fa-share-alt"></i> Bagikan
        </button>
        <a href="{{ route('customer.transaction.show', $transaction->id) }}" class="btn-action btn-back">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>
</div>

@push('scripts')
<script>
function downloadReceipt() {
    // Implementasi download PDF akan ditambahkan nanti
    alert('Fitur download PDF akan segera hadir!');
}

function shareReceipt() {
    if (navigator.share) {
        navigator.share({
            title: 'Nota Transaksi #{{ $transaction->id }}',
            text: 'Lihat nota transaksi saya di Pool Open System',
            url: '{{ route('customer.transaction.show', $transaction->id) }}'
        })
        .catch((error) => console.log('Error sharing:', error));
    } else {
        alert('Fitur berbagi tidak didukung di browser Anda');
    }
}
</script>
@endpush
@endsection