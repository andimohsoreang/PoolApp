@extends('layouts.app')

@section('title', 'Master Meja Biliar')

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
                <h4 class="page-title">Meja Biliar</h4>
                <div class="">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="#">Master</a></li>
                        <li class="breadcrumb-item active">Meja Biliar</li>
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
                            <h4 class="card-title">Daftar Meja Biliar</h4>
                        </div>
                        <div class="col-auto">
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                                data-bs-target="#addModalLarge">
                                <i class="fas fa-plus me-1"></i> Tambah Meja
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Filter Form -->
                <div class="card-body pb-0">
                    <form id="filterForm" method="GET" action="{{ route('admin.billiard-tables.index') }}">
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label for="filter_table_number" class="form-label">Nomor Meja</label>
                                <input type="text" class="form-control" id="filter_table_number" name="table_number"
                                    value="{{ request('table_number') }}" placeholder="Cari nomor meja...">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="filter_room_id" class="form-label">Ruangan</label>
                                <select class="form-select" id="filter_room_id" name="room_id">
                                    <option value="">Semua Ruangan</option>
                                    @foreach($rooms as $room)
                                        <option value="{{ $room->id }}" {{ request('room_id') == $room->id ? 'selected' : '' }}>
                                            {{ $room->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="filter_status" class="form-label">Status</label>
                                <select class="form-select" id="filter_status" name="status">
                                    <option value="">Semua Status</option>
                                    <option value="normal" {{ request('status') == 'normal' ? 'selected' : '' }}>Normal</option>
                                    <option value="rusak" {{ request('status') == 'rusak' ? 'selected' : '' }}>Rusak</option>
                                    <option value="maintenance" {{ request('status') == 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                                </select>
                            </div>
                            <div class="col-md-3 mb-3 d-flex align-items-end">
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search me-1"></i> Filter
                                    </button>
                                    <a href="{{ route('admin.billiard-tables.index') }}" class="btn btn-secondary">
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
                                    <th>Nomor Meja</th>
                                    <th>Ruangan</th>
                                    <th>Kapasitas</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($tables as $key => $table)
                                    <tr>
                                        <td>{{ $key + 1 }}</td>
                                        <td>{{ $table->table_number }}</td>
                                        <td>
                                            @if ($table->room)
                                                <span class="badge bg-primary">{{ $table->room->name }}</span>
                                            @else
                                                <span class="badge bg-danger">Room Tidak Tersedia</span>
                                            @endif
                                        </td>
                                        <td>{{ $table->capacity }} orang</td>
                                        <td>
                                            @if ($table->status == 'normal')
                                                <span class="badge bg-success">Normal</span>
                                            @elseif($table->status == 'rusak')
                                                <span class="badge bg-danger">Rusak</span>
                                            @elseif($table->status == 'maintenance')
                                                <span class="badge bg-warning">Maintenance</span>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('admin.billiard-tables.show', $table->id) }}" class="btn btn-outline-info btn-sm">
                                                <i class="fas fa-eye"></i> Detail
                                            </a>
                                            <a href="javascript:void(0);" class="btn btn-outline-primary btn-sm"
                                                onclick="openEditModal('{{ $table->id }}', '{{ $table->table_number }}', '{{ $table->room_id }}', '{{ $table->capacity }}', '{{ $table->status }}', '{{ $table->description }}')">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            <a href="javascript:void(0);" class="btn btn-outline-danger btn-sm delete-table"
                                                data-id="{{ $table->id }}" data-table-number="{{ $table->table_number }}">
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
                    <h6 class="modal-title m-0" id="myLargeModalLabel">Tambah Meja Biliar</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div><!--end modal-header-->
                <div class="modal-body">
                    <form id="tableFrm" action="{{ route('admin.billiard-tables.store') }}" method="POST">
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
                            <label for="table_number" class="col-sm-3 col-form-label text-end">Nomor Meja</label>
                            <div class="col-sm-9">
                                <input class="form-control @error('table_number') is-invalid @enderror" type="text"
                                    id="table_number" name="table_number" placeholder="Masukkan nomor meja"
                                    value="{{ old('table_number') }}" required>
                                @error('table_number')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3 row">
                            <label for="room_id" class="col-sm-3 col-form-label text-end">Ruangan</label>
                            <div class="col-sm-9">
                                <select class="form-select @error('room_id') is-invalid @enderror" id="room_id"
                                    name="room_id" required>
                                    <option value="">Pilih Ruangan</option>
                                    @foreach($rooms as $room)
                                        <option value="{{ $room->id }}" {{ old('room_id') == $room->id ? 'selected' : '' }}>
                                            {{ $room->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('room_id')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3 row">
                            <label for="capacity" class="col-sm-3 col-form-label text-end">Kapasitas</label>
                            <div class="col-sm-9">
                                <input class="form-control @error('capacity') is-invalid @enderror" type="number"
                                    id="capacity" name="capacity" placeholder="Masukkan kapasitas meja"
                                    value="{{ old('capacity', 4) }}" min="1" required>
                                @error('capacity')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3 row">
                            <label for="status" class="col-sm-3 col-form-label text-end">Status</label>
                            <div class="col-sm-9">
                                <select class="form-select @error('status') is-invalid @enderror" id="status"
                                    name="status" required>
                                    <option value="normal" {{ old('status') == 'normal' ? 'selected' : '' }}>Normal</option>
                                    <option value="rusak" {{ old('status') == 'rusak' ? 'selected' : '' }}>Rusak</option>
                                    <option value="maintenance" {{ old('status') == 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3 row">
                            <label for="description" class="col-sm-3 col-form-label text-end">Deskripsi</label>
                            <div class="col-sm-9">
                                <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description"
                                    rows="3" placeholder="Deskripsi meja (opsional)">{{ old('description') }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">
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
                    <h6 class="modal-title m-0" id="myEditModalLabel">Edit Meja Biliar</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div><!--end modal-header-->
                <div class="modal-body">
                    <form id="editTableFrm" method="POST">
                        @csrf
                        @method('PUT')
                        <input type="hidden" id="edit_id_table" name="id" value="{{ old('id') }}">

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
                            <label for="edit_table_number" class="col-sm-3 col-form-label text-end">Nomor Meja</label>
                            <div class="col-sm-9">
                                <input class="form-control @error('table_number') is-invalid @enderror" type="text"
                                    id="edit_table_number" name="table_number" placeholder="Masukkan nomor meja"
                                    value="{{ old('table_number') }}" required>
                                @error('table_number')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3 row">
                            <label for="edit_room_id" class="col-sm-3 col-form-label text-end">Ruangan</label>
                            <div class="col-sm-9">
                                <select class="form-select @error('room_id') is-invalid @enderror" id="edit_room_id"
                                    name="room_id" required>
                                    <option value="">Pilih Ruangan</option>
                                    @foreach($rooms as $room)
                                        <option value="{{ $room->id }}">{{ $room->name }}</option>
                                    @endforeach
                                </select>
                                @error('room_id')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3 row">
                            <label for="edit_capacity" class="col-sm-3 col-form-label text-end">Kapasitas</label>
                            <div class="col-sm-9">
                                <input class="form-control @error('capacity') is-invalid @enderror" type="number"
                                    id="edit_capacity" name="capacity" placeholder="Masukkan kapasitas meja"
                                    value="{{ old('capacity') }}" min="1" required>
                                @error('capacity')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3 row">
                            <label for="edit_status" class="col-sm-3 col-form-label text-end">Status</label>
                            <div class="col-sm-9">
                                <select class="form-select @error('status') is-invalid @enderror" id="edit_status"
                                    name="status" required>
                                    <option value="normal">Normal</option>
                                    <option value="rusak">Rusak</option>
                                    <option value="maintenance">Maintenance</option>
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3 row">
                            <label for="edit_description" class="col-sm-3 col-form-label text-end">Deskripsi</label>
                            <div class="col-sm-9">
                                <textarea class="form-control @error('description') is-invalid @enderror" id="edit_description" name="description"
                                    rows="3" placeholder="Deskripsi meja (opsional)">{{ old('description') }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">
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
        function openEditModal(id, table_number, room_id, capacity, status, description) {
            const form = document.getElementById('editTableFrm');
            form.setAttribute('action', `{{ route('admin.billiard-tables.index') }}/${id}`);

            document.getElementById('edit_id_table').value = id;
            document.getElementById('edit_table_number').value = table_number;
            document.getElementById('edit_room_id').value = room_id;
            document.getElementById('edit_capacity').value = capacity;
            document.getElementById('edit_status').value = status;
            document.getElementById('edit_description').value = description || '';

            // Open the modal
            var editModal = new bootstrap.Modal(document.getElementById('editModalLarge'));
            editModal.show();
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
                document.getElementById('tableFrm').submit();
            });

            // Add event listener for the update button
            document.getElementById('btnUpdate').addEventListener('click', function() {
                document.getElementById('editTableFrm').submit();
            });

            // Delete confirmation
            document.querySelectorAll('.delete-table').forEach(button => {
                button.addEventListener('click', function() {
                    const id = this.getAttribute('data-id');
                    const tableNumber = this.getAttribute('data-table-number');

                    Swal.fire({
                        title: 'Apakah Anda yakin?',
                        text: `Meja "${tableNumber}" akan dihapus secara permanen!`,
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
                            form.action = `{{ route('admin.billiard-tables.index') }}/${id}`;
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
