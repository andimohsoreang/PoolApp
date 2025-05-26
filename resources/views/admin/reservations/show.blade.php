@extends('layouts.app')

@section('title', 'Detail Reservasi')

@push('links')
    <!-- Sweet Alert -->
    <link href="{{ asset('dist/assets/libs/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('dist/assets/libs/animate.css/animate.min.css') }}" rel="stylesheet" type="text/css">
@endpush

@section('styles')
    <style>
        .reservation-section-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 1.25rem;
            letter-spacing: 0.5px;
        }
        .reservation-card {
            border-radius: 16px;
            box-shadow: 0 4px 16px rgba(44,62,80,0.07);
            border: none;
            margin-bottom: 2rem;
        }
        .reservation-card .card-body {
            padding: 2rem 1.5rem;
        }
        .reservation-badge {
            font-size: 1rem;
            padding: 0.5rem 1.25rem;
            border-radius: 20px;
            font-weight: 600;
        }
        .reservation-info-label {
            color: #888;
            font-size: 0.97rem;
            margin-bottom: 0.2rem;
        }
        .reservation-info-value {
            font-size: 1.08rem;
            font-weight: 500;
            color: #222;
            margin-bottom: 0.7rem;
        }
        .reservation-action-bar {
            display: flex;
            gap: 1rem;
            margin-top: 1.5rem;
        }
        .reservation-action-bar .btn {
            min-width: 120px;
            font-weight: 600;
            font-size: 1rem;
            border-radius: 8px;
        }
        .reservation-payment-card {
            background: #f8fafc;
            border: 1.5px solid #e2e8f0;
        }
        .reservation-status-card {
            background: #f6f9ff;
            border: 1.5px solid #dbeafe;
        }
        /* Timeline Styles */
        .timeline {
            position: relative;
            padding: 20px 0;
        }
        .timeline::before {
            content: '';
            position: absolute;
            left: 15px;
            top: 0;
            bottom: 0;
            width: 2px;
            background: #e2e8f0;
        }
        .timeline-item {
            position: relative;
            padding-left: 45px;
            margin-bottom: 20px;
        }
        .timeline-item:last-child {
            margin-bottom: 0;
        }
        .timeline-icon {
            position: absolute;
            left: 0;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: #f7fafc;
            border: 2px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #a0aec0;
            transition: all 0.3s ease;
        }
        .timeline-item.active .timeline-icon {
            background: #6246ea;
            border-color: #6246ea;
            color: white;
        }
        .timeline-item.completed .timeline-icon {
            background: #10b981;
            border-color: #10b981;
            color: white;
        }
        .timeline-content {
            background: #f8fafc;
            padding: 15px;
            border-radius: 6px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }
        .timeline-title {
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 5px;
        }
        .timeline-date {
            font-size: 0.875rem;
            color: #718096;
        }
        .timeline-reason {
            margin-top: 8px;
            font-size: 0.875rem;
            color: #4a5568;
        }
        @media (max-width: 768px) {
            .reservation-card .card-body {
                padding: 1.25rem 0.75rem;
            }
            .reservation-section-title {
                font-size: 1.1rem;
            }
        }
    </style>
