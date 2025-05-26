@extends('layouts.app')

@section('title', 'Profil Admin')

@section('content')
<div class="container-fluid">
    <!-- Page-Title -->
    <div class="row">
        <div class="col-sm-12">
            <div class="page-title-box">
                <div class="float-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="/admin/dashboard">Dashboard</a></li>
                        <li class="breadcrumb-item active">Profil Admin</li>
                    </ol>
                </div>
                <h4 class="page-title">Profil Admin</h4>
            </div>
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

    <!-- Main Content -->
    <div class="row">
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <div class="row align-items-center">
                        <div class="col">
                            <h4 class="card-title">Informasi Profil</h4>
                        </div>
                        <div class="col-auto">
                            <div class="dropdown">
                                <a href="#" class="btn btn-sm btn-outline-light dropdown-toggle" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="iconoir-settings"></i>
                                </a>
                                <div class="dropdown-menu dropdown-menu-end">
                                    <a class="dropdown-item" href="{{ route('admin.profile.edit') }}">
                                        <i class="iconoir-edit-pencil me-2"></i>Edit Profil
                                    </a>
                                    <a class="dropdown-item" href="{{ route('admin.profile.change-password') }}">
                                        <i class="iconoir-key me-2"></i>Ubah Password
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="text-center">
                        <img src="{{ asset('dist/assets/images/users/default-user.png') }}" alt="profile-image" class="rounded-circle img-thumbnail avatar-xl">
                        <div class="mt-3">
                            <h5>{{ $user->name }}</h5>
                            <p class="text-muted mb-0">{{ $user->email }}</p>
                            <p class="text-muted mb-0">{{ $user->phone }}</p>
                            <span class="badge bg-primary mt-1">Administrator</span>
                        </div>
                    </div>

                    <div class="mt-4">
                        <h5 class="font-size-14">Informasi Kontak</h5>
                        <div class="table-responsive">
                            <table class="table table-borderless mb-0">
                                <tbody>
                                    <tr>
                                        <th scope="row">Email :</th>
                                        <td>{{ $user->email }}</td>
                                    </tr>
                                    <tr>
                                        <th scope="row">Telepon :</th>
                                        <td>{{ $user->phone ?: '-' }}</td>
                                    </tr>
                                    <tr>
                                        <th scope="row">WhatsApp :</th>
                                        <td>{{ $user->whatsapp ?: '-' }}</td>
                                    </tr>
                                    <tr>
                                        <th scope="row">Jenis Kelamin :</th>
                                        <td>{{ $user->gender == 'male' ? 'Laki-laki' : ($user->gender == 'female' ? 'Perempuan' : '-') }}</td>
                                    </tr>
                                    <tr>
                                        <th scope="row">Umur :</th>
                                        <td>{{ $user->age ?: '-' }}</td>
                                    </tr>
                                    <tr>
                                        <th scope="row">Alamat :</th>
                                        <td>{{ $user->address ?: '-' }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Aktivitas Akun</h4>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center mb-4">
                        <div class="avatar-md me-3">
                            <span class="avatar-title rounded-circle bg-light text-primary">
                                <i class="iconoir-user-circle text-primary font-size-24"></i>
                            </span>
                        </div>
                        <div class="flex-grow-1">
                            <h5 class="font-size-15 mb-1">{{ $user->name }}</h5>
                            <p class="text-muted mb-0">Administrator</p>
                        </div>
                        <div>
                            <div class="btn-group">
                                <a href="{{ route('admin.profile.edit') }}" class="btn btn-primary btn-sm">
                                    <i class="iconoir-edit-pencil me-1"></i> Edit Profil
                                </a>
                                <a href="{{ route('admin.profile.change-password') }}" class="btn btn-info btn-sm">
                                    <i class="iconoir-key me-1"></i> Ubah Password
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="card border">
                                <div class="card-body">
                                    <h5 class="card-title mb-3">Informasi Akun</h5>
                                    <div class="table-responsive">
                                        <table class="table table-borderless mb-0">
                                            <tbody>
                                                <tr>
                                                    <th scope="row">Username :</th>
                                                    <td>{{ $user->username ?: '-' }}</td>
                                                </tr>
                                                <tr>
                                                    <th scope="row">Status :</th>
                                                    <td>
                                                        <span class="badge bg-success">Aktif</span>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th scope="row">Terakhir Login :</th>
                                                    <td>{{ $user->last_login ? $user->last_login->format('d M Y H:i') : '-' }}</td>
                                                </tr>
                                                <tr>
                                                    <th scope="row">Tanggal Daftar :</th>
                                                    <td>{{ $user->created_at->format('d M Y') }}</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card border">
                                <div class="card-body">
                                    <h5 class="card-title mb-3">Keamanan Akun</h5>
                                    <ul class="list-unstyled mb-0">
                                        <li class="mb-3">
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-xs me-3">
                                                    <span class="avatar-title rounded-circle bg-success text-white">
                                                        <i class="iconoir-lock"></i>
                                                    </span>
                                                </div>
                                                <div>
                                                    <p class="mb-0">Password</p>
                                                    <small class="text-muted">Terakhir diubah: {{ $user->password_changed_at ? $user->password_changed_at->format('d M Y') : 'Tidak ada data' }}</small>
                                                </div>
                                                <div class="ms-auto">
                                                    <a href="{{ route('admin.profile.change-password') }}" class="btn btn-sm btn-outline-primary">
                                                        Ubah
                                                    </a>
                                                </div>
                                            </div>
                                        </li>
                                        <li>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-xs me-3">
                                                    <span class="avatar-title rounded-circle bg-info text-white">
                                                        <i class="iconoir-user-circle"></i>
                                                    </span>
                                                </div>
                                                <div>
                                                    <p class="mb-0">Profil</p>
                                                    <small class="text-muted">Terakhir diperbarui: {{ $user->updated_at->format('d M Y') }}</small>
                                                </div>
                                                <div class="ms-auto">
                                                    <a href="{{ route('admin.profile.edit') }}" class="btn btn-sm btn-outline-primary">
                                                        Edit
                                                    </a>
                                                </div>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection