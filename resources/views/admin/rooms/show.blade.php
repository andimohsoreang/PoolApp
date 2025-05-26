@extends('layouts.app')

@section('title', 'Detail Room')

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
            <h4 class="page-title">Detail Room</h4>
            <div class="">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="#">Master</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.rooms.index') }}">Rooms</a></li>
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

<div class="row justify-content-center">
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h4 class="card-title">Informasi Room</h4>
                    </div>
                    <div class="col-auto">
                        <a href="javascript:void(0);" class="btn btn-outline-primary btn-sm"
                            onclick="openEditModal('{{ $room->id }}', '{{ $room->name }}', '{{ $room->type }}', '{{ $room->description }}', '{{ $room->status }}')">
                            <i class="fas fa-edit me-1"></i> Edit
                        </a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped mb-0">
                        <tbody>
                            <tr>
                                <th scope="row" width="40%">Nama Room</th>
                                <td>{{ $room->name }}</td>
                            </tr>
                            <tr>
                                <th scope="row">Tipe</th>
                                <td>
                                    @if ($room->type == 'regular')
                                        <span class="badge bg-primary">Regular</span>
                                    @elseif($room->type == 'vip')
                                        <span class="badge bg-info">VIP</span>
                                    @elseif($room->type == 'vvip')
                                        <span class="badge bg-danger">VVIP</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">Deskripsi</th>
                                <td>{{ $room->description ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th scope="row">Status</th>
                                <td>
                                    @if ($room->status)
                                        <span class="badge bg-success">Aktif</span>
                                    @else
                                        <span class="badge bg-danger">Tidak Aktif</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">Dibuat Pada</th>
                                <td>{{ $room->created_at->format('d M Y H:i') }}</td>
                            </tr>
                            <tr>
                                <th scope="row">Diperbarui Pada</th>
                                <td>{{ $room->updated_at->format('d M Y H:i') }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="d-flex justify-content-center mt-4">
                    <a href="{{ route('admin.rooms.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Kembali
                    </a>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h4 class="card-title">Daftar Meja di Ruangan {{ $room->name }}</h4>
                    </div>
                    <div class="col-auto">
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                            data-bs-target="#addTableModal">
                            <i class="fas fa-plus me-1"></i> Tambah Meja
                        </button>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table datatable" id="datatable_1">
                        <thead class="table-light">
                            <tr>
                                <th>No.</th>
                                <th>Nama Meja</th>
                                <th>Type</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($room->tables as $key => $table)
                            <tr>
                                <td>{{ $key + 1 }}</td>
                                <td>{{ $table->name }}</td>
                                <td>
                                    @if($table->type == 'regular')
                                        <span class="badge bg-primary">Regular</span>
                                    @elseif($table->type == 'vip')
                                        <span class="badge bg-info">VIP</span>
                                    @elseif($table->type == 'vvip')
                                        <span class="badge bg-danger">VVIP</span>
                                    @endif
                                </td>
                                <td>
                                    @if($table->status)
                                        <span class="badge bg-success">Aktif</span>
                                    @else
                                        <span class="badge bg-danger">Tidak Aktif</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="javascript:void(0);" class="btn btn-outline-primary btn-sm" onclick="openEditTableModal('{{ $table->id }}')">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <a href="javascript:void(0);" class="btn btn-outline-danger btn-sm delete-table" data-id="{{ $table->id }}" data-name="{{ $table->name }}">
                                        <i class="fas fa-trash-alt"></i> Hapus
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center">Belum ada meja di ruangan ini</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

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

<!-- Modal Add Table -->
<div class="modal fade" id="addTableModal" tabindex="-1" role="dialog" aria-labelledby="addTableModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title" id="addTableModalLabel">Tambah Meja</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addTableForm" method="POST" action="#">
                    @csrf
                    <input type="hidden" name="room_id" value="{{ $room->id }}">

                    <div class="mb-3 row">
                        <label for="table_name" class="col-sm-3 col-form-label text-end">Nama Meja</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" id="table_name" name="name" required>
                        </div>
                    </div>

                    <div class="mb-3 row">
                        <label for="table_type" class="col-sm-3 col-form-label text-end">Tipe Meja</label>
                        <div class="col-sm-9">
                            <select class="form-select" id="table_type" name="type" required>
                                <option value="">Pilih Tipe Meja</option>
                                <option value="regular">Regular</option>
                                <option value="vip">VIP</option>
                                <option value="vvip">VVIP</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3 row">
                        <label for="table_status" class="col-sm-3 col-form-label text-end">Status</label>
                        <div class="col-sm-9">
                            <div class="form-check form-switch form-switch-success">
                                <input class="form-check-input" type="checkbox" id="table_status" name="status" value="1" checked>
                                <label class="form-check-label" for="table_status">Aktif</label>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" id="btnAddTable">Simpan</button>
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

    // Initialize when document is ready
    document.addEventListener('DOMContentLoaded', function() {
        // Add event listener for the update button
        document.getElementById('btnUpdate').addEventListener('click', function() {
            document.getElementById('editRoomFrm').submit();
        });

        // Add event listener for the add table button
        document.getElementById('btnAddTable').addEventListener('click', function() {
            document.getElementById('addTableForm').submit();
        });

        // Delete confirmation for tables
        document.querySelectorAll('.delete-table').forEach(button => {
            button.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                const name = this.getAttribute('data-name');

                Swal.fire({
                    title: 'Apakah Anda yakin?',
                    text: `Meja "${name}" akan dihapus secara permanen!`,
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
                        form.action = `/admin/tables/${id}`;
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