@extends('layouts.app')

@section('title', 'Profil Saya')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="page-title-box d-md-flex align-items-center justify-content-between">
                <h4 class="mb-md-0">Profil Saya</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('account.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Profil</li>
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
        <div class="col-lg-4">
            <div class="card">
                <div class="card-body">
                    <div class="text-center">
                        <div class="user-profile-img">
                            <div class="avatar-lg mx-auto">
                                <div class="avatar-title bg-soft-primary text-primary display-4 rounded-circle">
                                    <i class="mdi mdi-account-circle"></i>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4">
                            <h5 class="fs-16 mb-1">{{ $user->name }}</h5>
                            <p class="text-muted">{{ $customer->category === 'member' ? 'Member' : 'Non-Member' }}</p>
                        </div>

                        <div class="mt-4">
                            <a href="{{ route('account.profile.edit') }}" class="btn btn-primary btn-sm">
                                <i class="mdi mdi-pencil me-1"></i> Edit Profil
                            </a>
                        </div>
                    </div>

                    <hr class="my-4">

                    <div class="card border shadow-none mb-4">
                        <div class="card-header bg-transparent border-bottom py-3 px-4">
                            <h5 class="fs-16 mb-0">Informasi Kontak</h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="mb-3">
                                <div class="d-flex align-items-center mb-1">
                                    <div class="flex-shrink-0 me-2">
                                        <i class="mdi mdi-email text-muted fs-16"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <p class="mb-0 fs-14">Email</p>
                                    </div>
                                </div>
                                <div class="text-break ps-4">{{ $user->email ?? '-' }}</div>
                            </div>
                            <div class="mb-3">
                                <div class="d-flex align-items-center mb-1">
                                    <div class="flex-shrink-0 me-2">
                                        <i class="mdi mdi-phone text-muted fs-16"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <p class="mb-0 fs-14">No. Telepon</p>
                                    </div>
                                </div>
                                <div class="text-break ps-4">{{ $user->phone ?? '-' }}</div>
                            </div>
                            <div class="mb-3">
                                <div class="d-flex align-items-center mb-1">
                                    <div class="flex-shrink-0 me-2">
                                        <i class="mdi mdi-whatsapp text-muted fs-16"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <p class="mb-0 fs-14">WhatsApp</p>
                                    </div>
                                </div>
                                <div class="text-break ps-4">{{ $user->whatsapp ?? '-' }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card">
                <div class="card-header bg-transparent border-bottom">
                    <h5 class="card-title mb-0">Informasi Pribadi</h5>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <h6 class="fw-medium mb-3">Informasi Pengguna</h6>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-medium">Nama Lengkap</label>
                                <div class="form-control-plaintext">{{ $user->name }}</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-medium">Username</label>
                                <div class="form-control-plaintext">{{ $user->username }}</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-medium">Email</label>
                                <div class="form-control-plaintext">{{ $user->email }}</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-medium">Role</label>
                                <div class="form-control-plaintext">{{ ucfirst($user->role) }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <h6 class="fw-medium mb-3">Data Customer</h6>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-medium">Jenis Kelamin</label>
                                <div class="form-control-plaintext">
                                    @if($customer->gender == 'male')
                                    Laki-laki
                                    @elseif($customer->gender == 'female')
                                    Perempuan
                                    @else
                                    -
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-medium">Usia</label>
                                <div class="form-control-plaintext">{{ $customer->age ?? '-' }}</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-medium">Kategori</label>
                                <div class="form-control-plaintext">
                                    @if($customer->category == 'member')
                                    <span class="badge bg-success">Member</span>
                                    @else
                                    <span class="badge bg-secondary">Non-Member</span>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-medium">Jumlah Kunjungan</label>
                                <div class="form-control-plaintext">{{ $customer->visit_count ?? '0' }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-0">
                        <h6 class="fw-medium mb-3">Alamat</h6>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-medium">Alamat Asal</label>
                                <div class="form-control-plaintext">{{ $customer->origin_address ?? '-' }}</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-medium">Alamat Saat Ini</label>
                                <div class="form-control-plaintext">{{ $customer->current_address ?? '-' }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection