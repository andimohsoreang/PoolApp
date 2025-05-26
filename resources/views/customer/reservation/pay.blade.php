@extends('layouts.customer')

@section('title', 'Pembayaran Reservasi')

@section('styles')
<link rel="stylesheet" href="{{ asset('Travgo/preview/assets/css/home.css') }}">
<style>
.payment-card {
    border-radius: 16px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.04);
    background: #fff;
    margin-bottom: 24px;
    padding: 24px;
}
.payment-header {
    text-align: center;
    margin-bottom: 24px;
}
.payment-amount {
    font-size: 2rem;
    font-weight: 600;
    color: #333;
    margin: 16px 0;
}
.payment-details {
    background: #f8f9fa;
    border-radius: 12px;
    padding: 16px;
    margin-bottom: 24px;
}
.payment-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 8px;
    padding-bottom: 8px;
    border-bottom: 1px solid #dee2e6;
}
.payment-row:last-child {
    margin-bottom: 0;
    padding-bottom: 0;
    border-bottom: none;
}
.payment-timer {
    text-align: center;
    margin-bottom: 24px;
    padding: 16px;
    background: #fff3cd;
    border-radius: 8px;
    color: #856404;
}
.payment-timer h4 {
    margin: 0;
    font-size: 1.2rem;
}
.payment-timer .countdown {
    font-size: 1.5rem;
    font-weight: 600;
    margin-top: 8px;
}
</style>
@endsection

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Pembayaran Reservasi</h2>
        <a href="{{ route('customer.transaction.index') }}" class="btn btn-outline-primary">
            <i class="fas fa-arrow-left me-2"></i>Kembali
        </a>
    </div>

    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="payment-card">
                <div class="payment-header">
                    <h3>Detail Pembayaran</h3>
                    <div class="payment-amount">
                        Rp {{ number_format($reservation->total_price, 0, ',', '.') }}
                    </div>
                    <p class="text-muted">Order ID: RES-{{ $reservation->id }}</p>
                </div>

                <div class="payment-timer">
                    <h4>Waktu Pembayaran</h4>
                    <div class="countdown" id="countdown">03:00</div>
                    <p class="mb-0">Silakan selesaikan pembayaran sebelum waktu habis</p>
                </div>

                <div class="payment-details">
                    <div class="payment-row">
                        <span>Nama</span>
                        <span>{{ $reservation->customer->name }}</span>
                    </div>
                    <div class="payment-row">
                        <span>Email</span>
                        <span>{{ $reservation->customer->email }}</span>
                    </div>
                    <div class="payment-row">
                        <span>Meja</span>
                        <span>Meja #{{ $reservation->table->table_number }} - {{ $reservation->table->room->name }}</span>
                    </div>
                    <div class="payment-row">
                        <span>Waktu</span>
                        <span>{{ $reservation->start_time->format('d M Y H:i') }} - {{ $reservation->end_time->format('H:i') }}</span>
                    </div>
                    <div class="payment-row">
                        <span>Durasi</span>
                        <span>{{ $reservation->duration_hours }} jam</span>
                    </div>
                    <div class="payment-row">
                        <span>Harga per Jam</span>
                        <span>Rp {{ number_format($reservation->price_per_hour, 0, ',', '.') }}</span>
                    </div>
                </div>

                <button id="pay-button" class="btn btn-primary w-100">
                    Bayar Sekarang
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ config('services.midtrans.client_key') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Countdown timer
    let timeLeft = 3 * 60; // 3 minutes in seconds
    const countdownElement = document.getElementById('countdown');

    const countdown = setInterval(() => {
        const minutes = Math.floor(timeLeft / 60);
        const seconds = timeLeft % 60;
        countdownElement.textContent = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;

        if (timeLeft <= 0) {
            clearInterval(countdown);
            window.location.href = '{{ route("customer.reservation.index") }}';
        }
        timeLeft--;
    }, 1000);

    // Handle payment button click
    document.getElementById('pay-button').addEventListener('click', function() {
        snap.pay('{{ $snapToken }}', {
            onSuccess: function(result) {
                // Send payment result to server
                fetch('{{ route('customer.reservation.payment-callback', $reservation->id) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify(result)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Pembayaran Berhasil',
                            text: 'Pembayaran Anda telah berhasil. Menunggu konfirmasi dari admin.',
                            confirmButtonText: 'OK',
                            allowOutsideClick: false
                        }).then(() => {
                            window.location.href = '{{ route('customer.reservation.history') }}';
                        });
                    } else {
                        throw new Error(data.message || 'Terjadi kesalahan saat memproses pembayaran');
                    }
                })
                .catch(err => {
                    console.error('Payment callback error:', err);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Gagal mengirim status pembayaran ke server.',
                    });
                });
            },
            onPending: function(result) {
                Swal.fire({
                    icon: 'info',
                    title: 'Pembayaran Pending',
                    text: 'Silakan selesaikan pembayaran Anda.',
                });
            },
            onError: function(result) {
                Swal.fire({
                    icon: 'error',
                    title: 'Pembayaran Gagal',
                    text: 'Terjadi kesalahan saat memproses pembayaran.',
                });
            },
            onClose: function() {
                Swal.fire({
                    icon: 'info',
                    title: 'Pembayaran Dibatalkan',
                    text: 'Anda menutup popup tanpa menyelesaikan pembayaran.',
                });
            }
        });
    });
});
</script>
@if(isset($expired) && $expired)
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: 'error',
                title: 'Reservasi Expired',
                text: 'Reservasi Anda sudah expired. Silakan lakukan pemesanan ulang.',
                confirmButtonText: 'Kembali ke Dashboard',
                allowOutsideClick: false,
            }).then(function() {
                window.location.href = '{{ route('customer.transaction.index') }}';
            });
        });
    </script>
@endif
@endsection
