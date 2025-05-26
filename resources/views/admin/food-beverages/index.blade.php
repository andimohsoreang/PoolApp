@extends('layouts.app')

@section('title', 'Food & Beverage Menu')

@section('content')
<div class="container-fluid">
    <!-- Page-Title -->
    <div class="row">
        <div class="col-sm-12">
            <div class="page-title-box">
                <div class="float-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Food & Beverage Menu</li>
                    </ol>
                </div>
                <h4 class="page-title">Food & Beverage Menu</h4>
            </div>
        </div>
    </div>

    <!-- Flash Messages -->
    @if(session('success'))
    <div class="row">
        <div class="col-12">
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
    </div>
    @endif

    <!-- Search & Filters -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card">
                <div class="card-body p-3">
                    <form action="{{ route('admin.food-beverages.index') }}" method="GET" class="row g-2 align-items-center">
                        <div class="col-lg-4 col-md-6 col-12">
                            <div class="search-box">
                                <div class="position-relative">
                                    <input type="text" name="search" class="form-control rounded-pill" placeholder="Search menu items..." value="{{ request('search') }}">
                                    <i class="iconoir-search position-absolute top-50 translate-middle-y text-muted ms-2"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6 col-12">
                            <select name="category" class="form-select rounded-pill">
                                <option value="">All Categories</option>
                                <option value="food" {{ request('category') == 'food' ? 'selected' : '' }}>Food</option>
                                <option value="beverage" {{ request('category') == 'beverage' ? 'selected' : '' }}>Beverage</option>
                                <option value="snack" {{ request('category') == 'snack' ? 'selected' : '' }}>Snack</option>
                                <option value="other" {{ request('category') == 'other' ? 'selected' : '' }}>Other</option>
                            </select>
                        </div>
                        <div class="col-lg-3 col-md-6 col-12">
                            <select name="status" class="form-select rounded-pill">
                                <option value="">All Status</option>
                                <option value="available" {{ request('status') == 'available' ? 'selected' : '' }}>Available</option>
                                <option value="unavailable" {{ request('status') == 'unavailable' ? 'selected' : '' }}>Unavailable</option>
                                <option value="featured" {{ request('status') == 'featured' ? 'selected' : '' }}>Featured</option>
                            </select>
                        </div>
                        <div class="col-lg-2 col-md-6 col-12 d-grid">
                            <button type="submit" class="btn btn-primary rounded-pill">
                                <i class="iconoir-filter me-1"></i> Filter
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Menu List -->
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center flex-wrap mb-3">
                <h5 class="mb-0">
                    <i class="iconoir-menu me-1"></i> Menu Items
                    <span class="badge rounded-pill bg-primary">{{ $foodBeverages->total() }}</span>
                </h5>
                <a href="{{ route('admin.food-beverages.create') }}" class="btn btn-primary rounded-pill">
                    <i class="iconoir-plus me-1"></i><span class="d-none d-sm-inline">Add New Item</span>
                </a>
            </div>

            <!-- Grid view for mobile, Table for larger screens -->
            <div class="d-block d-lg-none">
                <!-- Mobile Card View -->
                <div class="row g-3">
                    @forelse($foodBeverages as $item)
                    <div class="col-md-6 col-12">
                        <div class="card h-100 border-0 shadow-sm">
                            <div class="row g-0">
                                <div class="col-4">
                                    <div class="h-100 d-flex align-items-center justify-content-center p-2 bg-light rounded-start">
                                        @if($item->thumbnail)
                                            <img src="{{ asset('storage/' . $item->thumbnail) }}" alt="{{ $item->name }}" class="img-fluid rounded" style="max-height: 100px; object-fit: cover;">
                                        @else
                                            <div class="d-flex align-items-center justify-content-center" style="width: 80px; height: 80px; border-radius: 50%; background-color: #f5f5f5;">
                                                <i class="iconoir-food fs-3 text-muted"></i>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-8">
                                    <div class="card-body py-2 px-3">
                                        <div class="d-flex align-items-start justify-content-between">
                                            <h6 class="card-title mb-1 text-truncate">{{ $item->name }}</h6>
                                            <span class="badge bg-{{ $item->is_available ? 'success' : 'danger' }} rounded-pill">{{ $item->is_available ? 'Available' : 'Unavailable' }}</span>
                                        </div>
                                        <div class="d-flex mb-2">
                                            <span class="badge bg-info rounded-pill me-2">{{ ucfirst($item->category) }}</span>
                                            @if($item->is_featured)
                                                <span class="badge bg-warning rounded-pill">Featured</span>
                                            @endif
                                        </div>
                                        <p class="card-text fs-6 text-primary fw-bold mb-1">Rp {{ number_format($item->price, 0, ',', '.') }}</p>

                                        @if($item->rating_count > 0)
                                        <div class="d-flex align-items-center small">
                                            <span class="me-1">{{ number_format($item->average_rating, 1) }}</span>
                                            <i class="iconoir-star-solid text-warning"></i>
                                            <span class="ms-1 text-muted">({{ $item->rating_count }})</span>
                                        </div>
                                        @endif

                                        <div class="mt-2 d-flex flex-wrap gap-1">
                                            <a href="{{ route('admin.food-beverages.show', $item) }}" class="btn btn-sm btn-outline-info rounded-pill px-2">
                                                <i class="iconoir-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.food-beverages.edit', $item) }}" class="btn btn-sm btn-outline-primary rounded-pill px-2">
                                                <i class="iconoir-edit"></i>
                                            </a>
                                            @if($item->rating_count > 0)
                                                <a href="{{ route('admin.food-beverages.manage-ratings', $item) }}" class="btn btn-sm btn-outline-warning rounded-pill px-2">
                                                    <i class="iconoir-star"></i>
                                                </a>
                                            @endif
                                            <form action="{{ route('admin.food-beverages.destroy', $item) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this item?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-2">
                                                    <i class="iconoir-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="col-12">
                        <div class="alert alert-info text-center">
                            <i class="iconoir-info-empty fs-4 d-block mb-2"></i>
                            <span>No menu items found.</span>
                            <div class="mt-3">
                                <a href="{{ route('admin.food-beverages.create') }}" class="btn btn-sm btn-primary rounded-pill">
                                    <i class="iconoir-plus me-1"></i> Add your first item
                                </a>
                            </div>
                        </div>
                    </div>
                    @endforelse
                </div>
            </div>

            <!-- Table view for larger screens -->
            <div class="card d-none d-lg-block">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="border-top-0" style="width: 50px;">ID</th>
                                    <th class="border-top-0" style="width: 80px;">Image</th>
                                    <th class="border-top-0">Name</th>
                                    <th class="border-top-0">Category</th>
                                    <th class="border-top-0">Price</th>
                                    <th class="border-top-0">Rating</th>
                                    <th class="border-top-0">Status</th>
                                    <th class="border-top-0 text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($foodBeverages as $item)
                                <tr>
                                    <td>{{ $item->id }}</td>
                                    <td>
                                        @if($item->thumbnail)
                                            <img src="{{ asset('storage/' . $item->thumbnail) }}" alt="{{ $item->name }}" width="50" height="50" class="rounded shadow-sm" style="object-fit: cover;">
                                        @else
                                            <div class="d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; border-radius: 50%; background-color: #f5f5f5;">
                                                <i class="iconoir-food text-muted"></i>
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        <h6 class="mb-0">{{ $item->name }}</h6>
                                        @if($item->is_featured)
                                            <span class="badge bg-warning rounded-pill mt-1">Featured</span>
                                        @endif
                                    </td>
                                    <td><span class="badge bg-info rounded-pill">{{ ucfirst($item->category) }}</span></td>
                                    <td class="fw-medium text-primary">Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="me-1">
                                                @if($item->rating_count > 0)
                                                    {{ number_format($item->average_rating, 1) }}
                                                @else
                                                    -
                                                @endif
                                            </div>
                                            @if($item->rating_count > 0)
                                                <i class="iconoir-star-solid text-warning"></i>
                                                <span class="ms-1 text-muted small">({{ $item->rating_count }})</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        @if($item->is_available)
                                            <span class="badge bg-success rounded-pill">Available</span>
                                        @else
                                            <span class="badge bg-danger rounded-pill">Unavailable</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <div class="d-flex gap-1 justify-content-end">
                                            <a href="{{ route('admin.food-beverages.show', $item) }}" class="btn btn-sm btn-soft-info rounded-pill" data-bs-toggle="tooltip" title="View details">
                                                <i class="iconoir-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.food-beverages.edit', $item) }}" class="btn btn-sm btn-soft-primary rounded-pill" data-bs-toggle="tooltip" title="Edit item">
                                                <i class="iconoir-edit"></i>
                                            </a>
                                            @if($item->rating_count > 0)
                                                <a href="{{ route('admin.food-beverages.manage-ratings', $item) }}" class="btn btn-sm btn-soft-warning rounded-pill" data-bs-toggle="tooltip" title="Manage ratings">
                                                    <i class="iconoir-star"></i>
                                                </a>
                                            @endif
                                            <form action="{{ route('admin.food-beverages.destroy', $item) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this item?');" style="display: inline-block;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-soft-danger rounded-pill" data-bs-toggle="tooltip" title="Delete item">
                                                    <i class="iconoir-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center py-4">
                                        <i class="iconoir-info-empty fs-4 d-block mb-2"></i>
                                        <span>No menu items found.</span>
                                        <div class="mt-3">
                                            <a href="{{ route('admin.food-beverages.create') }}" class="btn btn-sm btn-primary rounded-pill">
                                                <i class="iconoir-plus me-1"></i> Add your first item
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Pagination -->
            <div class="d-flex justify-content-center mt-4">
                {{ $foodBeverages->links() }}
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    /* Custom styles for soft buttons */
    .btn-soft-primary {
        background-color: rgba(13, 110, 253, 0.1);
        color: #0d6efd;
        border: none;
    }
    .btn-soft-primary:hover {
        background-color: rgba(13, 110, 253, 0.2);
        color: #0d6efd;
    }

    .btn-soft-info {
        background-color: rgba(13, 202, 240, 0.1);
        color: #0dcaf0;
        border: none;
    }
    .btn-soft-info:hover {
        background-color: rgba(13, 202, 240, 0.2);
        color: #0dcaf0;
    }

    .btn-soft-warning {
        background-color: rgba(255, 193, 7, 0.1);
        color: #ffc107;
        border: none;
    }
    .btn-soft-warning:hover {
        background-color: rgba(255, 193, 7, 0.2);
        color: #ffc107;
    }

    .btn-soft-danger {
        background-color: rgba(220, 53, 69, 0.1);
        color: #dc3545;
        border: none;
    }
    .btn-soft-danger:hover {
        background-color: rgba(220, 53, 69, 0.2);
        color: #dc3545;
    }

    /* Card animations */
    .card {
        transition: transform 0.2s, box-shadow 0.2s;
    }

    .card:hover {
        transform: translateY(-3px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }

    /* Responsive table handling */
    @media (max-width: 992px) {
        .table-responsive {
            border: none;
        }
    }

    /* Search box styling */
    .search-box .form-control {
        padding-left: 35px;
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Initialize tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });
    });
</script>
@endpush
