@extends('layouts.app')

@section('title', 'Konfirmasi Pembayaran Elektronik')

@push('links')
<meta name="viewport" content="width=device-width, initial-scale=1">
<!-- SweetAlert2 CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.min.css">
<style>
    .payment-card {
        transition: all 0.3s ease;
    }
    .payment-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
    #snap-container {
        min-height: 500px;
    }
</style>
@endpush

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="page-title-box d-md-flex justify-content-md-between align-items-center">
                <h4 class="page-title">Konfirmasi Pembayaran Elektronik</h4>
                <div class="">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.walkin.index') }}">Walk-in</a></li>
                        <li class="breadcrumb-item active">Konfirmasi E-Payment</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Flash Message -->
    <div class="row">
        <div class="col-12">
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    <strong>Berhasil!</strong> {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    <strong>Error!</strong> {{ session('error') }}
                </div>
            @endif
        </div>
    </div>

    <div class="row">
        <div class="col-lg-7">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0 text-white">Detail Transaksi #{{ $transaction->transaction_code }}</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6 class="text-muted fw-normal mb-3">Informasi Customer:</h6>
                            <h5 class="mb-2">{{ $transaction->customer->name }}</h5>
                            <p class="mb-1"><i class="fas fa-phone-alt me-2 text-muted"></i> {{ $transaction->customer->phone }}</p>
                            @if($transaction->customer->email)
                            <p class="mb-0"><i class="fas fa-envelope me-2 text-muted"></i> {{ $transaction->customer->email }}</p>
                            @endif
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted fw-normal mb-3">Informasi Meja:</h6>
                            <h5 class="mb-2">Meja #{{ $transaction->table->table_number }}</h5>
                            <p class="mb-1"><i class="fas fa-map-marker-alt me-2 text-muted"></i> {{ $transaction->table->room->name }}</p>
                            <p class="mb-0"><i class="fas fa-tag me-2 text-muted"></i> {{ $transaction->table->brand }}</p>
                        </div>
                    </div>

                    <div class="table-responsive mb-4">
                        <table class="table table-bordered table-striped">
                            <thead class="table-light">
                                <tr>
                                    <th>Deskripsi</th>
                                    <th class="text-end">Detail</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Waktu Mulai</td>
                                    <td class="text-end">{{ $transaction->start_time->format('d M Y H:i') }}</td>
                                </tr>
                                <tr>
                                    <td>Waktu Selesai</td>
                                    <td class="text-end">{{ $transaction->end_time->format('d M Y H:i') }}</td>
                                </tr>
                                <tr>
                                    <td>Durasi</td>
                                    <td class="text-end">{{ $transaction->details->first()->duration_hours }} jam</td>
                                </tr>
                                <tr>
                                    <td>Harga per Jam</td>
                                    <td class="text-end">Rp {{ number_format($transaction->details->first()->price_per_hour, 0, ',', '.') }}</td>
                                </tr>
                                @if($transaction->details->first()->discount > 0)
                                <tr>
                                    <td>Subtotal</td>
                                    <td class="text-end">Rp {{ number_format($transaction->details->first()->subtotal + $transaction->details->first()->discount, 0, ',', '.') }}</td>
                                </tr>
                                <tr>
                                    <td>Diskon</td>
                                    <td class="text-end text-danger">-Rp {{ number_format($transaction->details->first()->discount, 0, ',', '.') }}</td>
                                </tr>
                                @endif
                                <tr class="table-primary">
                                    <td><strong>Total</strong></td>
                                    <td class="text-end"><strong>Rp {{ number_format($transaction->total_price, 0, ',', '.') }}</strong></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    @if($transaction->notes)
                    <div class="alert alert-info mb-4">
                        <h6 class="mb-1"><i class="fas fa-info-circle me-2"></i> Catatan:</h6>
                        <p class="mb-0">{{ $transaction->notes }}</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card payment-card">
                <div class="card-header bg-info text-white">
                    <h5 class="card-title mb-0 text-white">Pembayaran Elektronik</h5>
                </div>
                <div class="card-body">
                    <div class="text-center mb-4">
                        <h2 class="mb-1">Rp {{ number_format($transaction->total_price, 0, ',', '.') }}</h2>
                        <p class="text-muted mb-0">Total yang harus dibayar</p>
                    </div>

                    <div class="alert alert-warning">
                        <i class="fas fa-info-circle me-2"></i> Silakan pilih metode pembayaran di bawah ini.
                    </div>

                    <!-- Snap Container -->
                    <div id="snap-container"></div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<!-- SweetAlert2 JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.all.min.js"></script>
<!-- Midtrans Snap Library -->
<script type="text/javascript" src="{{ config('services.midtrans.snap_url') }}" data-client-key="{{ config('services.midtrans.client_key') }}"></script>
<script type="text/javascript">
    document.addEventListener('DOMContentLoaded', function() {
        // Embed Snap payment page
        window.snap.embed('{{ $snapToken }}', {
            embedId: 'snap-container',
            onSuccess: function(result) {
                // Handle success
                updatePaymentStatus('success', result);
            },
            onPending: function(result) {
                // Handle pending
                Swal.fire({
                    icon: 'warning',
                    title: 'Menunggu Pembayaran',
                    text: 'Silakan selesaikan pembayaran melalui metode yang dipilih.',
                    timer: 3000,
                    showConfirmButton: false
                }).then(() => {
                    window.location.href = "{{ route('admin.walkin.transactionSuccess', ['id' => $transaction->id]) }}";
                });
            },
            onError: function(result) {
                // Handle error
                Swal.fire({
                    icon: 'error',
                    title: 'Pembayaran Gagal',
                    text: 'Terjadi kesalahan saat memproses pembayaran.',
                    confirmButtonText: 'Coba Lagi',
                    showCancelButton: true,
                    cancelButtonText: 'Kembali'
                }).then((result) => {
                    if (result.dismiss === Swal.DismissReason.cancel) {
                        window.location.href = "{{ route('admin.walkin.transactionSuccess', ['id' => $transaction->id]) }}";
                    }
                });
            },
            onClose: function() {
                // Handle customer closed the popup without finishing the payment
                Swal.fire({
                    icon: 'warning',
                    title: 'Pembayaran Dibatalkan',
                    text: 'Anda menutup jendela pembayaran sebelum selesai.',
                    confirmButtonText: 'Coba Lagi',
                    showCancelButton: true,
                    cancelButtonText: 'Kembali'
                }).then((result) => {
                    if (result.dismiss === Swal.DismissReason.cancel) {
                        window.location.href = "{{ route('admin.walkin.transactionSuccess', ['id' => $transaction->id]) }}";
                    }
                });
            }
        });

        function updatePaymentStatus(status, result) {
            if (status === 'success') {
                // Kirim AJAX untuk update status di database
                const formData = new FormData();
                formData.append('transaction_id', '{{ $transaction->id }}');
                formData.append('status', 'paid');
                formData.append('result', JSON.stringify(result));

                fetch('{{ route('admin.transactions.update-payment-status', ['id' => $transaction->id]) }}', {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Pembayaran Berhasil!',
                            text: 'Anda akan dialihkan ke halaman transaksi.',
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            window.location.href = "{{ route('admin.walkin.transactionSuccess', ['id' => $transaction->id]) }}";
                        });
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal Memperbarui Status',
                        text: 'Terjadi kesalahan, namun Anda tetap akan diarahkan ke halaman transaksi.',
                        timer: 3000
                    }).then(() => {
                        window.location.href = "{{ route('admin.walkin.transactionSuccess', ['id' => $transaction->id]) }}";
                    });
                });
            }
        }
    });
</script>
@endpush
