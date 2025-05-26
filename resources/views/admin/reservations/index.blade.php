@extends('layouts.app')

@section('title', 'Master Reservasi')

@push('links')
    <link href="{{ asset('dist/assets/libs/simple-datatables/style.css') }}" rel="stylesheet" type="text/css" />
    <!-- Sweet Alert -->
    <link href="{{ asset('dist/assets/libs/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('dist/assets/libs/animate.css/animate.min.css') }}" rel="stylesheet" type="text/css">
@endpush

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="page-title-box d-md-flex justify-content-md-between align-items-center">
                <h4 class="page-title">Reservasi</h4>
                <div class="">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="#">Master</a></li>
                        <li class="breadcrumb-item active">Reservasi</li>
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

    <div class="row justify-content-center">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="row align-items-center">
                        <div class="col">
                            <h4 class="card-title">Daftar Reservasi</h4>
                        </div>
                    </div>
                </div>

                <!-- Filter Form -->
                <div class="card-body pb-0">
                    <form id="filterForm" method="GET" action="{{ route('admin.reservations.index') }}">
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label for="filter_status" class="form-label">Status</label>
                                <select class="form-select" id="filter_status" name="status">
                                    <option value="">Semua Status</option>
                                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                                    <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Paid</option>
                                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                    <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                                    <option value="expired" {{ request('status') == 'expired' ? 'selected' : '' }}>Expired</option>
                                </select>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="filter_date" class="form-label">Tanggal</label>
                                <input type="date" class="form-control" id="filter_date" name="date" value="{{ request('date') }}">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="filter_table" class="form-label">Meja</label>
                                <select class="form-select" id="filter_table" name="table_id">
                                    <option value="">Semua Meja</option>
                                    @foreach($tables as $table)
                                        <option value="{{ $table->id }}" {{ request('table_id') == $table->id ? 'selected' : '' }}>
                                            Meja #{{ $table->table_number }} - {{ $table->room->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3 mb-3 d-flex align-items-end">
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search me-1"></i> Filter
                                    </button>
                                    <a href="{{ route('admin.reservations.index') }}" class="btn btn-secondary">
                                        <i class="fas fa-sync-alt me-1"></i> Reset
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="card-body pt-0">
                    <div class="table-responsive">
                        <table class="table datatable" id="datatable_1">
                            <thead class="table-light">
                                <tr>
                                    <th>No.</th>
                                    <th>Kode</th>
                                    <th>Customer</th>
                                    <th>Meja</th>
                                    <th>Waktu</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($reservations as $key => $reservation)
                                    <tr>
                                        <td>{{ $key + 1 }}</td>
                                        <td>RES-{{ $reservation->id }}</td>
                                        <td>
                                            <div>{{ $reservation->customer->name }}</div>
                                            <small class="text-muted">{{ $reservation->customer->email }}</small>
                                        </td>
                                        <td>
                                            <span class="badge bg-primary">Meja #{{ $reservation->table->table_number }}</span>
                                            <div class="small text-muted">{{ $reservation->table->room->name }}</div>
                                        </td>
                                        <td>
                                            <div>{{ $reservation->start_time->format('d M Y H:i') }}</div>
                                            <div class="small text-muted">{{ $reservation->duration_hours }} jam</div>
                                        </td>
                                        <td>Rp {{ number_format($reservation->total_price, 0, ',', '.') }}</td>
                                        <td>
                                            @if ($reservation->status == 'pending')
                                                <span class="badge bg-warning">Pending</span>
                                            @elseif($reservation->status == 'approved')
                                                <span class="badge bg-info">Approved</span>
                                            @elseif($reservation->status == 'paid')
                                                <span class="badge bg-success">Paid</span>
                                            @elseif($reservation->status == 'completed')
                                                <span class="badge bg-primary">Completed</span>
                                            @elseif($reservation->status == 'cancelled')
                                                <span class="badge bg-danger">Cancelled</span>
                                            @elseif($reservation->status == 'rejected')
                                                <span class="badge bg-secondary">Rejected</span>
                                            @elseif($reservation->status == 'expired')
                                                <span class="badge bg-secondary">Expired</span>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('admin.reservations.show', $reservation->id) }}" class="btn btn-outline-info btn-sm">
                                                <i class="fas fa-eye"></i> Detail
                                            </a>
                                            @if($reservation->status == 'pending')
                                                <button type="button" class="btn btn-outline-success btn-sm approve-reservation"
                                                    data-id="{{ $reservation->id }}"
                                                    data-customer="{{ $reservation->customer->name }}">
                                                    <i class="fas fa-check"></i> Approve
                                                </button>
                                                <button class="btn btn-outline-danger btn-sm" data-bs-toggle="modal" data-bs-target="#rejectModal-{{ $reservation->id }}">
                                                    <i class="fas fa-times"></i> Reject
                                                </button>
                                            @endif
                                        </td>
                                    </tr>
                                    <!-- Modal khusus untuk reservation ini -->
                                    <div class="modal fade" id="rejectModal-{{ $reservation->id }}" tabindex="-1">
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
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('dist/assets/libs/simple-datatables/umd/simple-datatables.js') }}"></script>
    <script src="{{ asset('dist/assets/js/pages/datatable.init.js') }}"></script>
    <script src="{{ asset('dist/assets/libs/sweetalert2/sweetalert2.min.js') }}"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize Simple DataTables
            if (document.getElementById('datatable_1')) {
                new simpleDatatables.DataTable('#datatable_1', {
                    perPage: 10,
                    searchable: true,
                    fixedHeight: false,
                    labels: {
                        placeholder: "Cari...",
                        perPage: "{select} data per halaman",
                        noRows: "Tidak ada data ditemukan",
                        info: "Menampilkan {start} sampai {end} dari {rows} data",
                    }
                });
            }

            // Handle approve reservation
            document.querySelectorAll('.approve-reservation').forEach(button => {
                button.addEventListener('click', function() {
                    const id = this.getAttribute('data-id');
                    const customer = this.getAttribute('data-customer');

                    Swal.fire({
                        title: 'Konfirmasi Persetujuan',
                        text: `Apakah Anda yakin ingin menyetujui reservasi dari ${customer}?`,
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Ya, Setujui!',
                        cancelButtonText: 'Batal'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // Create form for approve
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
            });

            // Handle reject reservation
            document.querySelectorAll('.reject-reservation').forEach(button => {
                button.addEventListener('click', function() {
                    const id = this.getAttribute('data-id');
                    const customer = this.getAttribute('data-customer');

                    Swal.fire({
                        title: 'Konfirmasi Penolakan',
                        text: `Apakah Anda yakin ingin menolak reservasi dari ${customer}?`,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#3085d6',
                        confirmButtonText: 'Ya, Tolak!',
                        cancelButtonText: 'Batal'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // Create form for reject
                            const form = document.createElement('form');
                            form.method = 'POST';
                            form.action = `{{ route('admin.reservations.index') }}/${id}/reject`;
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
            });
        });
    </script>
@endpush
