@extends('layouts.app')

@section('title', 'Konfirmasi Pembayaran Tunai')

@push('links')
<style>
    .payment-card {
        transition: all 0.3s ease;
    }
    .payment-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
</style>
@endpush

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="page-title-box d-md-flex justify-content-md-between align-items-center">
                <h4 class="page-title">Konfirmasi Pembayaran Tunai</h4>
                <div class="">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.walkin.index') }}">Walk-in</a></li>
                        <li class="breadcrumb-item active">Konfirmasi Pembayaran</li>
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

            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    <strong>Error!</strong> Ada kesalahan dalam data yang Anda masukkan.
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
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

        <div class="col-lg-4">
            <div class="card payment-card">
                <div class="card-header bg-success text-white">
                    <h5 class="card-title mb-0 text-white">Pembayaran Tunai</h5>
                </div>
                <div class="card-body">
                    <form id="cashPaymentForm" action="{{ route('admin.walkin.processCashPayment', ['id' => $transaction->id]) }}" method="POST">
                        @csrf

                        <div class="text-center mb-4">
                            <h2 class="mb-1">Rp {{ number_format($transaction->total_price, 0, ',', '.') }}</h2>
                            <p class="text-muted mb-0">Total yang harus dibayar</p>
                        </div>

                        <div class="mb-4">
                            <label for="amount_paid" class="form-label">Jumlah yang Dibayarkan</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" class="form-control form-control-lg" id="amount_paid" name="amount_paid" min="{{ $transaction->total_price }}" value="{{ $transaction->total_price }}" required>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Kembalian</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="text" class="form-control form-control-lg bg-light" id="change_amount" readonly>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col">
                                <div class="d-grid">
                                    <button type="button" class="btn btn-outline-primary preset-amount" data-amount="{{ $transaction->total_price }}">
                                        Pas
                                    </button>
                                </div>
                            </div>
                            <div class="col">
                                <div class="d-grid">
                                    <button type="button" class="btn btn-outline-primary preset-amount" data-amount="{{ $transaction->total_price + 10000 }}">
                                        +10rb
                                    </button>
                                </div>
                            </div>
                            <div class="col">
                                <div class="d-grid">
                                    <button type="button" class="btn btn-outline-primary preset-amount" data-amount="{{ $transaction->total_price + 50000 }}">
                                        +50rb
                                    </button>
                                </div>
                            </div>
                            <div class="col">
                                <div class="d-grid">
                                    <button type="button" class="btn btn-outline-primary preset-amount" data-amount="{{ $transaction->total_price + 100000 }}">
                                        +100rb
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="fas fa-check-circle me-2"></i> Konfirmasi Pembayaran
                            </button>
                            <a href="{{ route('admin.walkin.index') }}" class="btn btn-light">
                                Batalkan
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const amountPaidInput = document.getElementById('amount_paid');
        const changeAmountInput = document.getElementById('change_amount');
        const totalPrice = {{ $transaction->total_price }};

        // Update change amount when payment amount changes
        amountPaidInput.addEventListener('input', updateChangeAmount);

        // Set preset amounts
        document.querySelectorAll('.preset-amount').forEach(button => {
            button.addEventListener('click', function() {
                amountPaidInput.value = this.dataset.amount;
                updateChangeAmount();
            });
        });

        // Initial calculation
        updateChangeAmount();

        function updateChangeAmount() {
            const amountPaid = parseFloat(amountPaidInput.value) || 0;
            const changeAmount = amountPaid - totalPrice;

            // Format as currency
            const formatter = new Intl.NumberFormat('id-ID');
            changeAmountInput.value = formatter.format(Math.max(0, changeAmount));
        }
    });
</script>
@endpush
