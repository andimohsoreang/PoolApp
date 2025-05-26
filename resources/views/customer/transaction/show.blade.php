@extends('layouts.customer')

@section('title', 'Detail Transaksi')

@section('styles')
    <link href="{{ asset('dist/assets/libs/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css">
    <link rel="stylesheet" href="{{ asset('Travgo/preview/assets/css/home.css') }}">
    <link href="{{ asset('css/customer/transaction-show.css') }}" rel="stylesheet">
@endsection

@section('content')
<div class="page-container">
    <!-- Jam Real-Time -->
    <div class="current-time" id="current-time" style="font-size: 1.5rem; font-weight: bold; margin-bottom: 20px;">
        <!-- Waktu saat ini akan ditampilkan di sini -->
    </div>

    <!-- Transaction Header -->
    <div class="transaction-header">
        <i class="fas fa-receipt header-icon"></i>
        <div class="header-content">
            <h1 class="header-title">Detail Transaksi</h1>
            <p class="header-subtitle">Informasi lengkap reservasi meja biliar Anda</p>

            <div class="transaction-id-display">
                <i class="fas fa-hashtag"></i>
                <span>RES-{{ $transaction->id }}</span>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-end mb-4">
        <a href="{{ route('customer.transaction.index') }}" class="btn btn-outline-secondary btn-icon">
            <i class="fas fa-arrow-left"></i>
            Kembali ke Daftar
        </a>
    </div>

    <!-- Status Card -->
    <div class="card mb-4">
        <div class="card-body d-flex flex-wrap justify-content-between align-items-center">
            <div>
                <div class="text-muted mb-2">Dibuat pada</div>
                <h5 class="mb-0">{{ $transaction->created_at->timezone('Asia/Makassar')->format('d M Y, H:i') }}</h5>
                <span class="badge bg-secondary mt-2">
                    {{ $transaction->source === 'reservation' ? 'Reservasi Online' : 'Walk-in/Admin' }}
                </span>
            </div>
            <div class="pt-3 pt-md-0 text-md-end">
                <div class="status-badge status-{{ $transaction->status }}">
                    <i class="fas {{
                        $transaction->status === 'pending' ? 'fa-hourglass-half' :
                        ($transaction->status === 'approved' ? 'fa-check-circle' :
                        ($transaction->status === 'paid' ? 'fa-credit-card' :
                        ($transaction->status === 'completed' ? 'fa-check-double' :
                        ($transaction->status === 'cancelled' ? 'fa-times-circle' :
                        ($transaction->status === 'rejected' ? 'fa-ban' :
                        ($transaction->status === 'expired' ? 'fa-calendar-times' : 'fa-exclamation-circle'))))))
                    }}"></i>
                    <span>{{ ucfirst($transaction->status) }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Status Message Alert -->
    @if($transaction->status === 'approved')
    <div class="alert alert-custom alert-warning">
        <i class="fas fa-exclamation-triangle fa-lg"></i>
        <div class="alert-content">
            <h5 class="alert-heading">Menunggu Pembayaran</h5>
            <p class="mb-0">Segera lakukan pembayaran sebelum <strong>{{ $transaction->payment_expired_at->timezone('Asia/Makassar')->format('d M Y, H:i') }}</strong> untuk mengonfirmasi reservasi Anda.</p>
        </div>
    </div>
    @elseif($transaction->status === 'paid')
    <div class="alert alert-custom alert-success">
        <i class="fas fa-check-circle fa-lg"></i>
        <div class="alert-content">
            <h5 class="alert-heading">Pembayaran Berhasil</h5>
            <p class="mb-0">Reservasi Anda telah dikonfirmasi. Silakan datang sesuai waktu yang telah Anda pesan.</p>
        </div>
    </div>
    @elseif($transaction->status === 'expired')
    <div class="alert alert-custom alert-warning">
        <i class="fas fa-calendar-times fa-lg"></i>
        <div class="alert-content">
            <h5 class="alert-heading">Pembayaran Kedaluwarsa</h5>
            <p class="mb-0">Waktu pembayaran telah berakhir. Silakan buat reservasi baru jika masih ingin memesan meja.</p>
        </div>
    </div>
    @endif

    {{-- Countdown Timer --}}
    <div class="card countdown-card mb-4">
        <div class="card-body text-center">
            <h5 id="play-countdown">Memuat waktu...</h5>
        </div>
    </div>

    <!-- Price Card -->
    <div class="card price-card mb-4">
        <div class="card-body">
            <div class="price-label">Total Pembayaran</div>
            <div class="price-value">Rp {{ number_format($transaction->total_price, 0, ',', '.') }}</div>
            @if($transaction->duration_hours && $transaction->price_per_hour)
            <div class="price-details">{{ $transaction->duration_hours }} jam &times; Rp {{ number_format($transaction->price_per_hour, 0, ',', '.') }}/jam</div>
            @endif

            @if($transaction->status === 'approved')
            <a href="{{ route('customer.reservation.pay', $transaction->id) }}" class="btn btn-success btn-lg btn-icon mt-3">
                <i class="fas fa-credit-card"></i>
                Bayar Sekarang
            </a>
            @endif
        </div>
    </div>

    <div class="section-divider"></div>

    <!-- Main Info -->
    <div class="card mb-4">
        <div class="card-header">
            <h5><i class="fas fa-info-circle"></i>Informasi Reservasi</h5>
        </div>
        <div class="card-body">
            <div class="info-grid">
                <div class="info-item">
                    <div class="info-label">Tanggal & Waktu Mulai</div>
                    <div class="info-value" id="start-time"></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Tanggal & Waktu Selesai</div>
                    <div class="info-value" id="end-time"></div>
                </div>
                @if($transaction->duration_hours)
                <div class="info-item">
                    <div class="info-label">Durasi</div>
                    <div class="info-value">
                        <i class="fas fa-hourglass-half"></i>
                        {{ $transaction->duration_hours }} Jam
                    </div>
                </div>
                @endif
                @if($transaction->price_per_hour)
                <div class="info-item">
                    <div class="info-label">Harga per Jam</div>
                    <div class="info-value">
                        <i class="fas fa-tag"></i>
                        Rp {{ number_format($transaction->price_per_hour, 0, ',', '.') }}
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Location Info -->
    <div class="card mb-4">
        <div class="card-header">
            <h5><i class="fas fa-map-marker-alt"></i>Lokasi</h5>
        </div>
        <div class="card-body">
            <div class="info-grid">
                <div class="info-item">
                    <div class="info-label">Ruangan</div>
                    <div class="info-value">
                        <i class="fas fa-door-open"></i>
                        {{ $transaction->table->room->name }}
                    </div>
                </div>
                <div class="info-item">
                    <div class="info-label">Meja</div>
                    <div class="info-value">
                        <i class="fas fa-table"></i>
                        Meja {{ $transaction->table->table_number }}
                    </div>
                </div>
                <div class="info-item">
                    <div class="info-label">Tipe Meja</div>
                    <div class="info-value">
                        <i class="fas fa-billiard"></i>
                        {{ $transaction->table->brand ?? 'Standard' }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Timeline -->
    <div class="card mb-4">
        <div class="card-header">
            <h5><i class="fas fa-history"></i>Riwayat Status</h5>
        </div>
        <div class="card-body">
            <div class="timeline">
                <div class="timeline-item">
                    <div class="timeline-point"></div>
                    <div class="timeline-content">
                        <div class="timeline-title">Reservasi dibuat</div>
                        <div class="timeline-date" id="timeline-created"></div>
                    </div>
                </div>

                @if($transaction->status != 'pending')
                <div class="timeline-item">
                    <div class="timeline-point"></div>
                    <div class="timeline-content">
                        <div class="timeline-title">Reservasi disetujui</div>
                        <div class="timeline-date" id="timeline-approved"></div>
                    </div>
                </div>
                @endif

                @if($transaction->status === 'paid' || $transaction->status === 'completed')
                <div class="timeline-item status-completed">
                    <div class="timeline-point"></div>
                    <div class="timeline-content">
                        <div class="timeline-title">Pembayaran sukses</div>
                        <div class="timeline-date" id="timeline-paid"></div>
                    </div>
                </div>
                @endif

                @if($transaction->status === 'completed')
                <div class="timeline-item status-completed">
                    <div class="timeline-point"></div>
                    <div class="timeline-content">
                        <div class="timeline-title">Reservasi selesai</div>
                        <div class="timeline-date" id="timeline-completed"></div>
                    </div>
                </div>
                @endif

                @if($transaction->status === 'cancelled')
                <div class="timeline-item status-cancelled">
                    <div class="timeline-point"></div>
                    <div class="timeline-content">
                        <div class="timeline-title">Reservasi dibatalkan</div>
                        <div class="timeline-date" id="timeline-cancelled"></div>
                    </div>
                </div>
                @endif

                @if($transaction->status === 'rejected')
                <div class="timeline-item">
                    <div class="timeline-point"></div>
                    <div class="timeline-content">
                        <div class="timeline-title">Reservasi ditolak</div>
                        <div class="timeline-date" id="timeline-rejected"></div>
                        @if($transaction->reason)
                        <div class="timeline-reason mt-2"><strong>Alasan:</strong> {{ $transaction->reason }}</div>
                        @else
                        <div class="timeline-reason mt-2">Alasan: Tidak ada alasan yang diberikan</div>
                        @endif
                    </div>
                </div>
                @endif

                @if($transaction->status === 'expired')
                <div class="timeline-item">
                    <div class="timeline-point"></div>
                    <div class="timeline-content">
                        <div class="timeline-title">Pembayaran kedaluwarsa</div>
                        <div class="timeline-date" id="timeline-expired"></div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    @if($transaction->status === 'approved' || $transaction->status === 'pending')
    <div class="card action-buttons-container">
        <div class="action-buttons">
            <div class="d-flex justify-content-between align-items-center">
                <a href="{{ route('customer.transaction.index') }}" class="btn btn-outline-secondary btn-lg btn-icon">
                    <i class="fas fa-arrow-left"></i>
                    Kembali
                </a>

                <div class="d-flex gap-2">
                    @if($transaction->status === 'approved')
                    <a href="{{ route('customer.reservation.pay', $transaction->id) }}" class="btn btn-success btn-lg btn-icon">
                        <i class="fas fa-credit-card"></i>
                        Bayar Sekarang
                    </a>
                    @endif

                    @if($transaction->status === 'pending' || $transaction->status === 'approved')
                    <form action="{{ route('customer.reservation.cancel', $transaction->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan reservasi ini?')">
                        @csrf
                        <button type="submit" class="btn btn-danger btn-lg btn-icon">
                            <i class="fas fa-times-circle"></i>
                            Batalkan Reservasi
                        </button>
                    </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif

    @if($transaction->status === 'success' && $transaction->reservation)
    <a href="{{ route('customer.reservation.show', $transaction->reservation_id) }}" class="btn btn-primary btn-action">
        <i class="fas fa-eye"></i>Lihat Reservasi
    </a>
    @endif

    <a href="{{ route('customer.transaction.preview', $transaction->id) }}" class="btn btn-info btn-action">
        <i class="fas fa-receipt"></i>Lihat Nota
    </a>

    {{-- <div>
        Raw: {{ $transaction->start_time }}<br>
        Makassar: {{ $transaction->start_time->timezone('Asia/Makassar')->format('d-m-Y H:i:s') }}<br>
        Server now (Asia/Makassar): {{ now()->timezone('Asia/Makassar')->format('d-m-Y H:i:s') }}<br>
        Start: {{ $transaction->start_time->timezone('Asia/Makassar')->format('d-m-Y H:i:s') }}<br>
        End: {{ $transaction->end_time->timezone('Asia/Makassar')->format('d-m-Y H:i:s') }}<br>
        <span id="client-now"></span>
    </div> --}}
</div>
@endsection

@section('scripts')
    <script src="{{ asset('dist/assets/libs/sweetalert2/sweetalert2.min.js') }}"></script>
    <script>
        $(document).ready(function() {
            // Animations
            $(".transaction-header").addClass("animate__animated animate__fadeIn");
            $(".card").addClass("animate__animated animate__fadeInUp");

            // Status badge tooltip
            $('[data-toggle="tooltip"]').tooltip();
        });
    </script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Waktu mentah dari backend
        var startTimeRaw = "{{ $transaction->start_time->format('Y-m-d H:i:s') }}";
        var endTimeRaw = "{{ $transaction->end_time->format('Y-m-d H:i:s') }}";
        var createdAtRaw = "{{ $transaction->created_at->format('Y-m-d H:i:s') }}";
        var approvedAtRaw = "{{ $transaction->status_approved_at ? $transaction->status_approved_at->format('Y-m-d H:i:s') : $transaction->updated_at->format('Y-m-d H:i:s') }}";
        var paidAtRaw = "{{ $transaction->status_paid_at ? $transaction->status_paid_at->format('Y-m-d H:i:s') : $transaction->updated_at->format('Y-m-d H:i:s') }}";
        var completedAtRaw = "{{ $transaction->status_completed_at ? $transaction->status_completed_at->format('Y-m-d H:i:s') : $transaction->updated_at->format('Y-m-d H:i:s') }}";
        var cancelledAtRaw = "{{ $transaction->status_cancelled_at ? $transaction->status_cancelled_at->format('Y-m-d H:i:s') : $transaction->updated_at->format('Y-m-d H:i:s') }}";
        var rejectedAtRaw = "{{ $transaction->status_rejected_at ? $transaction->status_rejected_at->format('Y-m-d H:i:s') : $transaction->updated_at->format('Y-m-d H:i:s') }}";
        var expiredAtRaw = "{{ $transaction->status_expired_at ? $transaction->status_expired_at->format('Y-m-d H:i:s') : $transaction->updated_at->format('Y-m-d H:i:s') }}";

        function formatClientTime(raw) {
            var date = new Date(raw.replace(' ', 'T'));
            return date.toLocaleString('id-ID', {
                year: 'numeric', month: 'short', day: '2-digit',
                hour: '2-digit', minute: '2-digit'
            });
        }
        // Tampilkan waktu di info utama
        document.getElementById('start-time').textContent = formatClientTime(startTimeRaw);
        document.getElementById('end-time').textContent = formatClientTime(endTimeRaw);
        // Timeline
        document.getElementById('timeline-created').textContent = formatClientTime(createdAtRaw);
        if(document.getElementById('timeline-approved')) document.getElementById('timeline-approved').textContent = formatClientTime(approvedAtRaw);
        if(document.getElementById('timeline-paid')) document.getElementById('timeline-paid').textContent = formatClientTime(paidAtRaw);
        if(document.getElementById('timeline-completed')) document.getElementById('timeline-completed').textContent = formatClientTime(completedAtRaw);
        if(document.getElementById('timeline-cancelled')) document.getElementById('timeline-cancelled').textContent = formatClientTime(cancelledAtRaw);
        if(document.getElementById('timeline-rejected')) document.getElementById('timeline-rejected').textContent = formatClientTime(rejectedAtRaw);
        if(document.getElementById('timeline-expired')) document.getElementById('timeline-expired').textContent = formatClientTime(expiredAtRaw);

        // Countdown
        var countdownEl = document.getElementById('play-countdown');
        var currentTimeEl = document.getElementById('current-time');
        var startTimestamp = new Date(startTimeRaw.replace(' ', 'T')).getTime();
        var endTimestamp = new Date(endTimeRaw.replace(' ', 'T')).getTime();

        function pad(num) { return num.toString().padStart(2, '0'); }
        function formatDateTime(dt) {
            return `${pad(dt.getHours())}:${pad(dt.getMinutes())}:${pad(dt.getSeconds())}, ${pad(dt.getDate())}-${pad(dt.getMonth()+1)}-${dt.getFullYear()} WITA`;
        }

        var interval = setInterval(function() {
            let now = new Date();
            if (currentTimeEl) currentTimeEl.textContent = formatDateTime(now);

            let nowTime = now.getTime();
            let diff, label;
            if (nowTime < startTimestamp) {
                diff = startTimestamp - nowTime;
                label = 'Dimulai dalam';
            } else if (nowTime >= startTimestamp && nowTime < endTimestamp) {
                diff = endTimestamp - nowTime;
                label = 'Sisa waktu permainan';
            } else {
                clearInterval(interval);
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
        }, 1000);
    });
    </script>
    <script>
        setInterval(function() {
            let now = new Date();
            document.getElementById('client-now').textContent = 'Client now: ' + now.getDate() + '-' + (now.getMonth()+1) + '-' + now.getFullYear() + ' ' + now.getHours() + ':' + now.getMinutes() + ':' + now.getSeconds();
        }, 1000);
    </script>
@endsection
