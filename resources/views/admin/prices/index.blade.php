@extends('layouts.app')

@section('title', 'Master Harga')

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
                <h4 class="page-title">Harga</h4>
                <div class="">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="#">Master</a></li>
                        <li class="breadcrumb-item active">Harga</li>
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
                            <h4 class="card-title">Daftar Harga</h4>
                        </div>
                        <div class="col-auto">
                            <a href="{{ route('admin.prices.test') }}" class="btn btn-info me-2">
                                <i class="fas fa-clock me-1"></i> Test Harga
                            </a>
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                                data-bs-target="#addModalLarge">
                                <i class="fas fa-plus me-1"></i> Tambah Harga
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Filter Form -->
                <div class="card-body pb-0">
                    <form id="filterForm" method="GET" action="{{ route('admin.prices.index') }}">
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label for="filter_table_id" class="form-label">Meja</label>
                                <select class="form-select" id="filter_table_id" name="table_id">
                                    <option value="">Semua Meja</option>
                                    @foreach($tables as $table)
                                        <option value="{{ $table->id }}" {{ request('table_id') == $table->id ? 'selected' : '' }}>
                                            {{ $table->table_number }} - {{ $table->room->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2 mb-3">
                                <label for="filter_day_type" class="form-label">Tipe Hari</label>
                                <select class="form-select" id="filter_day_type" name="day_type">
                                    <option value="">Semua Tipe</option>
                                    <option value="weekday" {{ request('day_type') == 'weekday' ? 'selected' : '' }}>Hari Kerja</option>
                                    <option value="weekend" {{ request('day_type') == 'weekend' ? 'selected' : '' }}>Akhir Pekan</option>
                                </select>
                            </div>
                            <div class="col-md-2 mb-3">
                                <label for="filter_status" class="form-label">Status</label>
                                <select class="form-select" id="filter_status" name="status">
                                    <option value="">Semua Status</option>
                                    <option value="1" {{ request('status') == '1' ? 'selected' : '' }}>Aktif</option>
                                    <option value="0" {{ request('status') == '0' ? 'selected' : '' }}>Tidak Aktif</option>
                                </select>
                            </div>
                            <div class="col-md-2 mb-3">
                                <label for="filter_min_price" class="form-label">Harga Min.</label>
                                <input type="number" class="form-control" id="filter_min_price" name="min_price"
                                    value="{{ request('min_price') }}" placeholder="Rp">
                            </div>
                            <div class="col-md-3 mb-3 d-flex align-items-end">
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search me-1"></i> Filter
                                    </button>
                                    <a href="{{ route('admin.prices.index') }}" class="btn btn-secondary">
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
                                    <th>Meja</th>
                                    <th>Waktu</th>
                                    <th>Tipe Hari</th>
                                    <th>Harga</th>
                                    <th>Periode Berlaku</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($prices as $key => $price)
                                    <tr>
                                        <td>{{ $key + 1 }}</td>
                                        <td>
                                            @if ($price->table && $price->table->room)
                                                <span class="badge bg-primary">{{ $price->table->table_number }} - {{ $price->table->room->name }}</span>
                                            @else
                                                <span class="badge bg-danger">Meja Tidak Tersedia</span>
                                            @endif
                                        </td>
                                        <td>{{ \Carbon\Carbon::parse($price->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($price->end_time)->format('H:i') }}</td>
                                        <td>
                                            @if($price->day_type == 'weekday')
                                                <span class="badge bg-primary">Hari Kerja</span>
                                            @elseif($price->day_type == 'weekend')
                                                <span class="badge bg-info">Akhir Pekan</span>
                                            @endif
                                        </td>
                                        <td>Rp {{ number_format($price->price, 0, ',', '.') }}</td>
                                        <td>
                                            @if($price->valid_from && $price->valid_until)
                                                {{ \Carbon\Carbon::parse($price->valid_from)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($price->valid_until)->format('d/m/Y') }}
                                            @elseif($price->valid_from)
                                                Dari {{ \Carbon\Carbon::parse($price->valid_from)->format('d/m/Y') }}
                                            @elseif($price->valid_until)
                                                Sampai {{ \Carbon\Carbon::parse($price->valid_until)->format('d/m/Y') }}
                                            @else
                                                Tidak ada batas
                                            @endif
                                        </td>
                                        <td>
                                            @if($price->status)
                                                <span class="badge bg-success">Aktif</span>
                                            @else
                                                <span class="badge bg-danger">Tidak Aktif</span>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('admin.prices.show', $price->id) }}" class="btn btn-outline-info btn-sm">
                                                <i class="fas fa-eye"></i> Detail
                                            </a>
                                            <a href="javascript:void(0);" class="btn btn-outline-primary btn-sm"
                                                onclick="openEditModal('{{ $price->id }}')">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            <a href="javascript:void(0);" class="btn btn-outline-danger btn-sm delete-price"
                                                data-id="{{ $price->id }}">
                                                <i class="fas fa-trash-alt"></i> Hapus
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Add -->
    <div class="modal fade bd-example-modal-lg" id="addModalLarge" tabindex="-1" role="dialog"
        aria-labelledby="myLargeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title m-0" id="myLargeModalLabel">Tambah Harga</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div><!--end modal-header-->
                <div class="modal-body">
                    <form id="priceFrm" action="{{ route('admin.prices.store') }}" method="POST">
                        @csrf
                        <div class="mb-3 row">
                            @if ($errors->any() && !old('_method'))
                                <div class="col-sm-12">
                                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                        <button type="button" class="btn-close" data-bs-dismiss="alert"
                                            aria-label="Close"></button>
                                        <strong>Error!</strong> Terdapat kesalahan pada data yang Anda masukkan.
                                        <ul class="mb-0 mt-2">
                                            @foreach ($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <div class="mb-3 row">
                            <label for="table_id" class="col-sm-3 col-form-label text-end">Meja</label>
                            <div class="col-sm-9">
                                <select class="form-select @error('table_id') is-invalid @enderror" id="table_id"
                                    name="table_id" required>
                                    <option value="">Pilih Meja</option>
                                    @foreach($tables as $table)
                                        <option value="{{ $table->id }}" {{ old('table_id') == $table->id ? 'selected' : '' }}>
                                            {{ $table->table_number }} - {{ $table->room->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('table_id')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3 row">
                            <label for="start_time" class="col-sm-3 col-form-label text-end">Waktu Mulai</label>
                            <div class="col-sm-9">
                                <input type="time" class="form-control @error('start_time') is-invalid @enderror"
                                    id="start_time" name="start_time" value="{{ old('start_time') }}" required>
                                @error('start_time')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3 row">
                            <label for="end_time" class="col-sm-3 col-form-label text-end">Waktu Selesai</label>
                            <div class="col-sm-9">
                                <input type="time" class="form-control @error('end_time') is-invalid @enderror"
                                    id="end_time" name="end_time" value="{{ old('end_time') }}" required>
                                @error('end_time')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3 row">
                            <label for="price" class="col-sm-3 col-form-label text-end">Harga (Rp)</label>
                            <div class="col-sm-9">
                                <input type="number" class="form-control @error('price') is-invalid @enderror"
                                    id="price" name="price" value="{{ old('price') }}" min="0" required>
                                @error('price')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3 row">
                            <label for="day_type" class="col-sm-3 col-form-label text-end">Tipe Hari</label>
                            <div class="col-sm-9">
                                <select class="form-select @error('day_type') is-invalid @enderror" id="day_type"
                                    name="day_type" required>
                                    <option value="weekday" {{ old('day_type') == 'weekday' ? 'selected' : '' }}>Hari Kerja (Senin-Jumat)</option>
                                    <option value="weekend" {{ old('day_type') == 'weekend' ? 'selected' : '' }}>Akhir Pekan (Sabtu-Minggu)</option>
                                </select>
                                @error('day_type')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3 row">
                            <label for="valid_from" class="col-sm-3 col-form-label text-end">Berlaku Dari</label>
                            <div class="col-sm-9">
                                <input type="date" class="form-control @error('valid_from') is-invalid @enderror"
                                    id="valid_from" name="valid_from" value="{{ old('valid_from') }}">
                                <small class="text-muted">Biarkan kosong jika berlaku selamanya</small>
                                @error('valid_from')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3 row">
                            <label for="valid_until" class="col-sm-3 col-form-label text-end">Berlaku Sampai</label>
                            <div class="col-sm-9">
                                <input type="date" class="form-control @error('valid_until') is-invalid @enderror"
                                    id="valid_until" name="valid_until" value="{{ old('valid_until') }}">
                                <small class="text-muted">Biarkan kosong jika berlaku selamanya</small>
                                @error('valid_until')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3 row">
                            <label for="status" class="col-sm-3 col-form-label text-end">Status</label>
                            <div class="col-sm-9">
                                <div class="form-check form-switch form-switch-success">
                                    <input class="form-check-input" type="checkbox" id="status" name="status"
                                        value="1" {{ old('status', '1') == '1' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="status">Aktif</label>
                                </div>
                                @error('status')
                                    <div class="text-danger mt-1">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                        </div>
                    </form>
                </div><!--end modal-body-->
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" id="btnSimpan">Simpan</button>
                </div><!--end modal-footer-->
            </div><!--end modal-content-->
        </div><!--end modal-dialog-->
    </div><!--end modal-->

    <!-- Modal Edit -->
    <div class="modal fade bd-example-modal-lg" id="editModalLarge" tabindex="-1" role="dialog"
        aria-labelledby="myEditModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title m-0" id="myEditModalLabel">Edit Harga</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div><!--end modal-header-->
                <div class="modal-body">
                    <form id="editPriceFrm" method="POST">
                        @csrf
                        @method('PUT')
                        <input type="hidden" id="edit_id_price" name="id" value="{{ old('id') }}">

                        <div class="mb-3 row">
                            @if ($errors->any() && old('_method') == 'PUT')
                                <div class="col-sm-12">
                                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                        <button type="button" class="btn-close" data-bs-dismiss="alert"
                                            aria-label="Close"></button>
                                        <strong>Error!</strong> Terdapat kesalahan pada data yang Anda masukkan.
                                        <ul class="mb-0 mt-2">
                                            @foreach ($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <div class="mb-3 row">
                            <label for="edit_table_id" class="col-sm-3 col-form-label text-end">Meja</label>
                            <div class="col-sm-9">
                                <select class="form-select @error('table_id') is-invalid @enderror" id="edit_table_id"
                                    name="table_id" required>
                                    <option value="">Pilih Meja</option>
                                    @foreach($tables as $table)
                                        <option value="{{ $table->id }}">
                                            {{ $table->table_number }} - {{ $table->room->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('table_id')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3 row">
                            <label for="edit_start_time" class="col-sm-3 col-form-label text-end">Waktu Mulai</label>
                            <div class="col-sm-9">
                                <input type="time" class="form-control @error('start_time') is-invalid @enderror"
                                    id="edit_start_time" name="start_time" value="{{ old('start_time') }}" required>
                                @error('start_time')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3 row">
                            <label for="edit_end_time" class="col-sm-3 col-form-label text-end">Waktu Selesai</label>
                            <div class="col-sm-9">
                                <input type="time" class="form-control @error('end_time') is-invalid @enderror"
                                    id="edit_end_time" name="end_time" value="{{ old('end_time') }}" required>
                                @error('end_time')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3 row">
                            <label for="edit_price" class="col-sm-3 col-form-label text-end">Harga (Rp)</label>
                            <div class="col-sm-9">
                                <input type="number" class="form-control @error('price') is-invalid @enderror"
                                    id="edit_price" name="price" value="{{ old('price') }}" min="0" required>
                                @error('price')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3 row">
                            <label for="edit_day_type" class="col-sm-3 col-form-label text-end">Tipe Hari</label>
                            <div class="col-sm-9">
                                <select class="form-select @error('day_type') is-invalid @enderror" id="edit_day_type"
                                    name="day_type" required>
                                    <option value="weekday">Hari Kerja (Senin-Jumat)</option>
                                    <option value="weekend">Akhir Pekan (Sabtu-Minggu)</option>
                                </select>
                                @error('day_type')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3 row">
                            <label for="edit_valid_from" class="col-sm-3 col-form-label text-end">Berlaku Dari</label>
                            <div class="col-sm-9">
                                <input type="date" class="form-control @error('valid_from') is-invalid @enderror"
                                    id="edit_valid_from" name="valid_from" value="{{ old('valid_from') }}">
                                <small class="text-muted">Biarkan kosong jika berlaku selamanya</small>
                                @error('valid_from')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3 row">
                            <label for="edit_valid_until" class="col-sm-3 col-form-label text-end">Berlaku Sampai</label>
                            <div class="col-sm-9">
                                <input type="date" class="form-control @error('valid_until') is-invalid @enderror"
                                    id="edit_valid_until" name="valid_until" value="{{ old('valid_until') }}">
                                <small class="text-muted">Biarkan kosong jika berlaku selamanya</small>
                                @error('valid_until')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3 row">
                            <label for="edit_status" class="col-sm-3 col-form-label text-end">Status</label>
                            <div class="col-sm-9">
                                <div class="form-check form-switch form-switch-success">
                                    <input class="form-check-input" type="checkbox" id="edit_status" name="status"
                                        value="1">
                                    <label class="form-check-label" for="edit_status">Aktif</label>
                                </div>
                                @error('status')
                                    <div class="text-danger mt-1">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                        </div>
                    </form>
                </div><!--end modal-body-->
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" id="btnUpdate">Update</button>
                </div><!--end modal-footer-->
            </div><!--end modal-content-->
        </div><!--end modal-dialog-->
    </div><!--end modal-->

@endsection

@push('scripts')
    <script src="{{ asset('dist/assets/libs/simple-datatables/umd/simple-datatables.js') }}"></script>
    <script src="{{ asset('dist/assets/js/pages/datatable.init.js') }}"></script>
    <script src="{{ asset('dist/assets/libs/sweetalert2/sweetalert2.min.js') }}"></script>

    <script>
        // Function to open edit modal with data
        function openEditModal(id) {
            // Show loading indicator
            Swal.fire({
                title: 'Memuat...',
                html: 'Sedang mengambil data harga',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            // Fetch price data via AJAX
            fetch(`{{ url('admin/prices') }}/${id}/edit`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! Status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                // Close loading indicator
                Swal.close();

                if (data && data.status === 'success' && data.price) {
                    const price = data.price;
                    const form = document.getElementById('editPriceFrm');
                    form.setAttribute('action', `{{ url('admin/prices') }}/${id}`);

                    // Set basic fields
                    document.getElementById('edit_id_price').value = price.id;
                    document.getElementById('edit_table_id').value = price.table_id;
                    document.getElementById('edit_price').value = price.price;
                    document.getElementById('edit_day_type').value = price.day_type;

                    // Format time fields
                    if (price.start_time) {
                        const startTimeParts = price.start_time.substring(0, 5).split(':');
                        if (startTimeParts.length === 2) {
                            document.getElementById('edit_start_time').value = startTimeParts[0].padStart(2, '0') + ':' + startTimeParts[1].padStart(2, '0');
                        }
                    }

                    if (price.end_time) {
                        const endTimeParts = price.end_time.substring(0, 5).split(':');
                        if (endTimeParts.length === 2) {
                            document.getElementById('edit_end_time').value = endTimeParts[0].padStart(2, '0') + ':' + endTimeParts[1].padStart(2, '0');
                        }
                    }

                    // Handle date fields
                    if (price.valid_from) {
                        document.getElementById('edit_valid_from').value = price.valid_from.split(' ')[0];
                    } else {
                        document.getElementById('edit_valid_from').value = '';
                    }

                    if (price.valid_until) {
                        document.getElementById('edit_valid_until').value = price.valid_until.split(' ')[0];
                    } else {
                        document.getElementById('edit_valid_until').value = '';
                    }

                    // Set status checkbox
                    document.getElementById('edit_status').checked = price.status === 1 || price.status === true;

                    // Open the modal
                    var editModal = new bootstrap.Modal(document.getElementById('editModalLarge'));
                    editModal.show();
                } else {
                    Swal.fire({
                        title: 'Error!',
                        text: 'Data tidak valid: ' + JSON.stringify(data),
                        icon: 'error'
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    title: 'Error!',
                    text: 'Terjadi kesalahan saat mengambil data: ' + error.message,
                    icon: 'error'
                });
            });
        }

        // Initialize when the add modal opens
        document.addEventListener('DOMContentLoaded', function() {
            // Buka modal jika ada error validasi
            @if ($errors->any())
                @if (old('_method') == 'PUT')
                    // Jika error pada form edit
                    var editModal = new bootstrap.Modal(document.getElementById('editModalLarge'));
                    editModal.show();
                @else
                    // Jika error pada form tambah
                    var addModal = new bootstrap.Modal(document.getElementById('addModalLarge'));
                    addModal.show();
                @endif
            @endif

            // Add event listener for the save button
            document.getElementById('btnSimpan').addEventListener('click', function() {
                document.getElementById('priceFrm').submit();
            });

            // Add event listener for the update button
            document.getElementById('btnUpdate').addEventListener('click', function() {
                document.getElementById('editPriceFrm').submit();
            });

            // Delete confirmation
            document.querySelectorAll('.delete-price').forEach(button => {
                button.addEventListener('click', function() {
                    const id = this.getAttribute('data-id');

                    Swal.fire({
                        title: 'Apakah Anda yakin?',
                        text: `Harga ini akan dihapus secara permanen!`,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Ya, hapus!',
                        cancelButtonText: 'Batal'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // Create form for delete
                            const form = document.createElement('form');
                            form.method = 'POST';
                            form.action = `{{ route('admin.prices.destroy', ':id') }}`.replace(':id', id);
                            form.style.display = 'none';

                            const csrfToken = document.createElement('input');
                            csrfToken.type = 'hidden';
                            csrfToken.name = '_token';
                            csrfToken.value = '{{ csrf_token() }}';

                            const method = document.createElement('input');
                            method.type = 'hidden';
                            method.name = '_method';
                            method.value = 'DELETE';

                            form.appendChild(csrfToken);
                            form.appendChild(method);
                            document.body.appendChild(form);

                            form.submit();
                        }
                    });
                });
            });

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
        });
    </script>
@endpush
