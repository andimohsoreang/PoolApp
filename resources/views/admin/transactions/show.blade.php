@extends('layouts.app')

@section('title', 'Detail Transaksi')

@push('links')
    <link href="{{ asset('dist/assets/libs/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css">
@endpush

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="page-title-box d-flex justify-content-between align-items-center">
                <h4 class="page-title">Detail Transaksi</h4>
                <div class="">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.transactions.index') }}">Transaksi</a></li>
                        <li class="breadcrumb-item active">Detail</li>
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
        <div class="col-lg-8">
            <!-- Transaction Details Card -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title">Informasi Transaksi</h4>
                    <div>
                        <span class="badge
                            @if($transaction->status == 'pending') bg-warning
                            @elseif($transaction->status == 'confirmed') bg-info
                            @elseif($transaction->status == 'paid') bg-success
                            @elseif($transaction->status == 'completed') bg-primary
                            @elseif($transaction->status == 'cancelled') bg-danger
                            @endif">
                            {{ ucfirst($transaction->status) }}
                        </span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <tr>
                                <th width="200">Kode Transaksi</th>
                                <td>{{ $transaction->transaction_code }}</td>
                            </tr>
                            <tr>
                                <th>Tipe Transaksi</th>
                                <td>{{ ucfirst($transaction->transaction_type) }}</td>
                            </tr>
                            <tr>
                                <th>Customer</th>
                                <td>
                                    {{ $transaction->customer->name }}<br>
                                    <small class="text-muted">{{ $transaction->customer->phone }}</small><br>
                                    <small class="text-muted">{{ $transaction->customer->email }}</small>
                                </td>
                            </tr>
                            <tr>
                                <th>Meja</th>
                                <td>{{ $transaction->table->table_number }} ({{ $transaction->table->room->name }})</td>
                            </tr>
                            <tr>
                                <th>Jadwal</th>
                                <td>
                                    {{ \Carbon\Carbon::parse($transaction->start_time)->format('d M Y') }}<br>
                                    {{ \Carbon\Carbon::parse($transaction->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($transaction->end_time)->format('H:i') }}
                                </td>
                            </tr>
                            <tr>
                                <th>Disetujui Oleh</th>
                                <td>
                                    {{ $transaction->user->name }}<br>
                                    <small class="text-muted">{{ $transaction->created_at->format('d/m/Y H:i') }}</small>
                                </td>
                            </tr>
                        </table>
                    </div>

                    <div class="table-responsive mt-4">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Durasi</th>
                                    <th>Harga Per Jam</th>
                                    <th>Subtotal</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($transaction->details as $detail)
                                <tr>
                                    <td>{{ $detail->duration_hours }} jam</td>
                                    <td>Rp {{ number_format($detail->price_per_hour ?: $transaction->price_per_hour, 0, ',', '.') }}</td>
                                    <td>Rp {{ number_format($detail->subtotal ?: ($detail->price_per_hour * $detail->duration_hours), 0, ',', '.') }}</td>
                                    <td>Rp {{ number_format($detail->subtotal ?: ($detail->price_per_hour * $detail->duration_hours), 0, ',', '.') }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td>{{ $transaction->duration_hours }} jam</td>
                                    <td>Rp {{ number_format($transaction->price_per_hour, 0, ',', '.') }}</td>
                                    <td>Rp {{ number_format($transaction->price_per_hour * $transaction->duration_hours, 0, ',', '.') }}</td>
                                    <td>Rp {{ number_format($transaction->total_price, 0, ',', '.') }}</td>
                                </tr>
                                @endforelse
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="3" class="text-end">Total</th>
                                    <td>Rp {{ number_format($transaction->total_price, 0, ',', '.') }}</td>
                                </tr>
                                @if($transaction->payment)
                                <tr>
                                    <th colspan="3" class="text-end">Dibayar</th>
                                    <td>Rp {{ number_format($transaction->payment->amount_paid, 0, ',', '.') }}</td>
                                </tr>
                                <tr>
                                    <th colspan="3" class="text-end">{{ $transaction->payment->payment_method == 'cash' ? 'Kembalian' : 'E-Payment' }}</th>
                                    <td>Rp {{ number_format($transaction->payment->change_amount, 0, ',', '.') }}</td>
                                </tr>
                                @endif
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Transaction Actions Card -->
            <div class="card mt-4">
                <div class="card-header">
                    <h4 class="card-title">Aksi Transaksi</h4>
                </div>
                <div class="card-body">
                    <div class="d-flex flex-wrap gap-2">
                        <a href="{{ route('admin.transactions.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-1"></i> Kembali
                        </a>

                        @if($transaction->status == 'pending')
                            <a href="{{ route('admin.transactions.edit', $transaction->id) }}" class="btn btn-primary">
                                <i class="fas fa-edit me-1"></i> Edit
                            </a>
                            <button type="button" class="btn btn-danger" id="cancelTransactionBtn">
                                <i class="fas fa-times me-1"></i> Batalkan
                            </button>
                        @endif

                        @if($transaction->status == 'paid')
                            <button type="button" class="btn btn-success" id="completeTransactionBtn">
                                <i class="fas fa-check me-1"></i> Selesaikan
                            </button>
                        @endif

                        @if($transaction->status == 'paid' || $transaction->status == 'completed')
                            <a href="{{ route('admin.transactions.generate-invoice', $transaction->id) }}" class="btn btn-info">
                                <i class="fas fa-file-invoice me-1"></i> Cetak Invoice
                            </a>
                        @endif
                    </div>

                    @if($transaction->status == 'pending')
                        <!-- Cancel Transaction Form -->
                        <form id="cancelTransactionForm" action="{{ route('admin.transactions.cancel', $transaction->id) }}" method="POST" class="d-none">
                            @csrf
                            @method('PUT')
                        </form>
                    @endif

                    @if($transaction->status == 'paid')
                        <!-- Complete Transaction Form -->
                        <form id="completeTransactionForm" action="{{ route('admin.transactions.complete', $transaction->id) }}" method="POST" class="d-none">
                            @csrf
                            @method('PUT')
                        </form>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Payment Details Card -->
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Detail Pembayaran</h4>
                </div>
                <div class="card-body">
                    @if($transaction->payment)
                        <div class="mb-4">
                            <h5 class="text-primary">Status Pembayaran</h5>
                            <p>
                                <span class="badge
                                    @if($transaction->payment->status == 'pending') bg-warning
                                    @elseif($transaction->payment->status == 'paid') bg-success
                                    @elseif($transaction->payment->status == 'failed') bg-danger
                                    @elseif($transaction->payment->status == 'refunded') bg-secondary
                                    @else bg-info
                                    @endif">
                                    {{ ucfirst($transaction->payment->status) }}
                                </span>
                            </p>

                            @if($transaction->payment->status == 'paid')
                                <h5 class="text-primary mt-4">Metode Pembayaran</h5>
                                <p>
                                    @if($transaction->payment->payment_method == 'cash')
                                        <i class="fas fa-money-bill-wave me-1"></i> Cash
                                    @else
                                        <i class="fas fa-credit-card me-1"></i> E-Payment
                                        @if($transaction->payment->midtrans_payment_type)
                                            <small class="d-block text-muted">{{ $transaction->payment->midtrans_payment_type }}</small>
                                        @endif
                                    @endif
                                </p>

                                <h5 class="text-primary mt-4">Detail Pembayaran</h5>
                                <div class="table-responsive">
                                    <table class="table table-borderless">
                                        <tr>
                                            <td>Total</td>
                                            <td>:</td>
                                            <td>Rp {{ number_format($transaction->payment->total_amount, 0, ',', '.') }}</td>
                                        </tr>
                                        <tr>
                                            <td>Dibayar</td>
                                            <td>:</td>
                                            <td>Rp {{ number_format($transaction->payment->amount_paid, 0, ',', '.') }}</td>
                                        </tr>
                                        <tr>
                                            <td>Kembalian</td>
                                            <td>:</td>
                                            <td>Rp {{ number_format($transaction->payment->change_amount, 0, ',', '.') }}</td>
                                        </tr>
                                    </table>
                                </div>

                                <h5 class="text-primary mt-4">Tanggal Pembayaran</h5>
                                <p>{{ $transaction->payment->payment_date ? $transaction->payment->payment_date->format('d F Y H:i') : '-' }}</p>

                                @if($transaction->payment->midtrans_reference)
                                    <h5 class="text-primary mt-4">Referensi</h5>
                                    <p>{{ $transaction->payment->midtrans_reference }}</p>
                                @endif
                            @endif
                        </div>
                    @endif

                    @if($transaction->status == 'pending')
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-1"></i> Transaksi ini belum dilakukan pembayaran.
                        </div>

                        <h5 class="mt-4 mb-3">Proses Pembayaran</h5>
                        <form action="{{ route('admin.transactions.process-payment', $transaction->id) }}" method="POST" id="paymentForm">
                            @csrf
                            @method('PUT')

                            <div class="mb-3">
                                <label for="payment_method" class="form-label">Metode Pembayaran <span class="text-danger">*</span></label>
                                <select class="form-select @error('payment_method') is-invalid @enderror" id="payment_method" name="payment_method" required>
                                    <option value="">-- Pilih Metode Pembayaran --</option>
                                    <option value="cash" {{ old('payment_method') == 'cash' ? 'selected' : '' }}>Cash</option>
                                    <option value="e_payment" {{ old('payment_method') == 'e_payment' ? 'selected' : '' }}>E-Payment</option>
                                </select>
                                @error('payment_method')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="amount_paid" class="form-label">Jumlah yang Dibayarkan <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" class="form-control @error('amount_paid') is-invalid @enderror" id="amount_paid" name="amount_paid" value="{{ old('amount_paid', $transaction->total_price) }}" required min="{{ $transaction->total_price }}">
                                    @error('amount_paid')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="change_amount" class="form-label">Kembalian</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="text" class="form-control" id="change_amount" readonly>
                                </div>
                            </div>

                            <div class="d-grid mt-4">
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-check-circle me-1"></i> Proses Pembayaran
                                </button>
                            </div>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('dist/assets/libs/sweetalert2/sweetalert2.min.js') }}"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Calculate change amount
            const amountPaidInput = document.getElementById('amount_paid');
            const changeAmountInput = document.getElementById('change_amount');

            if (amountPaidInput && changeAmountInput) {
                amountPaidInput.addEventListener('input', function() {
                    const totalPrice = {{ $transaction->total_price }};
                    const amountPaid = parseFloat(this.value) || 0;
                    const changeAmount = Math.max(0, amountPaid - totalPrice);

                    changeAmountInput.value = formatNumber(changeAmount);
                });

                // Trigger calculation on load
                amountPaidInput.dispatchEvent(new Event('input'));
            }

            // Handle cancel transaction button
            const cancelBtn = document.getElementById('cancelTransactionBtn');
            if (cancelBtn) {
                cancelBtn.addEventListener('click', function() {
                    Swal.fire({
                        title: 'Batalkan Transaksi?',
                        text: "Transaksi akan dibatalkan. Tindakan ini tidak dapat dibatalkan!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#3085d6',
                        confirmButtonText: 'Ya, batalkan!',
                        cancelButtonText: 'Tidak, kembali'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            document.getElementById('cancelTransactionForm').submit();
                        }
                    });
                });
            }

            // Handle complete transaction button
            const completeBtn = document.getElementById('completeTransactionBtn');
            if (completeBtn) {
                completeBtn.addEventListener('click', function() {
                    Swal.fire({
                        title: 'Selesaikan Transaksi?',
                        text: "Transaksi akan ditandai selesai.",
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#28a745',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Ya, selesaikan!',
                        cancelButtonText: 'Tidak, kembali'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            document.getElementById('completeTransactionForm').submit();
                        }
                    });
                });
            }
        });

        function formatNumber(num) {
            return new Intl.NumberFormat('id-ID').format(num);
        }
    </script>
@endpush
