@extends('layouts.app')

@section('title', 'Master Rooms')

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
                <h4 class="page-title">Rooms</h4>
                <div class="">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="#">Master</a></li>
                        <li class="breadcrumb-item active">Rooms</li>
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
                            <h4 class="card-title">Daftar Rooms</h4>
                        </div>
                        <div class="col-auto">
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                                data-bs-target="#addModalLarge">
                                <i class="fas fa-plus me-1"></i> Tambah Room
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Filter Form -->
                <div class="card-body pb-0">
                    <form id="filterForm" method="GET" action="{{ route('admin.rooms.index') }}">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="filter_name" class="form-label">Nama Room</label>
                                <input type="text" class="form-control" id="filter_name" name="name"
                                    value="{{ request('name') }}" placeholder="Cari nama room...">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="filter_type" class="form-label">Tipe Room</label>
                                <select class="form-select" id="filter_type" name="type">
                                    <option value="">Semua Tipe</option>
                                    <option value="regular" {{ request('type') == 'regular' ? 'selected' : '' }}>Regular
                                    </option>
                                    <option value="vip" {{ request('type') == 'vip' ? 'selected' : '' }}>VIP</option>
                                    <option value="vvip" {{ request('type') == 'vvip' ? 'selected' : '' }}>VVIP</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3 d-flex align-items-end">
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search me-1"></i> Filter
                                    </button>
                                    <a href="{{ route('admin.rooms.index') }}" class="btn btn-secondary">
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
                                    <th>Nama Room</th>
                                    <th>Type</th>
                                    <th>Deskripsi</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($rooms as $key => $room)
                                    <tr>
                                        <td>{{ $key + 1 }}</td>
                                        <td>{{ $room->name }}</td>
                                        <td>
                                            @if ($room->type == 'regular')
                                                <span class="badge bg-primary">Regular</span>
                                            @elseif($room->type == 'vip')
                                                <span class="badge bg-info">VIP</span>
                                            @elseif($room->type == 'vvip')
                                                <span class="badge bg-danger">VVIP</span>
                                            @endif
                                        </td>
                                        <td>{{ $room->description ?? '-' }}</td>
                                        <td>
                                            @if ($room->status)
                                                <span class="badge bg-success">Aktif</span>
                                            @else
                                                <span class="badge bg-danger">Tidak Aktif</span>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('admin.rooms.show', $room->id) }}" class="btn btn-outline-info btn-sm">
                                                <i class="fas fa-eye"></i> Detail
                                            </a>
                                            <a href="javascript:void(0);" class="btn btn-outline-primary btn-sm"
                                                onclick="openEditModal('{{ $room->id }}', '{{ $room->name }}', '{{ $room->type }}', '{{ $room->description }}', '{{ $room->status }}')">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            <a href="javascript:void(0);" class="btn btn-outline-danger btn-sm delete-room"
                                                data-id="{{ $room->id }}" data-name="{{ $room->name }}">
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
                    <h6 class="modal-title m-0" id="myLargeModalLabel">Tambah Room</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div><!--end modal-header-->
                <div class="modal-body">
                    <form id="roomFrm" action="{{ route('admin.rooms.store') }}" method="POST">
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
                            <label for="name" class="col-sm-3 col-form-label text-end">Nama Room</label>
                            <div class="col-sm-9">
                                <input class="form-control @error('name') is-invalid @enderror" type="text"
                                    id="name" name="name" placeholder="Masukkan nama room"
                                    value="{{ old('name') }}" required>
                                @error('name')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3 row">
                            <label for="type" class="col-sm-3 col-form-label text-end">Tipe Room</label>
                            <div class="col-sm-9">
                                <select class="form-select @error('type') is-invalid @enderror" id="type"
                                    name="type" required>
                                    <option value="">Pilih Tipe Room</option>
                                    <option value="regular" {{ old('type') == 'regular' ? 'selected' : '' }}>Regular
                                    </option>
                                    <option value="vip" {{ old('type') == 'vip' ? 'selected' : '' }}>VIP</option>
                                    <option value="vvip" {{ old('type') == 'vvip' ? 'selected' : '' }}>VVIP</option>
                                </select>
                                @error('type')
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
                                    rows="3" placeholder="Deskripsi room (opsional)">{{ old('description') }}</textarea>
                                @error('description')
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
                    <h6 class="modal-title m-0" id="myEditModalLabel">Edit Room</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div><!--end modal-header-->
                <div class="modal-body">
                    <form id="editRoomFrm" method="POST">
                        @csrf
                        @method('PUT')
                        <input type="hidden" id="edit_id_room" name="id" value="{{ old('id') }}">

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
                            <label for="edit_name" class="col-sm-3 col-form-label text-end">Nama Room</label>
                            <div class="col-sm-9">
                                <input class="form-control @error('name') is-invalid @enderror" type="text"
                                    id="edit_name" name="name" placeholder="Masukkan nama room"
                                    value="{{ old('name') }}" required>
                                @error('name')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3 row">
                            <label for="edit_type" class="col-sm-3 col-form-label text-end">Tipe Room</label>
                            <div class="col-sm-9">
                                <select class="form-select @error('type') is-invalid @enderror" id="edit_type"
                                    name="type" required>
                                    <option value="">Pilih Tipe Room</option>
                                    <option value="regular">Regular</option>
                                    <option value="vip">VIP</option>
                                    <option value="vvip">VVIP</option>
                                </select>
                                @error('type')
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
                                    rows="3" placeholder="Deskripsi room (opsional)">{{ old('description') }}</textarea>
                                @error('description')
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
        function openEditModal(id, name, type, description, status) {
            const form = document.getElementById('editRoomFrm');
            form.setAttribute('action', `{{ route('admin.rooms.index') }}/${id}`);

            document.getElementById('edit_id_room').value = id;
            document.getElementById('edit_name').value = name;
            document.getElementById('edit_type').value = type;
            document.getElementById('edit_description').value = description || '';
            document.getElementById('edit_status').checked = status == 1;

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
                document.getElementById('roomFrm').submit();
            });

            // Add event listener for the update button
            document.getElementById('btnUpdate').addEventListener('click', function() {
                document.getElementById('editRoomFrm').submit();
            });

            // Delete confirmation
            document.querySelectorAll('.delete-room').forEach(button => {
                button.addEventListener('click', function() {
                    const id = this.getAttribute('data-id');
                    const name = this.getAttribute('data-name');

                    Swal.fire({
                        title: 'Apakah Anda yakin?',
                        text: `Room "${name}" akan dihapus secara permanen!`,
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
                            form.action = `{{ route('admin.rooms.index') }}/${id}`;
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
