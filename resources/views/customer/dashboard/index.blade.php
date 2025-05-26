@extends('layouts.customer')

@section('title', 'Dashboard')

@section('styles')
    <link rel="stylesheet" href="{{ asset('Travgo/preview/assets/css/home.css') }}">
    <style>
        /* Dashboard custom styles */
        .stat-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            padding: 16px;
            margin-bottom: 16px;
        }

        .stat-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
        }

        .status-badge {
            padding: 6px 12px;
            border-radius: 50px;
            font-size: 12px;
            font-weight: 500;
        }

        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }

        .status-approved {
            background-color: #cce5ff;
            color: #004085;
        }

        .status-paid {
            background-color: #d4edda;
            color: #155724;
        }

        .status-completed {
            background-color: #d1ecf1;
            color: #0c5460;
        }

        .status-cancelled {
            background-color: #f8d7da;
            color: #721c24;
        }

        .status-rejected {
            background-color: #e2e3e5;
            color: #383d41;
        }

        .status-expired {
            background-color: #e2e3e5;
            color: #383d41;
        }

        .reservation-card {
            background: white;
            border-radius: 12px;
            padding: 14px;
            margin-bottom: 16px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
        }

        .action-buttons {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .action-buttons .btn {
            padding: 6px 12px;
            font-size: 12px;
            border-radius: 6px;
        }

        .price-detail {
            font-size: 12px;
            color: #6c757d;
        }

        .place-card {
            margin-bottom: 16px;
        }

        .section-title {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 16px;
        }

        @media (max-width: 576px) {
            .action-buttons .btn {
                font-size: 11px;
                padding: 5px 10px;
            }
        }

        /* Tambahan styling untuk Recent Transactions */
        .recent-trans-list li {
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 4px 16px rgba(98,70,234,0.07);
            margin-bottom: 1.5rem;
            border: 1px solid #e5e7eb;
            transition: box-shadow 0.2s, transform 0.2s;
            padding: 1.2rem 1.5rem;
            display: flex;
            align-items: center;
            gap: 1.2rem;
        }
        .recent-trans-list li:hover {
            box-shadow: 0 8px 32px rgba(98,70,234,0.13);
            transform: translateY(-2px) scale(1.01);
        }
        .recent-trans-list .image {
            width: 70px;
            height: 70px;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(98,70,234,0.08);
            background: #f3f4f6;
            border: 2px solid #f8fafc;
        }
        .recent-trans-list .image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .recent-trans-list .content {
            flex: 1;
            min-width: 0;
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 1.2rem;
        }
        .recent-trans-list .text-end {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            justify-content: flex-end;
            min-width: 110px;
        }
        .recent-trans-list .price {
            font-size: 1.15rem;
            font-weight: 700;
            color: #059669;
            margin-bottom: 0.7rem;
            margin-top: 0.2rem;
            text-align: right;
        }
        .recent-trans-list .action-buttons {
            display: flex;
            flex-direction: column;
            gap: 0.3rem;
            width: 100%;
        }
        .recent-trans-list .action-buttons .btn {
            padding: 0.45rem 1.1rem;
            border-radius: 8px;
            font-size: 0.97rem;
            font-weight: 600;
            margin-bottom: 0.2rem;
            min-width: 80px;
        }
        .recent-trans-list h4 {
            font-size: 1.1rem;
            font-weight: 700;
            color: #6246ea;
            margin-bottom: 0.2rem;
        }
        .recent-trans-list h5 {
            font-size: 0.98rem;
            color: #64748b;
            margin-bottom: 0.2rem;
        }
        .recent-trans-list .status-badge {
            padding: 0.4rem 1rem;
            border-radius: 50px;
            font-size: 0.95rem;
            font-weight: 600;
            box-shadow: 0 1px 4px rgba(98,70,234,0.07);
            margin-left: 0.5rem;
        }
        .recent-trans-list .recent-countdown {
            font-size: 0.98rem;
            color: #2563eb;
            margin-top: 0.2rem;
            font-weight: 500;
        }
        .recent-trans-list .location {
            font-size: 0.95rem;
            color: #334155;
            margin-bottom: 0.2rem;
        }
        @media (max-width: 768px) {
            .recent-trans-list li { flex-direction: column; align-items: flex-start; padding: 1rem; }
            .recent-trans-list .content { flex-direction: column; align-items: flex-start; gap: 0.5rem; }
            .recent-trans-list .text-end { align-items: flex-start; width: 100%; }
            .recent-trans-list .price { text-align: left; }
        }
    </style>
@endsection

@section('content')
    <!-- info start -->
    <section class="info d-flex align-items-start justify-content-between pb-12">
        <div class="d-flex align-items-center justify-content-between gap-14">
            <div class="image shrink-0 rounded-full overflow-hidden">
                <img src="{{ asset('Travgo/preview/assets/images/home/avatar.png') }}" alt="avatar"
                    class="w-100 h-100 object-fit-cover">
            </div>
            <div>
                <h3>Hi, {{ auth()->user()->name }}</h3>
                <p class="d-flex align-items-center gap-04">
                    <img src="{{ asset('Travgo/preview/assets/svg/map-marker.svg') }}" alt="icon">
                    Pool Reservation System
                </p>
            </div>
        </div>

        <ul class="d-flex align-items-center gap-16">
            <li>
                <a href="{{ route('customer.transaction.index') }}"
                    class="d-flex align-items-center justify-content-center rounded-full position-relative">
                    <img src="{{ asset('Travgo/preview/assets/svg/bell-black.svg') }}" alt="icon">
                    @if ($stats['pending'] > 0)
                        <span class="dot"></span>
                    @endif
                </a>
            </li>
        </ul>
    </section>
    <!-- info end -->

    <!-- search start -->
    <section class="search py-12">
        <form action="{{ route('customer.reservation.index') }}" method="GET">
            <div class="form-inner w-100 d-flex align-items-center gap-8 radius-24">
                <img src="{{ asset('Travgo/preview/assets/svg/search.svg') }}" alt="search" class="shrink-0">
                <input type="search" name="search" class="input-search input-field" placeholder="Search table...">
                <div class="filter shrink-0">
                    <a href="{{ route('customer.reservation.index') }}" class="d-flex align-items-center">
                        <img src="{{ asset('Travgo/preview/assets/svg/filter-black.svg') }}" alt="filter">
                    </a>
                </div>
            </div>
        </form>
    </section>
    <!-- search end -->

    <!-- Stats Cards -->
    <div class="row py-12">
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="d-flex align-items-center">
                    <div class="stat-icon bg-primary bg-opacity-10 text-primary me-3">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div>
                        <div class="stat-value">{{ $stats['pending'] }}</div>
                        <div class="stat-label">Pending</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="d-flex align-items-center">
                    <div class="stat-icon bg-success bg-opacity-10 text-success me-3">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div>
                        <div class="stat-value">{{ $stats['completed_transactions'] }}</div>
                        <div class="stat-label">Completed</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="d-flex align-items-center">
                    <div class="stat-icon bg-warning bg-opacity-10 text-warning me-3">
                        <i class="fas fa-credit-card"></i>
                    </div>
                    <div>
                        <div class="stat-value">Rp {{ number_format($stats['total_spent'], 0, ',', '.') }}</div>
                        <div class="stat-label">Total Spent</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="d-flex align-items-center">
                    <div class="stat-icon bg-info bg-opacity-10 text-info me-3">
                        <i class="fas fa-history"></i>
                    </div>
                    <div>
                        <div class="stat-value">{{ $stats['total_transactions'] }}</div>
                        <div class="stat-label">Total Reservations</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- service start -->
    <section class="service py-12">
        <!-- Regular Tables -->
        <a href="{{ route('customer.reservation.index', ['type' => 'regular']) }}" class="room-filter-link">
            <figure class="item text-center">
                <div class="image rounded-full d-flex align-items-center justify-content-center m-auto">
                    <img src="{{ asset('Travgo/preview/assets/images/home/airport.png') }}" alt="Regular"
                        class="img-fluid backface-hidden">
                </div>
                <figcaption>Regular Room</figcaption>
            </figure>
        </a>

        <!-- VIP Tables -->
        <a href="{{ route('customer.reservation.index', ['type' => 'vip']) }}" class="room-filter-link">
            <figure class="item text-center">
                <div class="image rounded-full d-flex align-items-center justify-content-center m-auto">
                    <img src="{{ asset('Travgo/preview/assets/images/home/car-rental.png') }}" alt="VIP"
                        class="img-fluid backface-hidden">
                </div>
                <figcaption>VIP Room</figcaption>
            </figure>
        </a>

        <!-- VVIP Tables -->
        <a href="{{ route('customer.reservation.index', ['type' => 'vvip']) }}" class="room-filter-link">
            <figure class="item text-center">
                <div class="image rounded-full d-flex align-items-center justify-content-center m-auto">
                    <img src="{{ asset('Travgo/preview/assets/images/home/hotel.png') }}" alt="VVIP"
                        class="img-fluid backface-hidden">
                </div>
                <figcaption>VVIP Room</figcaption>
            </figure>
        </a>

        <!-- All Tables -->
        <a href="{{ route('customer.reservation.index') }}" class="room-filter-link">
            <figure class="item text-center">
                <div class="image rounded-full d-flex align-items-center justify-content-center m-auto">
                    <img src="{{ asset('Travgo/preview/assets/images/home/category.png') }}" alt="All Tables"
                        class="img-fluid backface-hidden">
                </div>
                <figcaption>All Tables</figcaption>
            </figure>
        </a>
    </section>
    <!-- service end -->

    <!-- Recent Bookings Start -->
    <section class="budget pt-12 pb-24">
        <!-- title -->
        <div class="title d-flex align-items-center justify-content-between">
            <h2 class="shrink-0">Recent Transactions</h2>
            <a href="{{ route('customer.transaction.index') }}" class="shrink-0 d-inline-block">See All</a>
        </div>

        <ul class="recent-trans-list">
            @forelse($recent_transactions as $transaction)
                <li>
                    <div class="image shrink-0 overflow-hidden radius-8">
                        <img src="{{ asset('Travgo/preview/assets/images/home/budget-1.png') }}" alt="Table" class="img-fluid w-100 h-100 object-fit-cover">
                    </div>
                    <div class="content shrink-0 d-flex align-items-center gap-12 justify-content-between flex-grow">
                        <div>
                            <h4>Table #{{ $transaction->table->table_number }}
                                <span class="status-badge status-{{ $transaction->status }}">{{ ucfirst($transaction->status) }}</span>
                                <span style="font-size:12px;color:#888;">[ID: {{ $transaction->id }}]</span>
                            </h4>
                            <h5>{{ $transaction->duration_text }}</h5>
                            <p class="d-flex align-items-center gap-8 location">
                                <img src="{{ asset('Travgo/preview/assets/svg/map-marker.svg') }}" alt="icon">
                                {{ $transaction->table->room->name }}
                            </p>
                            <div class="recent-countdown" id="countdown-{{ $transaction->id }}">Memuat waktu...</div>
                        </div>
                        <div class="text-end">
                            <p class="price"><span>Rp {{ number_format($transaction->total_price, 0, ',', '.') }}</span></p>
                            <div class="action-buttons mt-2">
                                <a href="{{ route('customer.transaction.show', $transaction->id) }}" class="btn btn-sm btn-primary">
                                    <i class="fas fa-eye"></i> Detail
                                </a>
                                @if ($transaction->status === 'approved')
                                    <a href="{{ route('customer.reservation.pay', $transaction->id) }}" class="btn btn-sm btn-success">
                                        <i class="fas fa-credit-card"></i> Pay
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </li>
            @empty
                <div class="text-center py-5">
                    <img src="{{ asset('dist/assets/images/no-data.svg') }}" alt="No Data" class="mb-4" style="max-width: 120px;">
                    <h5>No bookings yet</h5>
                    <p class="text-muted">You don't have any reservation history</p>
                    <a href="{{ route('customer.reservation.index') }}" class="btn btn-primary mt-3">
                        <i class="fas fa-plus me-1"></i> Book Now
                    </a>
                </div>
            @endforelse
        </ul>
    </section>
    <!-- Recent Bookings End -->

    <!-- Quick info card -->
    <div class="card mb-4">
        <div class="card-body">
            <h5 class="mb-3">Information</h5>
            <div class="alert alert-info mb-3">
                <h6 class="alert-heading">
                    <i class="fas fa-info-circle me-2"></i> Operating Hours
                </h6>
                <p>Monday - Sunday: 08:00 - 23:00</p>
            </div>
            <div class="alert alert-warning mb-0">
                <h6 class="alert-heading">
                    <i class="fas fa-exclamation-circle me-2"></i> Payment Deadline
                </h6>
                <p>Payments must be made within 3 minutes after reservation approval</p>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
    <script src="{{ asset('dist/assets/libs/sweetalert2/sweetalert2.min.js') }}"></script>
    <script>
        // Check for notifications
        function checkNotifications() {
            fetch('{{ route('customer.reservation.notifications') }}')
                .then(response => response.json())
                .then(notifications => {
                    if (notifications.length > 0) {
                        notifications.forEach(notification => {
                            Swal.fire({
                                title: 'Notification',
                                text: notification.message,
                                icon: notification.status === 'approved' ? 'success' : notification
                                    .status === 'rejected' ? 'error' : 'warning',
                                confirmButtonText: 'OK'
                            });
                        });
                    }
                });
        }

        // Swiper initialization for section sliders
        if (typeof Swiper !== 'undefined') {
            var swiper = new Swiper(".visited-swiper", {
                slidesPerView: 1.2,
                spaceBetween: 16,
                pagination: {
                    el: ".visited-pagination",
                    clickable: true,
                },
                breakpoints: {
                    640: {
                        slidesPerView: 2,
                        spaceBetween: 20,
                    },
                    768: {
                        slidesPerView: 2,
                        spaceBetween: 20,
                    },
                    1024: {
                        slidesPerView: 2,
                        spaceBetween: 20,
                    },
                },
            });
        }

        // Check notifications every 30 seconds
        setInterval(checkNotifications, 30000);
        checkNotifications();

        document.addEventListener('DOMContentLoaded', function() {
            @foreach ($recent_transactions as $transaction)
                (function() {
                    var startRaw = "{{ $transaction->start_time->format('Y-m-d H:i:s') }}";
                    var endRaw = "{{ $transaction->end_time->format('Y-m-d H:i:s') }}";
                    var countdownEl = document.getElementById('countdown-{{ $transaction->id }}');
                    var startTimestamp = new Date(startRaw.replace(' ', 'T')).getTime();
                    var endTimestamp = new Date(endRaw.replace(' ', 'T')).getTime();

                    function updateCountdown() {
                        let now = new Date();
                        let nowTime = now.getTime();
                        let diff, label;
                        if (nowTime < startTimestamp) {
                            diff = startTimestamp - nowTime;
                            label = 'Dimulai dalam';
                        } else if (nowTime >= startTimestamp && nowTime < endTimestamp) {
                            diff = endTimestamp - nowTime;
                            label = 'Sisa waktu permainan';
                        } else {
                            countdownEl.textContent = 'Waktu permainan telah berakhir';
                            return;
                        }
                        var totalSeconds = Math.floor(diff / 1000);
                        var hours = Math.floor(totalSeconds / 3600);
                        var minutes = Math.floor((totalSeconds % 3600) / 60);
                        var seconds = totalSeconds % 60;
                        var parts = [];
                        if (hours > 0) parts.push(hours + ' jam');
                        if (minutes > 0) parts.push(minutes + ' menit');
                        parts.push(seconds + ' detik');
                        countdownEl.textContent = label + ' ' + parts.join(' ');
                    }
                    updateCountdown();
                    setInterval(updateCountdown, 1000);
                })();
            @endforeach
        });
    </script>
@endsection

