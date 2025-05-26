@extends('layouts.app')

@section('title', 'Detail Meja Biliar')

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
            <h4 class="page-title">Detail Meja Biliar</h4>
            <div class="">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="#">Master</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.billiard-tables.index') }}">Meja Biliar</a></li>
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
                        <h4 class="card-title">Informasi Meja</h4>
                    </div>
                    <div class="col-auto">
                        <a href="javascript:void(0);" class="btn btn-outline-primary btn-sm"
                            onclick="openEditModal('{{ $table->id }}', '{{ $table->table_number }}', '{{ $table->room_id }}', '{{ $table->capacity }}', '{{ $table->status }}', '{{ $table->description }}')">
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
                                <th scope="row" width="40%">Nomor Meja</th>
                                <td>{{ $table->table_number }}</td>
                            </tr>
                            <tr>
                                <th scope="row">Ruangan</th>
                                <td>
                                    @if ($table->room)
                                        <span class="badge bg-primary">{{ $table->room->name }}</span>
                                    @else
                                        <span class="badge bg-danger">Room Tidak Tersedia</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">Kapasitas</th>
                                <td>{{ $table->capacity }} orang</td>
                            </tr>
                            <tr>
                                <th scope="row">Status</th>
                                <td>
                                    @if ($table->status == 'normal')
                                        <span class="badge bg-success">Normal</span>
                                    @elseif($table->status == 'rusak')
                                        <span class="badge bg-danger">Rusak</span>
                                    @elseif($table->status == 'maintenance')
                                        <span class="badge bg-warning">Maintenance</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">Deskripsi</th>
                                <td>{{ $table->description ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th scope="row">Dibuat Pada</th>
                                <td>{{ $table->created_at->format('d M Y H:i') }}</td>
                            </tr>
                            <tr>
                                <th scope="row">Diperbarui Pada</th>
                                <td>{{ $table->updated_at->format('d M Y H:i') }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="d-flex justify-content-center mt-4">
                    <a href="{{ route('admin.billiard-tables.index') }}" class="btn btn-secondary">
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
                        <h4 class="card-title">Daftar Harga untuk Meja {{ $table->table_number }}</h4>
                    </div>
                    <div class="col-auto">
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                            data-bs-target="#addPriceModal">
                            <i class="fas fa-plus me-1"></i> Tambah Harga
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
                                <th>Waktu</th>
                                <th>Tipe Hari</th>
                                <th>Harga</th>
                                <th>Periode Berlaku</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($table->prices as $key => $price)
                            <tr>
                                <td>{{ $key + 1 }}</td>
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
                                    <a href="javascript:void(0);" class="btn btn-outline-primary btn-sm" onclick="openEditPriceModal('{{ $price->id }}')">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <a href="javascript:void(0);" class="btn btn-outline-danger btn-sm delete-price" data-id="{{ $price->id }}">
                                        <i class="fas fa-trash-alt"></i> Hapus
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center">Belum ada data harga untuk meja ini</td>
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
                                @foreach($table->room ? \App\Models\Room::where('status', true)->get() : [] as $room)
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

<!-- Modal Add Price -->
<div class="modal fade" id="addPriceModal" tabindex="-1" role="dialog" aria-labelledby="addPriceModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title" id="addPriceModalLabel">Tambah Harga</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addPriceForm" method="POST" action="{{ route('admin.prices.store') }}">
                    @csrf
                    <input type="hidden" name="table_id" value="{{ $table->id }}">

                    <div class="mb-3 row">
                        <label for="start_time" class="col-sm-3 col-form-label text-end">Waktu Mulai</label>
                        <div class="col-sm-9">
                            <input type="time" class="form-control" id="start_time" name="start_time" required>
                        </div>
                    </div>

                    <div class="mb-3 row">
                        <label for="end_time" class="col-sm-3 col-form-label text-end">Waktu Selesai</label>
                        <div class="col-sm-9">
                            <input type="time" class="form-control" id="end_time" name="end_time" required>
                        </div>
                    </div>

                    <div class="mb-3 row">
                        <label for="price" class="col-sm-3 col-form-label text-end">Harga (Rp)</label>
                        <div class="col-sm-9">
                            <input type="number" class="form-control" id="price" name="price" min="0" required>
                        </div>
                    </div>

                    <div class="mb-3 row">
                        <label for="day_type" class="col-sm-3 col-form-label text-end">Tipe Hari</label>
                        <div class="col-sm-9">
                            <select class="form-select" id="day_type" name="day_type" required>
                                <option value="weekday">Hari Kerja (Senin-Jumat)</option>
                                <option value="weekend">Akhir Pekan (Sabtu-Minggu)</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3 row">
                        <label for="valid_from" class="col-sm-3 col-form-label text-end">Berlaku Dari</label>
                        <div class="col-sm-9">
                            <input type="date" class="form-control" id="valid_from" name="valid_from">
                            <small class="text-muted">Biarkan kosong jika berlaku selamanya</small>
                        </div>
                    </div>

                    <div class="mb-3 row">
                        <label for="valid_until" class="col-sm-3 col-form-label text-end">Berlaku Sampai</label>
                        <div class="col-sm-9">
                            <input type="date" class="form-control" id="valid_until" name="valid_until">
                            <small class="text-muted">Biarkan kosong jika berlaku selamanya</small>
                        </div>
                    </div>

                    <div class="mb-3 row">
                        <label for="price_status" class="col-sm-3 col-form-label text-end">Status</label>
                        <div class="col-sm-9">
                            <div class="form-check form-switch form-switch-success">
                                <input class="form-check-input" type="checkbox" id="price_status" name="status" value="1" checked>
                                <label class="form-check-label" for="price_status">Aktif</label>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" id="btnAddPrice">Simpan</button>
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

    // Initialize when document is ready
    document.addEventListener('DOMContentLoaded', function() {
        // Add event listener for the update button
        document.getElementById('btnUpdate').addEventListener('click', function() {
            document.getElementById('editTableFrm').submit();
        });

        // Add event listener for the add price button
        document.getElementById('btnAddPrice').addEventListener('click', function() {
            document.getElementById('addPriceForm').submit();
        });

        // Delete confirmation for prices
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
                        form.action = `/admin/prices/${id}`;
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
