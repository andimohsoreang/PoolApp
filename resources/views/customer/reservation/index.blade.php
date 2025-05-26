@extends('layouts.customer')

@section('title', 'Reservasi Meja')

@section('styles')
    <link rel="stylesheet" href="{{ asset('Travgo/preview/assets/css/common.css') }}">
    <link rel="stylesheet" href="{{ asset('Travgo/preview/assets/css/booking.css') }}">
    <!-- Flatpickr CSS from CDN -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link href="{{ asset('dist/assets/libs/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css">
<style>
        /* Additional custom styles */
        .content-wrapper {
            padding-top: 5rem; /* 80px - standard navbar height + extra spacing */
            padding-bottom: 2rem;
        }

        @media (max-width: 768px) {
            .content-wrapper {
                padding-top: 4rem; /* Slightly less padding on mobile */
            }
        }

        .date-selector {
            margin-bottom: 24px;
        }

        .table-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            margin-bottom: 16px;
            overflow: hidden;
            cursor: pointer;
        }

        .table-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 16px rgba(0,0,0,0.12);
        }

        .table-card.selected {
            border: 2px solid #4361ee;
        }

        .card-header-img {
            height: 120px;
            width: 100%;
            background-color: #f5f5f5;
            position: relative;
            overflow: hidden;
        }

        .card-header-img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .room-type {
            position: absolute;
            top: 10px;
            right: 10px;
            background: rgba(255,255,255,0.9);
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .capacity-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            background-color: #f8f9fa;
            color: #212529;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 12px;
            margin-top: 8px;
        }

        .table-price {
            font-weight: 600;
            color: #4361ee;
            font-size: 16px;
            margin-top: 12px;
        }

        .reserve-btn {
            margin-top: 16px;
            width: 100%;
        }

        .btn-primary {
            background-color: #4361ee;
            border-color: #4361ee;
        }

        .btn-primary:hover {
            background-color: #3651d4;
            border-color: #3651d4;
        }

        .info-alert {
            border-radius: 12px;
            padding: 12px 16px;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 12px;
            background-color: #e6f3ff;
            color: #0055cc;
        }

        .modal-content {
    border-radius: 16px;
            border: none;
            overflow: hidden;
        }

        .modal-header {
            border-bottom: 1px solid #e9ecef;
            padding: 16px 20px;
        }

        .modal-body {
            padding: 20px;
        }

        .modal-footer {
            border-top: 1px solid #e9ecef;
            padding: 16px 20px;
        }

        .form-label {
            font-weight: 500;
            margin-bottom: 4px;
            color: #495057;
        }

        .form-control-plaintext {
            padding: 8px 0;
            font-size: 15px;
        }

        /* Timeline styles */
        .timeline-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 0 15px rgba(0,0,0,0.05);
            padding: 16px;
            margin-bottom: 20px;
            overflow-x: auto;
            position: relative;
        }

        .timeline-hours {
            display: flex;
            height: 40px;
            background: #f8f9fa;
            border-radius: 8px;
            margin-bottom: 12px;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 2px 4px rgba(0,0,0,0.03);
            min-width: 960px;
        }

        .timeline-hour {
            flex: 1;
            text-align: center;
            font-size: 12px;
            font-weight: 500;
            padding: 10px 0;
            border-right: 1px dashed #dee2e6;
            color: #495057;
            min-width: 60px;
            background: white;
            transition: all 0.3s ease;
        }

        .timeline-hour:hover {
            background: #f1f5f9;
        }

        .timeline-slots {
            position: relative;
            min-height: 70px;
            padding: 15px 0;
            background: #f8fafc;
            border-radius: 8px;
            min-width: 960px;
        }

        .timeline-slot {
            position: absolute;
            height: 40px;
            border-radius: 8px;
            padding: 8px 12px;
            font-size: 12px;
            font-weight: 500;
            color: white;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            cursor: pointer;
            border: 1px solid rgba(255,255,255,0.2);
            z-index: 10;
        }

        .timeline-slot:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }

        .timeline-slot-booked {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        }

        .timeline-slot-selected {
            background: linear-gradient(135deg, #4361ee 0%, #3651d4 100%);
        }

        .timeline-slot-available {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        }

        .server-time {
            font-size: 20px;
            font-weight: 600;
            text-align: center;
            color: #4361ee;
            margin-bottom: 16px;
            letter-spacing: 1px;
        }

        .server-time-label {
            font-size: 12px;
            color: #6c757d;
            text-align: center;
            margin-bottom: 16px;
        }

        .price-info-modal {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 16px;
            margin-top: 16px;
        }

        .price-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
        }

        .price-total {
    font-weight: 600;
            color: #4361ee;
            font-size: 18px;
        }

        @media (max-width: 576px) {
            .table-card {
                margin-bottom: 14px;
            }

            .card-header-img {
                height: 100px;
            }

            .reservation-title {
                font-size: 20px;
            }

            .timeline-container {
                padding: 10px;
                margin: 0 -10px;
                border-radius: 8px;
            }

            .timeline-hours {
                min-width: 720px;
            }

            .timeline-slots {
                min-width: 720px;
            }

            .timeline-hour {
                min-width: 45px;
                font-size: 11px;
                padding: 8px 0;
            }

            .timeline-slot {
                height: 35px;
                font-size: 11px;
                padding: 6px 10px;
            }

            .server-time {
                font-size: 18px;
            }
}
</style>
@endsection

