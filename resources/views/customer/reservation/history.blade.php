@extends('layouts.customer')

@section('title', 'Riwayat Reservasi')

@section('styles')
    <script>
        console.log('History page loaded');
        console.log('Reservations data:', @json($reservations));
    </script>
    <link rel="stylesheet" href="{{ asset('Travgo/preview/assets/css/home.css') }}">
    <link href="{{ asset('dist/assets/libs/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css">
    <script src="{{ asset('dist/assets/libs/sweetalert2/sweetalert2.min.js') }}"></script>
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

    .reservation-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        transition: all 0.3s ease;
        margin-bottom: 1.5rem;
        overflow: hidden;
        border: 1px solid #f1f5f9;
        position: relative;
    }

    .reservation-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
    }

    .reservation-header {
        padding: 1.25rem 1.5rem;
        border-bottom: 1px solid #f1f5f9;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .reservation-body {
        padding: 1.5rem;
    }

    .reservation-footer {
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

    .reservation-image {
        width: 110px;
        height: 110px;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        border: 2px solid #f8fafc;
    }

    .reservation-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .reservation-content {
        flex: 1;
        min-width: 0;
        padding-left: 1.5rem;
    }

    .reservation-id {
        font-size: 1.25rem;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 0.5rem;
    }

    .reservation-date {
        color: #64748b;
        font-size: 0.9rem;
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .reservation-info {
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

    .btn-action {
        padding: 0.6rem 1.2rem;
        border-radius: 8px;
        font-size: 0.9rem;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
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

        .reservation-body .d-flex {
            flex-direction: column;
        }

        .reservation-image {
            width: 100%;
            height: 180px;
            margin-bottom: 1rem;
        }

        .reservation-content {
            padding-left: 0;
        }

        .reservation-footer {
            flex-direction: column;
            gap: 1rem;
            align-items: flex-start;
        }

        .reservation-actions {
            width: 100%;
            justify-content: space-between;
        }

        .btn-action {
            flex: 1;
            justify-content: center;
        }
    }

    @media (max-width: 576px) {
        .dashboard-header {
            padding: 1rem;
            border-radius: 10px;
            font-size: 1rem;
        }
        .dashboard-header h3 {
            font-size: 1.2rem;
        }
        .dashboard-header p {
            font-size: 0.95rem;
        }
        .filter-section {
            padding: 0.75rem;
            border-radius: 8px;
        }
        .filter-item {
            min-width: 100%;
        }
        .reservation-card {
            border-radius: 8px;
            margin-bottom: 1rem;
        }
        .reservation-header, .reservation-footer {
            padding: 1rem 0.75rem;
        }
        .reservation-body {
            padding: 1rem 0.75rem;
        }
        .reservation-image {
            width: 100%;
            height: 140px;
            margin-bottom: 0.75rem;
        }
        .reservation-content {
            padding-left: 0;
        }
        .reservation-id {
            font-size: 1rem;
        }
        .reservation-date {
            font-size: 0.85rem;
        }
        .info-item {
            font-size: 0.9rem;
        }
        .price {
            font-size: 1.05rem;
        }
        .reservation-footer {
            flex-direction: column;
            gap: 0.5rem;
            align-items: flex-start;
        }
        .reservation-actions {
            width: 100%;
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }
        .btn-action, .btn-primary, .btn-success, .btn-danger, .btn-secondary {
            width: 100%;
            justify-content: center;
            font-size: 0.97rem;
            padding: 0.7rem 0;
            border-radius: 8px;
        }
        .empty-state {
            padding: 2rem 0.5rem;
        }
        .empty-state h4 {
            font-size: 1.1rem;
        }
        .empty-state p {
            font-size: 0.95rem;
        }
    }
</style>
@endsection

@section('content')
<div style="display: none;">
    Debug Info:
    <pre>
    Route: {{ request()->route()->getName() }}
    URL: {{ request()->url() }}
    Method: {{ request()->method() }}
    User: {{ auth()->user()->name ?? 'Not logged in' }}
    </pre>
</div>

<div class="page-container">
    <!-- Header Section -->
    <div class="dashboard-header">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <h3>Riwayat Reservasi</h3>
                <p>
                    <i class="fas fa-history me-2"></i>
                    Lihat semua reservasi Anda, termasuk yang ditolak, dibatalkan, atau expired
                </p>
            </div>
            <a href="{{ route('customer.reservation.index') }}" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>
                Buat Reservasi
            </a>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="filter-section">
        <form action="{{ route('customer.reservation.history') }}" method="GET">
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
                    <a href="{{ route('customer.reservation.history') }}" class="btn btn-secondary flex-grow-1">
                        <i class="fas fa-sync-alt me-2"></i>Reset
                    </a>
                </div>
            </div>
        </form>
    </div>

    <!-- Reservations List -->
    <div class="reservations">
        @forelse($reservations as $reservation)
        <div class="reservation-card">
            <div class="reservation-header">
                <div class="reservation-id">RES-{{ $reservation->id }}</div>
                <span class="status-badge status-{{ $reservation->status }}">
                    {{ ucfirst($reservation->status) }}
                </span>
            </div>

            <div class="reservation-body">
                <div class="d-flex">
                    <div class="reservation-image">
                        <img src="{{ asset('Travgo/preview/assets/images/home/item-' . (($loop->index % 2) + 1) . '.png') }}" alt="{{ $reservation->table->name }}" class="img-fluid">
                    </div>

                    <div class="reservation-content">
                        <div class="reservation-date">
                            <i class="far fa-calendar-alt"></i>
                            {{ $reservation->created_at->format('d M Y H:i') }}
                        </div>

                        <div class="reservation-info">
                            <div class="info-item">
                                <i class="fas fa-table"></i>
                                <span>Meja {{ $reservation->table->table_number }}</span>
                            </div>
                            <div class="info-item">
                                <i class="fas fa-map-marker-alt"></i>
                                <span>{{ $reservation->table->room->name }}</span>
                            </div>
                            <div class="info-item">
                                <i class="far fa-clock"></i>
                                <span>{{ $reservation->start_time->format('d M Y') }} • {{ $reservation->start_time->format('H:i') }} - {{ $reservation->end_time->format('H:i') }}</span>
                            </div>
                            <div class="info-item">
                                <i class="fas fa-hourglass-half"></i>
                                <span>{{ $reservation->duration_hours }} Jam</span>
                            </div>

                            @if($reservation->status === 'approved' && $reservation->payment_expired_at)
                            <div class="info-item text-warning">
                                <i class="fas fa-exclamation-circle"></i>
                                <span>Batas pembayaran: {{ $reservation->payment_expired_at->format('d M Y H:i') }}</span>
                            </div>
                            @endif

                            @if($reservation->status == 'rejected' && $reservation->rejection_reason)
                            <div class="info-item text-danger">
                                <i class="fas fa-times-circle"></i>
                                <span>Alasan ditolak: {{ $reservation->rejection_reason }}</span>
                            </div>
                            @endif

                            @if($reservation->status == 'cancelled' && $reservation->reason)
                            <div class="info-item text-warning">
                                <i class="fas fa-exclamation-triangle"></i>
                                <span>Alasan dibatalkan: {{ $reservation->reason }}</span>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="reservation-footer">
                <div class="price">Rp {{ number_format($reservation->total_price, 0, ',', '.') }}</div>
                <div class="reservation-actions">
                    <button onclick="checkStatus({{ $reservation->id }})" class="btn btn-info btn-action">
                        <i class="fas fa-sync-alt"></i> Cek Status
                    </button>
                    <a href="{{ route('customer.reservation.show', $reservation->id) }}" class="btn btn-primary btn-action">
                        <i class="fas fa-eye"></i> Detail
                    </a>

                    @if($reservation->status === 'approved')
                    <a href="{{ route('customer.reservation.pay', $reservation->id) }}" class="btn btn-success btn-action">
                        <i class="fas fa-credit-card"></i> Bayar
                    </a>
                    @endif

                    @if($reservation->status === 'pending')
                    <form action="{{ route('customer.reservation.cancel', $reservation->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan reservasi ini?')">
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
            <img src="{{ asset('Travgo/preview/assets/images/empty-state.svg') }}" alt="Tidak ada reservasi" class="img-fluid">
            <h4>Belum Ada Reservasi</h4>
            <p>Anda belum memiliki riwayat reservasi. Mulai dengan membuat reservasi meja biliar sekarang!</p>
            <a href="{{ route('customer.reservation.index') }}" class="btn btn-primary btn-lg">
                <i class="fas fa-plus me-2"></i>Buat Reservasi
            </a>
        </div>
        @endforelse

        @if($reservations->count() > 0)
        <div class="d-flex justify-content-center mt-4">
            {{ $reservations->links() }}
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

    function checkStatus(id) {
        Swal.fire({
            title: 'Memeriksa Status',
            text: 'Mohon tunggu sebentar...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        // Kirim request AJAX untuk memeriksa status
        $.ajax({
            url: `/customer/reservation/${id}/check-status`,
            method: 'GET',
            success: function(response) {
                Swal.fire({
                    title: 'Status Terbaru',
                    text: `Status reservasi Anda: ${response.status}`,
                    icon: 'info',
                    confirmButtonText: 'OK'
                }).then(() => {
                    // Refresh halaman setelah user klik OK
                    window.location.reload();
                });
            },
            error: function(xhr) {
                let errorMessage = 'Gagal memeriksa status reservasi';
                if (xhr.responseJSON && xhr.responseJSON.error) {
                    errorMessage = xhr.responseJSON.error;
                }
                Swal.fire({
                    title: 'Error',
                    text: errorMessage,
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            }
        });
    }
</script>
@endpush
