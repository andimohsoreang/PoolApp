@extends('layouts.app')

@section('title', 'Daftar Customer')

@section('content')
<div class="container-fluid">
    <!-- Page-Title -->
    <div class="row">
        <div class="col-sm-12">
            <div class="page-title-box">
                <div class="float-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="/admin/dashboard">Dashboard</a></li>
                        <li class="breadcrumb-item active">Daftar Customer</li>
                    </ol>
                </div>
                <h4 class="page-title">Daftar Customer</h4>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="row align-items-center">
                        <div class="col">
                            <h4 class="card-title">Manajemen Customer</h4>
                        </div>
                        <div class="col-auto">
                            <a href="{{ route('admin.customers.create') }}" class="btn btn-primary">
                                <i class="iconoir-plus"></i> Tambah Customer
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Filters -->
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <form action="{{ route('admin.customers.index') }}" method="GET" class="row g-3">
                                <div class="col-md-3">
                                    <div class="input-group">
                                        <input type="text" class="form-control" name="search" value="{{ request('search') }}" placeholder="Cari nama/email/telepon">
                                        <button class="btn btn-outline-secondary" type="submit">
                                            <i class="iconoir-search"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <select name="category" class="form-select" onchange="this.form.submit()">
                                        <option value="">Semua Kategori</option>
                                        <option value="member" {{ request('category') == 'member' ? 'selected' : '' }}>Member</option>
                                        <option value="non_member" {{ request('category') == 'non_member' ? 'selected' : '' }}>Non-Member</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <select name="status" class="form-select" onchange="this.form.submit()">
                                        <option value="">Semua Status</option>
                                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Aktif</option>
                                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Non-Aktif</option>
                                    </select>
                                </div>
                                <div class="col-md-auto">
                                    <a href="{{ route('admin.customers.index') }}" class="btn btn-outline-secondary">Reset</a>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Alert Messages -->
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <!-- Table -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped mb-0">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nama</th>
                                    <th>Email</th>
                                    <th>Telepon</th>
                                    <th>Kategori</th>
                                    <th>Status</th>
                                    <th>Tanggal Daftar</th>
                                    <th width="15%">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($customers as $customer)
                                    <tr>
                                        <td>{{ $customer->id }}</td>
                                        <td>{{ $customer->name }}</td>
                                        <td>{{ $customer->email }}</td>
                                        <td>{{ $customer->phone }}</td>
                                        <td>
                                            <span class="badge {{ $customer->category == 'member' ? 'bg-success' : 'bg-info' }}">
                                                {{ $customer->category == 'member' ? 'Member' : 'Non-Member' }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge {{ $customer->status ? 'bg-success' : 'bg-danger' }}">
                                                {{ $customer->status ? 'Aktif' : 'Non-Aktif' }}
                                            </span>
                                        </td>
                                        <td>{{ $customer->created_at->format('d M Y') }}</td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('admin.customers.show', $customer->id) }}" class="btn btn-sm btn-info">
                                                    <i class="iconoir-eye"></i>
                                                </a>
                                                <a href="{{ route('admin.customers.edit', $customer->id) }}" class="btn btn-sm btn-primary">
                                                    <i class="iconoir-edit-pencil"></i>
                                                </a>
                                                <a href="{{ route('admin.customers.transactions', $customer->id) }}" class="btn btn-sm btn-warning" title="Riwayat Transaksi">
                                                    <i class="iconoir-receipt"></i>
                                                </a>
                                                <button type="button" class="btn btn-sm {{ $customer->status ? 'btn-dark' : 'btn-success' }}"
                                                        onclick="toggleStatus({{ $customer->id }})" title="{{ $customer->status ? 'Nonaktifkan' : 'Aktifkan' }}">
                                                    <i class="iconoir-{{ $customer->status ? 'lock' : 'unlock' }}"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-warning" onclick="confirmSoftDelete({{ $customer->id }})" title="Hapus Sementara">
                                                    <i class="iconoir-bin"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-danger" onclick="confirmDelete({{ $customer->id }})" title="Hapus Permanen">
                                                    <i class="iconoir-trash"></i>
                                                </button>
                                            </div>

                                            <form id="toggleStatus-{{ $customer->id }}" action="{{ route('admin.customers.toggle-status', $customer->id) }}" method="POST" style="display: none;">
                                                @csrf
                                                @method('PUT')
                                            </form>

                                            <form id="softDelete-{{ $customer->id }}" action="{{ route('admin.customers.soft-delete', $customer->id) }}" method="POST" style="display: none;">
                                                @csrf
                                                @method('DELETE')
                                            </form>

                                            <form id="delete-{{ $customer->id }}" action="{{ route('admin.customers.destroy', $customer->id) }}" method="POST" style="display: none;">
                                                @csrf
                                                @method('DELETE')
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center">Tidak ada data customer</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-center mt-3">
                        {{ $customers->withQueryString()->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .btn-group .btn {
        margin-right: 2px;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function toggleStatus(id) {
        Swal.fire({
            title: 'Konfirmasi',
            text: 'Apakah Anda yakin ingin mengubah status customer ini?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, ubah status!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('toggleStatus-' + id).submit();
            }
        });
    }

    function confirmSoftDelete(id) {
        Swal.fire({
            title: 'Konfirmasi',
            text: 'Apakah Anda yakin ingin menonaktifkan customer ini? Customer akan dinonaktifkan tetapi datanya tetap tersimpan.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, nonaktifkan!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('softDelete-' + id).submit();
            }
        });
    }

    function confirmDelete(id) {
        Swal.fire({
            title: 'Peringatan!',
            text: 'PERHATIAN: Apakah Anda yakin ingin menghapus customer ini secara permanen? Tindakan ini tidak dapat dibatalkan dan semua data akan hilang.',
            icon: 'error',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, hapus permanen!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('delete-' + id).submit();
            }
        });
    }
</script>
@endpush