@section('content')
<div class="content-wrapper">
    <div class="container">
        <!-- Page Header -->
        <div class="d-flex align-items-center justify-content-between mb-24">
            <div>
                <h1 class="reservation-title">Reservasi Meja</h1>
                <p class="text-muted">Pilih meja yang tersedia untuk reservasi Anda</p>
            </div>
            <a href="{{ route('customer.transaction.index') }}" class="btn-primary d-flex align-items-center gap-8 radius-8">
                <i class="fas fa-history"></i> Riwayat
            </a>
        </div>

        <!-- Notifications Container -->
        <div id="notifications" class="notification-badge"></div>

        <!-- Date Picker Section -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title mb-3">Filter Reservasi</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="date" class="form-label">Pilih Tanggal</label>
                                    <input type="date" class="form-control" id="date" name="date" value="{{ request('date', date('Y-m-d')) }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="type" class="form-label">Tipe Ruangan</label>
                                    <select class="form-select" id="type" name="type">
                                        <option value="">Semua Ruangan</option>
                                        <option value="regular" {{ request('type') == 'regular' ? 'selected' : '' }}>Regular Room</option>
                                        <option value="vip" {{ request('type') == 'vip' ? 'selected' : '' }}>VIP Room</option>
                                        <option value="vvip" {{ request('type') == 'vvip' ? 'selected' : '' }}>VVIP Room</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tables Grid -->
        <div class="row g-3" id="tables-container">
            @foreach($tables as $table)
            <div class="col-12 col-sm-6 col-lg-4">
                <div class="table-card h-100">
                    <div class="card-header-img">
                        <img src="{{ asset('Travgo/preview/assets/images/home/item-' . (($loop->index % 2) + 1) . '.png') }}" alt="Table">
                        <div class="room-type">{{ $table->room->name }}</div>
                    </div>
                    <div class="p-3">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h5 class="mb-1">Meja #{{ $table->table_number }}</h5>
                                <div class="capacity-badge">
                                    <i class="fas fa-user"></i> {{ $table->capacity }} Orang
                                </div>
                            </div>
                        </div>

                        <div class="table-price">
                            Mulai dari Rp {{ number_format($table->price_per_hour ?? 50000, 0, ',', '.') }}/jam
                        </div>

                        <a href="{{ route('customer.reservation.create', ['table' => $table->id, 'date' => request('date', now()->format('Y-m-d'))]) }}" class="btn btn-primary reserve-btn">
                            <i class="fas fa-calendar-plus me-2"></i> Buat Reservasi
                        </a>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endsection

@section('scripts')
    <!-- Flatpickr JS from CDN -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="{{ asset('dist/assets/libs/sweetalert2/sweetalert2.min.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const dateInput = document.getElementById('date');
            const typeSelect = document.getElementById('type');

            function updateUrl() {
                const date = dateInput.value;
                const type = typeSelect.value;
                let url = new URL(window.location.href);

                if (date) {
                    url.searchParams.set('date', date);
                } else {
                    url.searchParams.delete('date');
                }

                if (type) {
                    url.searchParams.set('type', type);
                } else {
                    url.searchParams.delete('type');
                }

                window.location.href = url.toString();
            }

            dateInput.addEventListener('change', updateUrl);
            typeSelect.addEventListener('change', updateUrl);

            // Intercept reservation links to add proper error handling
            document.querySelectorAll('.reserve-btn').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    const href = this.getAttribute('href');

                    // Add table parameter if missing
                    let url = new URL(href, window.location.origin);
                    if (!url.searchParams.has('table') && !url.searchParams.has('table_id')) {
                        // Extract table ID from the button's parent
                        const urlParts = href.split('?');
                        const baseUrl = urlParts[0];
                        const tableId = baseUrl.split('/').pop();

                        if (!isNaN(tableId)) {
                            url.searchParams.set('table', tableId);
                        }
                    }

                    window.location.href = url.toString();
                });
            });
        });
    </script>
@endsection
