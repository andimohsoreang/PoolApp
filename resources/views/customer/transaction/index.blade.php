@extends('layouts.customer')

@section('title', 'Riwayat Transaksi')

@section('styles')
<link rel="stylesheet" href="{{ asset('Travgo/preview/assets/css/home.css') }}">
<style>
    .page-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 2rem 1rem;
    }

    .dashboard-header {
        background: linear-gradient(120deg, #6246ea 0%, #2563eb 100%);
        border-radius: 16px;
        padding: 2rem;
        margin-bottom: 2rem;
        position: relative;
        overflow: hidden;
        color: white;
        box-shadow: 0 10px 20px rgba(37, 99, 235, 0.15);
    }

    .dashboard-header h3 {
        font-size: 1.8rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
        position: relative;
        z-index: 1;
    }

    .dashboard-header p {
        font-size: 1rem;
        opacity: 0.9;
        margin-bottom: 0;
        position: relative;
        z-index: 1;
    }

    .dashboard-header::before {
        content: '';
        position: absolute;
        right: -50px;
        top: -50px;
        width: 200px;
        height: 200px;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 50%;
    }

    .dashboard-header::after {
        content: '';
        position: absolute;
        right: 50px;
        bottom: -50px;
        width: 150px;
        height: 150px;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 50%;
    }

    .filter-section {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        margin-bottom: 2rem;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.05);
        border: 1px solid #f1f5f9;
    }

    .filter-item {
        flex: 1;
        min-width: 200px;
    }

    .filter-item label {
        font-weight: 600;
        color: #334155;
        margin-bottom: 0.5rem;
    }

    .form-select, .form-control {
        padding: 0.75rem 1rem;
        border-radius: 8px;
        border: 1px solid #e2e8f0;
        font-size: 0.95rem;
        box-shadow: none;
        transition: all 0.3s ease;
    }

    .form-select:focus, .form-control:focus {
        border-color: #6246ea;
        box-shadow: 0 0 0 3px rgba(98, 70, 234, 0.1);
    }

    .btn {
        padding: 0.75rem 1.5rem;
        border-radius: 8px;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .btn-primary {
        background: #6246ea;
        border-color: #6246ea;
    }

    .btn-primary:hover {
        background: #5236d9;
        border-color: #5236d9;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(98, 70, 234, 0.15);
    }

    .btn-secondary {
        background: #f8fafc;
        color: #334155;
        border-color: #e2e8f0;
    }

    .btn-secondary:hover {
        background: #f1f5f9;
        color: #1e293b;
        border-color: #cbd5e1;
    }

    .transaction-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        transition: all 0.3s ease;
        margin-bottom: 1.5rem;
        overflow: hidden;
        border: 1px solid #f1f5f9;
        position: relative;
    }

    .transaction-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
    }

    .transaction-header {
        padding: 1.25rem 1.5rem;
        border-bottom: 1px solid #f1f5f9;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .transaction-body {
        padding: 1.5rem;
    }

    .transaction-footer {
        background: #f8fafc;
        padding: 1rem 1.5rem;
        border-top: 1px solid #f1f5f9;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .status-badge {
        padding: 0.5rem 1rem;
        border-radius: 50px;
        font-size: 0.85rem;
        font-weight: 600;
        display: inline-block;
    }

    .status-pending { background: #fff4de; color: #905911; }
    .status-approved { background: #e0f2ff; color: #0058b9; }
    .status-paid { background: #e6f7ed; color: #06783a; }
    .status-completed { background: #e0f2ff; color: #0069cc; }
    .status-cancelled { background: #ffeced; color: #b91c1c; }
    .status-rejected { background: #f3f4f6; color: #4b5563; }
    .status-expired { background: #f3f4f6; color: #4b5563; }

    .price {
        font-size: 1.25rem;
        font-weight: 700;
        color: #1e293b;
    }

    .transaction-image {
        width: 110px;
        height: 110px;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        border: 2px solid #f8fafc;
    }

    .transaction-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .transaction-content {
        flex: 1;
        min-width: 0;
        padding-left: 1.5rem;
    }

    .transaction-id {
        font-size: 1.25rem;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 0.5rem;
    }

    .transaction-date {
        color: #64748b;
        font-size: 0.9rem;
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .transaction-info {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }

    .info-item {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.95rem;
        color: #334155;
    }

    .info-item i {
        color: #6246ea;
        font-size: 1rem;
        width: 20px;
        text-align: center;
    }

    .badge-source {
        background: #f1f5f9;
        color: #475569;
        font-size: 0.75rem;
        font-weight: 600;
        padding: 0.35rem 0.75rem;
        border-radius: 4px;
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
    }

    .badge-source i {
        font-size: 0.8rem;
    }

    .btn-action {
        padding: 0.6rem 1.2rem;
        border-radius: 8px;
        font-size: 0.9rem;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }

    .btn-outline-primary {
        color: #6246ea;
        border-color: #6246ea;
    }

    .btn-outline-primary:hover {
        background: #6246ea;
        color: white;
    }

    .btn-success {
        background: #10b981;
        border-color: #10b981;
    }

    .btn-success:hover {
        background: #0ca678;
        border-color: #0ca678;
    }

    .transaction-actions {
        display: flex;
        gap: 0.75rem;
    }

    .empty-state {
        text-align: center;
        padding: 4rem 2rem;
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.05);
    }

    .empty-state img {
        max-width: 220px;
        margin-bottom: 2rem;
    }

    .empty-state h4 {
        font-size: 1.5rem;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 1rem;
    }

    .empty-state p {
        color: #64748b;
        font-size: 1.1rem;
        margin-bottom: 2rem;
        max-width: 500px;
        margin-left: auto;
        margin-right: auto;
    }

    /* Mobile Optimizations */
    @media (max-width: 768px) {
        .dashboard-header {
            padding: 1.5rem;
        }

        .filter-section {
            padding: 1rem;
        }

        .filter-section .d-flex {
            flex-direction: column;
            gap: 1rem;
        }

        .transaction-body .d-flex {
            flex-direction: column;
        }

        .transaction-image {
            width: 100%;
            height: 180px;
            margin-bottom: 1rem;
        }

        .transaction-content {
            padding-left: 0;
        }

        .transaction-footer {
            flex-direction: column;
            gap: 1rem;
            align-items: flex-start;
        }

        .transaction-actions {
            width: 100%;
            justify-content: space-between;
        }

        .btn-action {
            flex: 1;
            justify-content: center;
        }
    }
</style>
@endsection

@section('content')
<div class="page-container">
    <!-- Header Section -->
    <div class="dashboard-header">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <h3>Riwayat Transaksi</h3>
                <p>
                    <i class="fas fa-history me-2"></i>
                    Lihat riwayat transaksi dan status reservasi Anda
                </p>
            </div>
            <a href="{{ route('customer.reservation.index') }}" class="btn btn-light d-inline-flex align-items-center gap-2">
                <i class="fas fa-plus"></i>
                Buat Reservasi
            </a>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="filter-section">
        <form action="{{ route('customer.transaction.index') }}" method="GET">
            <div class="d-flex gap-3 flex-wrap">
                <div class="filter-item">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">Semua Status</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Menunggu</option>
                        <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Disetujui</option>
                        <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Dibayar</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Selesai</option>
                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Dibatalkan</option>
                        <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Ditolak</option>
                        <option value="expired" {{ request('status') == 'expired' ? 'selected' : '' }}>Kedaluwarsa</option>
                    </select>
                </div>
                <div class="filter-item">
                    <label class="form-label">Tanggal</label>
                    <input type="text" name="date" class="form-control datepicker" value="{{ request('date') }}" placeholder="Pilih tanggal">
                </div>
                <div class="filter-item d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary flex-grow-1">
                        <i class="fas fa-filter me-2"></i>Filter
                    </button>
                    <a href="{{ route('customer.transaction.index') }}" class="btn btn-secondary flex-grow-1">
                        <i class="fas fa-sync-alt me-2"></i>Reset
                    </a>
                </div>
            </div>
        </form>
    </div>

    <!-- Transactions List -->
    <div class="transactions">
        @forelse($transactions as $transaction)
        <div class="transaction-card">
            <div class="transaction-header">
                <div class="transaction-id">RES-{{ $transaction->id }}</div>
                <span class="status-badge status-{{ $transaction->status }}">
                    {{ ucfirst($transaction->status) }}
                </span>
            </div>

            <div class="transaction-body">
                <div class="d-flex">
                    <div class="transaction-image">
                        <img src="{{ asset('Travgo/preview/assets/images/home/budget-1.png') }}" alt="{{ $transaction->table->name }}" class="img-fluid">
                    </div>

                    <div class="transaction-content">
                        <div class="transaction-date">
                            <i class="far fa-calendar-alt"></i>
                            {{ $transaction->created_at->format('d M Y H:i') }}
                            <span class="badge-source ms-2">
                                <i class="fas {{ $transaction->source === 'reservation' ? 'fa-globe' : 'fa-store' }}"></i>
                                {{ $transaction->source === 'reservation' ? 'Reservasi Online' : 'Reservasi Online' }}
                            </span>
                        </div>

                        <div class="transaction-info">
                            <div class="info-item">
                                <i class="fas fa-table"></i>
                                <span>Meja {{ $transaction->table->table_number }}</span>
                            </div>
                            <div class="info-item">
                                <i class="fas fa-map-marker-alt"></i>
                                <span>{{ $transaction->table->room->name }}</span>
                            </div>
                            <div class="info-item">
                                <i class="far fa-clock"></i>
                                <span>{{ $transaction->start_time->format('d M Y') }} • {{ $transaction->start_time->format('H:i') }} - {{ $transaction->end_time->format('H:i') }}</span>
                            </div>
                            <div class="info-item">
                                <i class="fas fa-hourglass-half"></i>
                                <span>{{ $transaction->duration_hours }} Jam</span>
                            </div>

                            @if($transaction->status === 'approved' && $transaction->payment_expired_at)
                            <div class="info-item text-warning">
                                <i class="fas fa-exclamation-circle"></i>
                                <span>Batas pembayaran: {{ $transaction->payment_expired_at->format('d M Y H:i') }}</span>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="transaction-footer">
                <div class="price">Rp {{ number_format($transaction->total_price, 0, ',', '.') }}</div>
                <div class="transaction-actions">
                    <a href="{{ route('customer.transaction.show', $transaction->id) }}" class="btn btn-sm btn-primary">
                        <i class="fas fa-eye"></i> Detail
                    </a>

                    @if($transaction->status === 'approved')
                    <a href="{{ route('customer.reservation.pay', $transaction->id) }}" class="btn btn-success btn-action">
                        <i class="fas fa-credit-card"></i>Bayar
                    </a>
                    @endif

                    @if($transaction->status === 'pending')
                    <form action="{{ route('customer.reservation.cancel', $transaction->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan reservasi ini?')">
                        @csrf
                        <button type="submit" class="btn btn-danger btn-action">
                            <i class="fas fa-times"></i>Batalkan
                        </button>
                    </form>
                    @endif
                </div>
            </div>
        </div>
        @empty
        <div class="empty-state">
            <img src="{{ asset('Travgo/preview/assets/images/empty-state.svg') }}" alt="Tidak ada transaksi" class="img-fluid">
            <h4>Belum Ada Transaksi</h4>
            <p>Anda belum memiliki riwayat transaksi. Mulai dengan membuat reservasi meja biliar sekarang!</p>
            <a href="{{ route('customer.reservation.index') }}" class="btn btn-primary btn-lg">
                <i class="fas fa-plus me-2"></i>Buat Reservasi
            </a>
        </div>
        @endforelse

        @if($transactions->count() > 0)
        <div class="d-flex justify-content-center mt-4">
            {{ $transactions->links() }}
        </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('.datepicker').datepicker({
            format: 'yyyy-mm-dd',
            autoclose: true,
            todayHighlight: true
        });
    });

    function checkStatus() {
        Swal.fire({
            title: 'Memeriksa Status',
            text: 'Mohon tunggu sebentar...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        // Refresh halaman setelah 1 detik
        setTimeout(() => {
            window.location.reload();
        }, 1000);
    }

    function confirmCancel(event) {
        event.preventDefault();
        Swal.fire({
            title: 'Batalkan Reservasi?',
            text: "Apakah Anda yakin ingin membatalkan reservasi ini?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, batalkan!',
            cancelButtonText: 'Tidak, kembali'
        }).then((result) => {
            if (result.isConfirmed) {
                // Submit form
                event.target.submit();
                // Refresh page after 1 second
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            }
        });
        return false;
    }
</script>
@endpush
