@extends('layouts.app')

@section('title', 'Detail Harga')

@push('links')
    <!-- Sweet Alert -->
    <link href="{{ asset('dist/assets/libs/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css">
@endpush

@section('content')
<div class="row">
    <div class="col-sm-12">
        <div class="page-title-box d-md-flex justify-content-md-between align-items-center">
            <h4 class="page-title">Detail Harga</h4>
            <div class="">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="#">Master</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.prices.index') }}">Harga</a></li>
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
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h4 class="card-title">Informasi Harga</h4>
                    </div>
                    <div class="col-auto">
                        <a href="javascript:void(0);" class="btn btn-outline-primary btn-sm edit-price"
                            data-id="{{ $price->id }}">
                            <i class="fas fa-edit me-1"></i> Edit
                        </a>
                        <a href="javascript:void(0);" class="btn btn-outline-danger btn-sm delete-price"
                            data-id="{{ $price->id }}">
                            <i class="fas fa-trash-alt me-1"></i> Hapus
                        </a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped mb-0">
                        <tbody>
                            <tr>
                                <th scope="row" width="30%">Meja</th>
                                <td>
                                    @if ($price->table && $price->table->room)
                                        <span class="badge bg-primary">{{ $price->table->table_number }} - {{ $price->table->room->name }}</span>
                                    @else
                                        <span class="badge bg-danger">Meja Tidak Tersedia</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">Waktu</th>
                                <td>{{ \Carbon\Carbon::parse($price->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($price->end_time)->format('H:i') }}</td>
                            </tr>
                            <tr>
                                <th scope="row">Tipe Hari</th>
                                <td>
                                    @if($price->day_type == 'weekday')
                                        <span class="badge bg-primary">Hari Kerja (Senin-Jumat)</span>
                                    @elseif($price->day_type == 'weekend')
                                        <span class="badge bg-info">Akhir Pekan (Sabtu-Minggu)</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">Harga</th>
                                <td>Rp {{ number_format($price->price, 0, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <th scope="row">Periode Berlaku</th>
                                <td>
                                    @if($price->valid_from && $price->valid_until)
                                        {{ \Carbon\Carbon::parse($price->valid_from)->format('d F Y') }} - {{ \Carbon\Carbon::parse($price->valid_until)->format('d F Y') }}
                                    @elseif($price->valid_from)
                                        Dari {{ \Carbon\Carbon::parse($price->valid_from)->format('d F Y') }}
                                    @elseif($price->valid_until)
                                        Sampai {{ \Carbon\Carbon::parse($price->valid_until)->format('d F Y') }}
                                    @else
                                        Tidak ada batas
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">Status</th>
                                <td>
                                    @if($price->status)
                                        <span class="badge bg-success">Aktif</span>
                                    @else
                                        <span class="badge bg-danger">Tidak Aktif</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">Dibuat Pada</th>
                                <td>{{ $price->created_at->format('d F Y H:i') }}</td>
                            </tr>
                            <tr>
                                <th scope="row">Diperbarui Pada</th>
                                <td>{{ $price->updated_at->format('d F Y H:i') }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="d-flex justify-content-center mt-4">
                    <a href="{{ route('admin.prices.index') }}" class="btn btn-secondary me-2">
                        <i class="fas fa-arrow-left me-1"></i> Kembali
                    </a>
                    @if ($price->table)
                        <a href="{{ route('admin.billiard-tables.show', $price->table_id) }}" class="btn btn-primary">
                            <i class="fas fa-eye me-1"></i> Lihat Meja
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="{{ asset('dist/assets/libs/sweetalert2/sweetalert2.min.js') }}"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Edit button click handler
        document.querySelector('.edit-price').addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            window.location.href = `{{ url('admin/prices') }}/${id}/edit`;
        });

        // Delete button click handler
        document.querySelector('.delete-price').addEventListener('click', function() {
            const id = this.getAttribute('data-id');

            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: 'Harga ini akan dihapus secara permanen!',
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
                    form.action = `{{ url('admin/prices') }}/${id}`;
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
</script>
@endpush