@endsection

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="page-title-box d-md-flex justify-content-md-between align-items-center">
                <h4 class="page-title">Detail Reservasi</h4>
                <div class="">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.reservations.index') }}">Reservasi</a></li>
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
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="row align-items-center">
                        <div class="col">
                            <h4 class="card-title">Informasi Reservasi</h4>
                        </div>
                        <div class="col-auto">
                            <a href="{{ route('admin.reservations.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-1"></i> Kembali
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Status and Timeline Section -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card reservation-card reservation-status-card">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h5 class="reservation-section-title mb-0">Status Reservasi</h5>
                                        <span class="badge bg-{{
                                            $reservation->status == 'pending' ? 'warning' :
                                            ($reservation->status == 'approved' ? 'info' :
                                            ($reservation->status == 'paid' ? 'success' :
                                            ($reservation->status == 'completed' ? 'primary' :
                                            ($reservation->status == 'cancelled' ? 'danger' :
                                            ($reservation->status == 'rejected' ? 'secondary' :
                                            ($reservation->status == 'expired' ? 'secondary' : 'secondary'))))))
                                        }} reservation-badge">
                                            {{ ucfirst($reservation->status) }}
                                            @if($reservation->status == 'completed' && $reservation->status_completed_at)
                                                <small class="ms-1">({{ $reservation->status_completed_at->format('d M Y H:i') }})</small>
                                            @endif
                                        </span>
                                    </div>
                                    <ul class="list-unstyled ms-2 position-relative" style="border-left: 3px solid #e2e8f0;">
                                        <li class="mb-4 position-relative">
                                            <span class="position-absolute top-0 start-0 translate-middle bg-white border border-2 rounded-circle" style="width:18px;height:18px;left:-10px;"><i class="fas fa-file-alt text-dark small d-flex justify-content-center align-items-center" style="font-size:12px;"></i></span>
                                            <div class="ms-4">
                                                <div class="fw-bold">Reservasi dibuat</div>
                                                <div class="text-muted small">{{ $reservation->created_at->format('d M Y, H:i') }}</div>
                                            </div>
                                        </li>
                                        <li class="mb-4 position-relative">
                                            <span class="position-absolute top-0 start-0 translate-middle bg-white border border-2 rounded-circle {{ $reservation->status == 'approved' || in_array($reservation->status, ['paid','completed']) ? 'border-info' : '' }}" style="width:18px;height:18px;left:-10px;"><i class="fas fa-check-circle text-info small d-flex justify-content-center align-items-center" style="font-size:12px;"></i></span>
                                            <div class="ms-4">
                                                <div class="fw-bold">Reservasi disetujui</div>
                                                <div class="text-muted small">{{ $reservation->status_approved_at ? \Carbon\Carbon::parse($reservation->status_approved_at)->format('d M Y, H:i') : '-' }}</div>
                                            </div>
                                        </li>
                                        <li class="mb-4 position-relative">
                                            <span class="position-absolute top-0 start-0 translate-middle bg-white border border-2 rounded-circle {{ in_array($reservation->status, ['paid','completed']) ? 'border-success' : '' }}" style="width:18px;height:18px;left:-10px;"><i class="fas fa-credit-card text-success small d-flex justify-content-center align-items-center" style="font-size:12px;"></i></span>
                                            <div class="ms-4">
                                                <div class="fw-bold">Pembayaran sukses</div>
                                                <div class="text-muted small">{{ $reservation->status_paid_at ? \Carbon\Carbon::parse($reservation->status_paid_at)->format('d M Y, H:i') : '-' }}</div>
                                            </div>
                                        </li>
                                        <li class="mb-4 position-relative">
                                            <span class="position-absolute top-0 start-0 translate-middle bg-white border border-2 rounded-circle {{ $reservation->status == 'completed' ? 'border-primary' : '' }}" style="width:18px;height:18px;left:-10px;"><i class="fas fa-check-double text-primary small d-flex justify-content-center align-items-center" style="font-size:12px;"></i></span>
                                            <div class="ms-4">
                                                <div class="fw-bold">Reservasi selesai</div>
                                                <div class="text-muted small">
                                                    @if($reservation->status_completed_at)
                                                        {{ $reservation->status_completed_at->format('d M Y, H:i') }}
                                                        @if($reservation->status == 'completed')
                                                            <span class="badge bg-primary ms-2">Completed</span>
                                                        @endif
                                                    @else
                                                        -
                                                    @endif
                                                </div>
                                            </div>
                                        </li>
                                        @if($reservation->status == 'cancelled')
                                        <li class="mb-4 position-relative">
                                            <span class="position-absolute top-0 start-0 translate-middle bg-white border border-2 rounded-circle border-danger" style="width:18px;height:18px;left:-10px;"><i class="fas fa-times-circle text-danger small d-flex justify-content-center align-items-center" style="font-size:12px;"></i></span>
                                            <div class="ms-4">
                                                <div class="fw-bold">Reservasi dibatalkan</div>
                                                <div class="text-muted small">{{ $reservation->status_cancelled_at ? \Carbon\Carbon::parse($reservation->status_cancelled_at)->format('d M Y, H:i') : '-' }}</div>
                                                @if($reservation->reason)
                                                <div class="text-danger small"><strong>Alasan:</strong> {{ $reservation->reason }}</div>
                                                @endif
                                            </div>
                                        </li>
                                        @endif
                                        @if($reservation->status == 'rejected')
                                        <li class="mb-4 position-relative">
                                            <span class="position-absolute top-0 start-0 translate-middle bg-white border border-2 rounded-circle border-secondary" style="width:18px;height:18px;left:-10px;"><i class="fas fa-ban text-secondary small d-flex justify-content-center align-items-center" style="font-size:12px;"></i></span>
                                            <div class="ms-4">
                                                <div class="fw-bold">Reservasi ditolak</div>
                                                <div class="text-muted small">{{ $reservation->status_rejected_at ? \Carbon\Carbon::parse($reservation->status_rejected_at)->format('d M Y, H:i') : '-' }}</div>
                                                @if($reservation->rejection_reason)
                                                <div class="text-danger small"><strong>Alasan:</strong> {{ $reservation->rejection_reason }}</div>
                                                @endif
                                            </div>
                                        </li>
                                        @endif
                                        @if($reservation->status == 'expired')
                                        <li class="mb-4 position-relative">
                                            <span class="position-absolute top-0 start-0 translate-middle bg-white border border-2 rounded-circle border-warning" style="width:18px;height:18px;left:-10px;"><i class="fas fa-calendar-times text-warning small d-flex justify-content-center align-items-center" style="font-size:12px;"></i></span>
                                            <div class="ms-4">
                                                <div class="fw-bold">Pembayaran kedaluwarsa</div>
                                                <div class="text-muted small">{{ $reservation->status_expired_at ? \Carbon\Carbon::parse($reservation->status_expired_at)->format('d M Y, H:i') : '-' }}</div>
                                            </div>
                                        </li>
                                        @endif
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Status Card -->
                        <div class="col-md-4 mb-4">
                            <div class="card reservation-card reservation-status-card">
                                <div class="card-body">
                                    <h5 class="reservation-section-title">Status Reservasi</h5>
                                    <div class="d-flex align-items-center mb-3">
                                        @if ($reservation->status == 'pending')
                                            <span class="badge bg-warning reservation-badge">Pending</span>
                                        @elseif($reservation->status == 'approved')
                                            <span class="badge bg-info reservation-badge">Approved</span>
                                        @elseif($reservation->status == 'paid')
                                            <span class="badge bg-success reservation-badge">Paid</span>
                                        @elseif($reservation->status == 'completed')
                                            <span class="badge bg-primary reservation-badge">Completed</span>
                                        @elseif($reservation->status == 'cancelled')
                                            <span class="badge bg-danger reservation-badge">Cancelled</span>
                                        @elseif($reservation->status == 'rejected')
                                            <span class="badge bg-secondary reservation-badge">Rejected</span>
                                        @elseif($reservation->status == 'expired')
                                            <span class="badge bg-secondary reservation-badge">Expired</span>
                                        @endif
                                    </div>
                                    <div class="text-muted">
                                        <small>Dibuat: {{ $reservation->created_at->format('d M Y H:i') }}</small><br>
                                        <small>Terakhir diupdate: {{ $reservation->updated_at->format('d M Y H:i') }}</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Customer Info Card -->
                        <div class="col-md-4 mb-4">
                            <div class="card reservation-card">
                                <div class="card-body">
                                    <h5 class="reservation-section-title">Informasi Customer</h5>
                                    <div class="mb-2">
                                        <div class="reservation-info-label">Nama:</div>
                                        <div class="reservation-info-value">{{ $reservation->customer->name }}</div>
                                    </div>
                                    <div class="mb-2">
                                        <div class="reservation-info-label">Email:</div>
                                        <div class="reservation-info-value">{{ $reservation->customer->email ?? '-' }}</div>
                                    </div>
                                    <div class="mb-2">
                                        <div class="reservation-info-label">Telepon:</div>
                                        <div class="reservation-info-value">{{ $reservation->customer->phone ?? '-' }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Table Info Card -->
                        <div class="col-md-4 mb-4">
                            <div class="card reservation-card">
                                <div class="card-body">
                                    <h5 class="reservation-section-title">Informasi Meja</h5>
                                    <div class="mb-2">
                                        <div class="reservation-info-label">Nomor Meja:</div>
                                        <div class="reservation-info-value">Meja #{{ $reservation->table->table_number }}</div>
                                    </div>
                                    <div class="mb-2">
                                        <div class="reservation-info-label">Ruangan:</div>
                                        <div class="reservation-info-value">{{ $reservation->table->room->name }}</div>
                                    </div>
                                    <div class="mb-2">
                                        <div class="reservation-info-label">Kapasitas:</div>
                                        <div class="reservation-info-value">{{ $reservation->table->capacity }} orang</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Reservation Details Card -->
                        <div class="col-md-6 mb-4">
                            <div class="card reservation-card">
                                <div class="card-body">
                                    <h5 class="reservation-section-title">Detail Reservasi</h5>
                                    <div class="mb-2">
                                        <div class="reservation-info-label">Kode Reservasi:</div>
                                        <div class="reservation-info-value">RES-{{ $reservation->id }}</div>
                                    </div>
                                    <div class="mb-2">
                                        <div class="reservation-info-label">Waktu Mulai:</div>
                                        <div class="reservation-info-value">{{ $reservation->start_time->format('d M Y H:i') }}</div>
                                    </div>
                                    <div class="mb-2">
                                        <div class="reservation-info-label">Waktu Selesai:</div>
                                        <div class="reservation-info-value">{{ $reservation->end_time->format('d M Y H:i') }}</div>
                                    </div>
                                    <div class="mb-2">
                                        <div class="reservation-info-label">Durasi:</div>
                                        <div class="reservation-info-value">{{ $reservation->duration_hours ?? $reservation->start_time->diffInHours($reservation->end_time) }} jam</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Payment Info Card -->
                        <div class="col-md-6 mb-4">
                            <div class="card reservation-card reservation-payment-card">
                                <div class="card-body">
                                    <h5 class="reservation-section-title">Informasi Pembayaran</h5>
                                    <div class="mb-2">
                                        <div class="reservation-info-label">Total Harga:</div>
                                        <div class="reservation-info-value text-primary">Rp {{ number_format($reservation->total_price ?? $reservation->calculateTotalPrice(), 0, ',', '.') }}</div>
                                    </div>
                                    @if($reservation->status == 'approved')
                                        <div class="mb-2">
                                            <div class="reservation-info-label">Batas Pembayaran:</div>
                                            <div class="reservation-info-value">{{ $reservation->payment_expired_at ? $reservation->payment_expired_at->format('d M Y H:i') : '-' }}</div>
                                        </div>
                                    @endif
                                    @if($reservation->status == 'rejected')
                                        <div class="mb-2">
                                            <div class="reservation-info-label">Alasan Penolakan:</div>
                                            <div class="reservation-info-value text-danger">{{ $reservation->reason ?? '-' }}</div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Information Section -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Informasi Pembayaran</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <tbody>
                                        <tr>
                                            <td width="30%">Harga per Jam</td>
                                            <td>{{ $paymentDetails['price_per_hour'] }}</td>
                                        </tr>
                                        <tr>
                                            <td>Durasi</td>
                                            <td>{{ $paymentDetails['duration_hours'] }}</td>
                                        </tr>
                                        <tr>
                                            <td>Subtotal</td>
                                            <td>{{ $paymentDetails['subtotal'] }}</td>
                                        </tr>
                                        @php
                                            $calculatedDiscount = 0;
                                            $subtotal = $reservation->price_per_hour * $reservation->duration_hours;
                                            $calculatedDiscount = $subtotal - ($reservation->total_price ?: $subtotal);
                                        @endphp
                                        <tr>
                                            <td>Potongan Diskon</td>
                                            <td class="text-danger">
                                                @if($calculatedDiscount > 0)
                                                    - Rp {{ number_format($calculatedDiscount, 0, ',', '.') }}
                                                @else
                                                    Rp 0
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Total Harga</td>
                                            <td>{{ $paymentDetails['total_price'] }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="reservation-action-bar">
                        @if($reservation->status == 'pending')
                            <button type="button" class="btn btn-success approve-reservation" data-id="{{ $reservation->id }}">
                                <i class="fas fa-check me-1"></i> Setujui
                            </button>
                            <button class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#rejectModal">
                                <i class="fas fa-times"></i> Tolak
                            </button>
                        @endif
                        @if($reservation->status == 'approved')
                            <a href="{{ route('admin.reservations.edit', $reservation->id) }}" class="btn btn-primary">
                                <i class="fas fa-edit me-1"></i> Edit
                            </a>
                        @endif
                        @if(in_array($reservation->status, ['approved','paid']) && $reservation->status != 'completed')
                            <form action="{{ route('admin.reservations.check-payment', $reservation->id) }}" method="POST" style="display:inline;">
                                @csrf
                                <button type="submit" class="btn btn-info ms-2">Cek Pembayaran</button>
                            </form>
                        @endif
                        @if($reservation->status == 'completed')
                            <button type="button" class="btn btn-info ms-2" disabled>
                                <i class="fas fa-check-circle me-1"></i> Pembayaran Terverifikasi
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Tolak Reservasi -->
    @if($reservation->status == 'pending')
    <div class="modal fade" id="rejectModal" tabindex="-1">
      <div class="modal-dialog">
        <form method="POST" action="{{ route('admin.reservations.reject', $reservation->id) }}">
          @csrf
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">Alasan Penolakan</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
              <textarea name="rejection_reason" class="form-control" required placeholder="Masukkan alasan penolakan"></textarea>
            </div>
            <div class="modal-footer">
              <button type="submit" class="btn btn-danger">Tolak Reservasi</button>
            </div>
          </div>
        </form>
      </div>
    </div>
    @endif
@endsection

@push('scripts')
    <script src="{{ asset('dist/assets/libs/sweetalert2/sweetalert2.min.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Handle approve reservation
            const approveBtn = document.querySelector('.approve-reservation');
            if (approveBtn) {
                approveBtn.addEventListener('click', function() {
                    const id = this.getAttribute('data-id');
                    Swal.fire({
                        title: 'Konfirmasi Persetujuan',
                        text: 'Apakah Anda yakin ingin menyetujui reservasi ini?',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Ya, Setujui!',
                        cancelButtonText: 'Batal'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            const form = document.createElement('form');
                            form.method = 'POST';
                            form.action = `{{ route('admin.reservations.index') }}/${id}/approve`;
                            form.style.display = 'none';

                            const csrfToken = document.createElement('input');
                            csrfToken.type = 'hidden';
                            csrfToken.name = '_token';
                            csrfToken.value = '{{ csrf_token() }}';

                            form.appendChild(csrfToken);
                            document.body.appendChild(form);
                            form.submit();
                        }
                    });
                });
            }

            // Handle reject reservation
            const rejectBtn = document.querySelector('.reject-reservation');
            if (rejectBtn) {
                rejectBtn.addEventListener('click', function() {
                    const id = this.getAttribute('data-id');
                    Swal.fire({
                        title: 'Konfirmasi Penolakan',
                        text: 'Apakah Anda yakin ingin menolak reservasi ini?',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#3085d6',
                        confirmButtonText: 'Ya, Tolak!',
                        cancelButtonText: 'Batal',
                        input: 'text',
                        inputLabel: 'Alasan Penolakan',
                        inputPlaceholder: 'Masukkan alasan penolakan',
                        inputValidator: (value) => {
                            if (!value) {
                                return 'Alasan penolakan harus diisi!';
                            }
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            const form = document.createElement('form');
                            form.method = 'POST';
                            form.action = `{{ route('admin.reservations.index') }}/${id}/reject`;
                            form.style.display = 'none';

                            const csrfToken = document.createElement('input');
                            csrfToken.type = 'hidden';
                            csrfToken.name = '_token';
                            csrfToken.value = '{{ csrf_token() }}';

                            const reasonInput = document.createElement('input');
                            reasonInput.type = 'hidden';
                            reasonInput.name = 'reason';
                            reasonInput.value = result.value;

                            form.appendChild(csrfToken);
                            form.appendChild(reasonInput);
                            document.body.appendChild(form);
                            form.submit();
                        }
                    });
                });
            }
        });
    </script>
@endpush
