@extends('layouts.customer')

@section('title', 'Detail Reservasi')

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

    .detail-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        margin-bottom: 1.5rem;
        overflow: hidden;
        border: 1px solid #f1f5f9;
    }

    .detail-header {
        padding: 1.25rem 1.5rem;
        border-bottom: 1px solid #f1f5f9;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .detail-body {
        padding: 1.5rem;
    }

    .detail-footer {
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

    .detail-image {
        width: 100%;
        height: 200px;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        border: 2px solid #f8fafc;
        margin-bottom: 1.5rem;
    }

    .detail-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .detail-content {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 1.5rem;
    }

    .detail-section {
        background: #f8fafc;
        border-radius: 8px;
        padding: 1.25rem;
    }

    .detail-section h5 {
        font-size: 1.1rem;
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .detail-section h5 i {
        color: #6246ea;
    }

    .info-item {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        margin-bottom: 0.75rem;
        font-size: 0.95rem;
        color: #334155;
    }

    .info-item i {
        color: #6246ea;
        font-size: 1rem;
        width: 20px;
        text-align: center;
    }

    .info-label {
        font-weight: 500;
        min-width: 120px;
    }

    .info-value {
        color: #1e293b;
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

    .timeline {
        position: relative;
        padding: 20px 0;
    }

    .timeline-item {
        position: relative;
        padding-left: 40px;
        margin-bottom: 20px;
    }

    .timeline-item:before {
        content: '';
        position: absolute;
        left: 15px;
        top: 0;
        bottom: -20px;
        width: 2px;
        background: #e9ecef;
    }

    .timeline-item:last-child:before {
        display: none;
    }

    .timeline-dot {
        position: absolute;
        left: 10px;
        top: 5px;
        width: 12px;
        height: 12px;
        border-radius: 50%;
        background: #fff;
        border: 2px solid #4361ee;
    }

    .timeline-content {
        background: #f8f9fa;
        padding: 15px;
        border-radius: 8px;
    }

    .timeline-date {
        font-size: 0.85rem;
        color: #6c757d;
        margin-bottom: 5px;
    }

    .timeline-title {
        font-weight: 600;
        margin-bottom: 5px;
    }

    .timeline-text {
        color: #495057;
        margin-bottom: 0;
    }

    @media (max-width: 768px) {
        .dashboard-header {
            padding: 1.5rem;
        }

        .detail-content {
            grid-template-columns: 1fr;
        }

        .detail-footer {
            flex-direction: column;
            gap: 1rem;
            align-items: flex-start;
        }

        .btn-action {
            width: 100%;
            justify-content: center;
        }
    }

    .status-section {
        background: white;
        border-radius: 12px;
        padding: 1.25rem;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    }

    .status-title {
        font-size: 1.1rem;
        color: #1e293b;
        margin-bottom: 1.25rem;
        font-weight: 600;
    }

    .timeline {
        position: relative;
        padding-left: 1.5rem;
    }

    .timeline::before {
        content: '';
        position: absolute;
        left: 0.4rem;
        top: 0;
        bottom: 0;
        width: 2px;
        background: #e2e8f0;
    }

    .timeline-item {
        position: relative;
        padding-bottom: 1.5rem;
        opacity: 0.5;
        transition: all 0.3s ease;
    }

    .timeline-item.active,
    .timeline-item.completed {
        opacity: 1;
    }

    .timeline-item:last-child {
        padding-bottom: 0;
    }

    .timeline-icon {
        position: absolute;
        left: -1.5rem;
        width: 1.5rem;
        height: 1.5rem;
        border-radius: 50%;
        background: #f1f5f9;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #64748b;
        font-size: 0.75rem;
        z-index: 1;
        transition: all 0.3s ease;
    }

    .timeline-item.active .timeline-icon {
        background: #6246ea;
        color: white;
        transform: scale(1.1);
    }

    .timeline-item.completed .timeline-icon {
        background: #10b981;
        color: white;
    }

    .timeline-content {
        background: #f8fafc;
        padding: 0.875rem;
        border-radius: 8px;
        margin-left: 0.75rem;
        transition: all 0.3s ease;
    }

    .timeline-content h5 {
        color: #1e293b;
        margin-bottom: 0.25rem;
        font-size: 0.95rem;
        font-weight: 600;
    }

    .timeline-content p {
        color: #64748b;
        margin-bottom: 0.25rem;
        font-size: 0.85rem;
        line-height: 1.4;
    }

    .timeline-item.active .timeline-content {
        background: #f1f5f9;
        border-left: 2px solid #6246ea;
    }

    .timeline-item.completed .timeline-content {
        background: #f0fdf4;
        border-left: 2px solid #10b981;
    }

    .text-warning, .text-info {
        font-size: 0.8rem;
        margin-top: 0.25rem;
        display: flex;
        align-items: center;
        gap: 0.375rem;
        color: #64748b;
    }

    .text-warning i {
        color: #f59e0b;
    }

    .text-info i {
        color: #3b82f6;
    }

    @media (max-width: 576px) {
        .status-section {
            padding: 1rem;
        }

        .timeline {
            padding-left: 1.25rem;
        }

        .timeline-icon {
            left: -1.25rem;
            width: 1.25rem;
            height: 1.25rem;
            font-size: 0.7rem;
        }

        .timeline-content {
            padding: 0.75rem;
            margin-left: 0.5rem;
        }

        .timeline-content h5 {
            font-size: 0.9rem;
        }

        .timeline-content p {
            font-size: 0.8rem;
        }

        .text-warning, .text-info {
            font-size: 0.75rem;
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
                <h3>Detail Reservasi</h3>
                <p>
                    <i class="fas fa-info-circle me-2"></i>
                    Informasi lengkap tentang reservasi Anda
                </p>
            </div>
            <a href="{{ route('customer.reservation.history') }}" class="btn btn-light d-inline-flex align-items-center gap-2">
                <i class="fas fa-arrow-left"></i>
                Kembali
            </a>
        </div>
    </div>

    <!-- Detail Card -->
    <div class="detail-card">
        <div class="detail-header">
            <div class="reservation-id">RES-{{ $reservation->id }}</div>
            <span class="status-badge status-{{ $reservation->status }}">
                {{ ucfirst($reservation->status) }}
            </span>
        </div>

        <div class="detail-body">
            <div class="detail-image">
                <img src="{{ asset('Travgo/preview/assets/images/home/item-' . (($reservation->id % 2) + 1) . '.png') }}" alt="{{ $reservation->table->name }}" class="img-fluid">
            </div>

            <div class="detail-content">
                <!-- Informasi Reservasi -->
                <div class="detail-section">
                    <h5><i class="fas fa-calendar-alt"></i> Informasi Reservasi</h5>
                    <div class="info-item">
                        <span class="info-label">Tanggal</span>
                        <span class="info-value">{{ $reservation->start_time->format('d M Y') }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Waktu</span>
                        <span class="info-value">{{ $reservation->start_time->format('H:i') }} - {{ $reservation->end_time->format('H:i') }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Durasi</span>
                        <span class="info-value">{{ $reservation->duration_hours }} Jam</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Total Harga</span>
                        <span class="info-value">Rp {{ number_format($reservation->total_price, 0, ',', '.') }}</span>
                    </div>
                </div>

                <!-- Informasi Meja -->
                <div class="detail-section">
                    <h5><i class="fas fa-table"></i> Informasi Meja</h5>
                    <div class="info-item">
                        <span class="info-label">Nomor Meja</span>
                        <span class="info-value">Meja {{ $reservation->table->table_number }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Ruangan</span>
                        <span class="info-value">{{ $reservation->table->room->name }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Harga per Jam</span>
                        <span class="info-value">Rp {{ number_format($reservation->price_per_hour, 0, ',', '.') }}</span>
                    </div>
                </div>

                <!-- Status & Timeline -->
                <div class="status-section mb-4">
                    <h4>Status Reservasi</h4>
                    <div class="status-timeline">
                        @php
                            $statuses = [
                                'pending' => [
                                    'icon' => 'fas fa-clock',
                                    'title' => 'Menunggu Persetujuan',
                                    'description' => 'Reservasi Anda sedang menunggu persetujuan dari admin.'
                                ],
                                'approved' => [
                                    'icon' => 'fas fa-check-circle',
                                    'title' => 'Disetujui',
                                    'description' => 'Reservasi Anda telah disetujui. Silakan lakukan pembayaran dalam waktu 3 menit.'
                                ],
                                'paid' => [
                                    'icon' => 'fas fa-money-bill-wave',
                                    'title' => 'Pembayaran Terverifikasi',
                                    'description' => 'Pembayaran Anda telah berhasil. Menunggu konfirmasi dari admin.'
                                ],
                                'completed' => [
                                    'icon' => 'fas fa-check-double',
                                    'title' => 'Selesai',
                                    'description' => 'Reservasi Anda telah selesai dan dikonfirmasi oleh admin.'
                                ],
                                'cancelled' => [
                                    'icon' => 'fas fa-times-circle',
                                    'title' => 'Dibatalkan',
                                    'description' => 'Reservasi Anda telah dibatalkan.'
                                ],
                                'rejected' => [
                                    'icon' => 'fas fa-ban',
                                    'title' => 'Ditolak',
                                    'description' => 'Reservasi Anda ditolak oleh admin.'
                                ],
                                'expired' => [
                                    'icon' => 'fas fa-hourglass-end',
                                    'title' => 'Kedaluwarsa',
                                    'description' => 'Waktu pembayaran telah habis.'
                                ]
                            ];
                        @endphp

                    <div class="timeline">
                            @foreach($statuses as $status => $info)
                                @php
                                    $isActive = $reservation->status === $status;
                                    $isCompleted = $reservation->status === 'completed' && in_array($status, ['pending', 'approved', 'paid']);
                                    $isCurrentStatus = $isActive || $isCompleted;
                                    $statusDates = [
                                        'approved' => $reservation->status_approved_at,
                                        'paid' => $reservation->status_paid_at,
                                        'completed' => $reservation->status_completed_at,
                                        'cancelled' => $reservation->status_cancelled_at,
                                        'rejected' => $reservation->status_rejected_at,
                                        'expired' => $reservation->status_expired_at,
                                    ];
                                @endphp
                                <div class="timeline-item {{ $isCurrentStatus ? ($isActive ? 'active' : 'completed') : '' }}">
                                    <div class="timeline-icon">
                                        <i class="{{ $info['icon'] }}"></i>
                        </div>
                                    <div class="timeline-content">
                                        <h5>{{ $info['title'] }}</h5>
                                        @if(isset($statusDates[$status]))
                                            <div class="timeline-date">
                                                {{ $statusDates[$status] ? \Carbon\Carbon::parse($statusDates[$status])->format('d M Y, H:i') : '-' }}
                        </div>
                                        @elseif($status === 'pending')
                                            <div class="timeline-date">
                                                {{ $reservation->created_at ? $reservation->created_at->format('d M Y, H:i') : '-' }}
                        </div>
                        @endif
                                        <p>{{ $info['description'] }}</p>
                                        @if($status === 'approved' && $reservation->status === 'approved')
                                            <div class="text-warning">
                                                <i class="fas fa-exclamation-circle"></i>
                                                Batas pembayaran: {{ $reservation->payment_expired_at->format('d M Y H:i') }}
                        </div>
                        @endif
                                        @if($status === 'paid' && $reservation->status === 'paid' && !$isCompleted)
                                            <div class="text-info">
                                                <i class="fas fa-info-circle"></i>
                                                Menunggu konfirmasi pembayaran dari admin
                        </div>
                        @endif
                            </div>
                        </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="detail-footer">
            <div class="price">Rp {{ number_format($reservation->total_price, 0, ',', '.') }}</div>
            <div class="d-flex gap-2">
                @if($reservation->status === 'approved')
                <a href="{{ route('customer.reservation.pay', $reservation->id) }}" class="btn btn-success btn-action">
                    <i class="fas fa-credit-card"></i>Bayar
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

                @if(in_array($reservation->status, ['paid', 'completed']))
                <a href="{{ route('customer.transaction.index') }}" class="btn btn-info btn-action">
                    <i class="fas fa-history"></i>Riwayat Transaksi
                </a>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
