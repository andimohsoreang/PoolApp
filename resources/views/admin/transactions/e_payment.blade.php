@extends('layouts.app')

@section('title', 'Pembayaran E-Payment')

@push('links')
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
@endpush

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="page-title-box d-md-flex justify-content-md-between align-items-center">
                <h4 class="page-title">Pembayaran E-Payment</h4>
                <div class="">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.transactions.index') }}">Transaksi</a></li>
                        <li class="breadcrumb-item active">Pembayaran E-Payment</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">Detail Pembayaran</h5>
                </div>
                <div class="card-body payment-body">
                    <div class="alert alert-info">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-info-circle me-2 fa-lg"></i>
                            <div>
                                <h6 class="mb-1">Status Pembayaran</h6>
                                <p class="mb-0">
                                    <span class="payment-status bg-warning text-white">MENUNGGU</span>
                                    <span class="ms-2">Menunggu pembayaran</span>
                                </p>
                            </div>
                        </div>
                    </div>

                    <div id="loading-container" class="text-center py-5">
                        <div class="spinner-border text-primary mb-3" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p>Memuat halaman pembayaran...</p>
                    </div>

                    <div id="manual-payment" class="text-center py-5" style="display: none;">
                        <p class="mb-4">Jika popup pembayaran tidak muncul otomatis, silakan klik tombol di bawah ini:</p>
                        <button id="pay-button" class="btn btn-primary btn-lg">
                            <i class="fas fa-credit-card me-2"></i> Bayar Sekarang
                        </button>
                    </div>

                    <div class="mt-4">
                        <h6>Informasi Transaksi</h6>
                        <table class="table table-bordered">
                            <tr>
                                <th width="200">Kode Transaksi</th>
                                <td>{{ $transaction->transaction_code }}</td>
                            </tr>
                            <tr>
                                <th>Customer</th>
                                <td>{{ $transaction->customer->name }}</td>
                            </tr>
                            <tr>
                                <th>Meja</th>
                                <td>Meja #{{ $transaction->table->table_number }} ({{ $transaction->table->room->name }})</td>
                            </tr>
                            <tr>
                                <th>Total Pembayaran</th>
                                <td><strong>Rp {{ number_format($transaction->total_price, 0, ',', '.') }}</strong></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">Petunjuk Pembayaran</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Perhatian!</strong> Jangan tutup halaman ini sebelum pembayaran selesai.
                    </div>

                    <h6 class="mb-3">Langkah-langkah Pembayaran:</h6>
                    <ol class="mb-0">
                        <li class="mb-2">Klik tombol "Bayar Sekarang"</li>
                        <li class="mb-2">Pilih metode pembayaran yang diinginkan</li>
                        <li class="mb-2">Ikuti instruksi pembayaran yang muncul</li>
                        <li class="mb-2">Tunggu konfirmasi pembayaran</li>
                        <li>Anda akan diarahkan ke halaman transaksi setelah pembayaran selesai</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <meta name="base-url" content="{{ url('/') }}">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ config('services.midtrans.snap_url') }}" data-client-key="{{ config('services.midtrans.client_key') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Simpan token snap di variabel
            const snapToken = '{{ $snapToken }}';

            // Tampilkan popup Midtrans setelah halaman dimuat
            setTimeout(function() {
                try {
                    snap.pay(snapToken, {
                        onSuccess: function(result) {
                            updatePaymentStatus('success', result);
                            showResultAndRedirect('success', 'Pembayaran Berhasil!',
                                'Anda akan dialihkan ke halaman transaksi.');
                        },
                        onPending: function(result) {
                            showResultAndRedirect('warning', 'Menunggu Pembayaran',
                                'Silakan selesaikan pembayaran melalui metode yang dipilih.');
                        },
                        onError: function(result) {
                            showPaymentError('Pembayaran Gagal',
                                'Terjadi kesalahan saat memproses pembayaran.');
                        },
                        onClose: function() {
                            showPaymentError('Pembayaran Dibatalkan',
                                'Anda menutup jendela pembayaran sebelum selesai.');
                        }
                    });

                    // Jika berhasil loading, tampilkan tombol manual setelah 5 detik
                    setTimeout(function() {
                        document.getElementById('loading-container').style.display = 'none';
                        document.getElementById('manual-payment').style.display = 'block';
                    }, 5000);

                } catch (e) {
                    console.error('Error opening Snap:', e);
                    document.getElementById('loading-container').style.display = 'none';
                    document.getElementById('manual-payment').style.display = 'block';

                    // Tambahkan alert error
                    const alertDiv = document.createElement('div');
                    alertDiv.className = 'alert alert-danger mt-3';
                    alertDiv.innerHTML =
                        '<i class="fas fa-exclamation-circle me-2"></i> Gagal memuat popup pembayaran: ' + e.message;
                    document.querySelector('.payment-body').insertBefore(alertDiv, document.getElementById('manual-payment'));
                }
            }, 1500);

            // Event listener untuk tombol bayar manual
            document.getElementById('pay-button').addEventListener('click', function() {
                try {
                    snap.pay(snapToken, {
                        onSuccess: function(result) {
                            updatePaymentStatus('success', result);
                            showResultAndRedirect('success', 'Pembayaran Berhasil!',
                                'Anda akan dialihkan ke halaman transaksi.');
                        },
                        onPending: function(result) {
                            showResultAndRedirect('warning', 'Menunggu Pembayaran',
                                'Silakan selesaikan pembayaran melalui metode yang dipilih.');
                        },
                        onError: function(result) {
                            showPaymentError('Pembayaran Gagal',
                                'Terjadi kesalahan saat memproses pembayaran.');
                        },
                        onClose: function() {
                            showPaymentError('Pembayaran Dibatalkan',
                                'Anda menutup jendela pembayaran sebelum selesai.');
                        }
                    });
                } catch (e) {
                    console.error('Error opening Snap:', e);
                    alert('Gagal memuat popup pembayaran: ' + e.message);
                }
            });

            // Fungsi untuk menampilkan hasil dan redirect
            function showResultAndRedirect(icon, title, text) {
                // Jika pembayaran berhasil, tunggu proses update status selesai
                if (icon === 'success') {
                    return; // Fungsi updatePaymentStatus akan menangani redirect
                }

                // Untuk kasus lain (pending, error)
                Swal.fire({
                    icon: icon,
                    title: title,
                    text: text,
                    timer: 3000,
                    showConfirmButton: false
                }).then(() => {
                    window.location.href = '/admin/transactions';
                });
            }

            // Fungsi untuk menampilkan error pembayaran
            function showPaymentError(title, text) {
                Swal.fire({
                    icon: 'error',
                    title: title,
                    text: text,
                    confirmButtonText: 'Coba Lagi',
                    showCancelButton: true,
                    cancelButtonText: 'Kembali'
                }).then((result) => {
                    if (result.dismiss === Swal.DismissReason.cancel) {
                        window.location.href = '/admin/transactions';
                    }
                });
            }

            function updatePaymentStatus(status, result) {
                // Log untuk debugging
                console.log('Updating payment status', status, result);

                if (status === 'success') {
                    // Perbarui tampilan alert
                    const paymentStatusAlert = document.querySelector('.alert');
                    paymentStatusAlert.className = 'alert alert-success';
                    paymentStatusAlert.innerHTML = `
                        <div class="d-flex align-items-center">
                            <i class="fas fa-check-circle me-2 fa-lg"></i>
                            <div>
                                <h6 class="mb-1">Status Pembayaran</h6>
                                <p class="mb-0">
                                    <span class="payment-status bg-success text-white">LUNAS</span>
                                    <span class="ms-2">Pembayaran berhasil</span>
                                </p>
                            </div>
                        </div>
                    `;

                    // Kirim AJAX untuk update status di database
                    const baseUrl = document.querySelector('meta[name="base-url"]').getAttribute('content');
                    const transactionId = '{{ $transaction->id }}';

                    // Ubah cara mengirim data
                    const formData = new FormData();
                    formData.append('transaction_id', transactionId);
                    formData.append('status', 'paid');
                    formData.append('result', JSON.stringify(result)); // Kirim hasil dari Midtrans

                    fetch(`${baseUrl}/admin/transactions/update-payment-status/${transactionId}`, {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: formData
                    })
                    .then(response => {
                        console.log('Update response:', response);
                        if (!response.ok) {
                            throw new Error('Network response was not ok: ' + response.status);
                        }
                        return response.json();
                    })
                    .then(data => {
                        console.log('Update success:', data);
                        if (data.success) {
                            // Arahkan ke halaman transaksi index
                            Swal.fire({
                                icon: 'success',
                                title: 'Pembayaran Berhasil!',
                                text: 'Anda akan dialihkan ke halaman transaksi.',
                                timer: 2000,
                                showConfirmButton: false
                            }).then(() => {
                                window.location.href = `${baseUrl}/admin/transactions`;
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Error updating payment status:', error);

                        // Tampilkan error tapi tetap arahkan ke halaman transaksi
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal Memperbarui Status',
                            text: 'Terjadi kesalahan, namun Anda tetap akan diarahkan ke daftar transaksi.',
                            timer: 3000
                        }).then(() => {
                            window.location.href = `${baseUrl}/admin/transactions`;
                        });
                    });
                }
            }
        });
    </script>
@endpush
