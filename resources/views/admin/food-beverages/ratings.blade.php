@extends('layouts.app')

@section('title', 'Manage Ratings - ' . $foodBeverage->name)

@section('content')
<div class="container-fluid">
    <!-- Page-Title -->
    <div class="row">
        <div class="col-sm-12">
            <div class="page-title-box">
                <div class="float-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.food-beverages.index') }}">Food & Beverage Menu</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.food-beverages.show', $foodBeverage) }}">{{ $foodBeverage->name }}</a></li>
                        <li class="breadcrumb-item active">Manage Ratings</li>
                    </ol>
                </div>
                <h4 class="page-title">Manage Ratings - {{ $foodBeverage->name }}</h4>
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

    <div class="row">
        <div class="col-lg-4">
            <!-- Item Info Card -->
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Item Information</h4>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        @if($foodBeverage->thumbnail)
                            <img src="{{ asset('storage/' . $foodBeverage->thumbnail) }}" alt="{{ $foodBeverage->name }}" class="img-fluid rounded" style="max-height: 200px;">
                        @elseif($foodBeverage->images->count() > 0)
                            <img src="{{ asset('storage/' . $foodBeverage->images->first()->image_path) }}" alt="{{ $foodBeverage->name }}" class="img-fluid rounded" style="max-height: 200px;">
                        @else
                            <div class="placeholder-image rounded mx-auto" style="height: 200px; width: 200px; background-color: #f5f5f5; display: flex; align-items: center; justify-content: center;">
                                <i class="iconoir-food" style="font-size: 3rem; color: #ccc;"></i>
                            </div>
                        @endif
                    </div>

                    <h4 class="text-center mb-3">{{ $foodBeverage->name }}</h4>

                    <div class="mb-3">
                        <span class="badge bg-info">{{ ucfirst($foodBeverage->category) }}</span>
                        @if($foodBeverage->is_available)
                            <span class="badge bg-success">Available</span>
                        @else
                            <span class="badge bg-danger">Unavailable</span>
                        @endif
                        @if($foodBeverage->is_featured)
                            <span class="badge bg-warning">Featured</span>
                        @endif
                    </div>

                    <div class="ratings-summary text-center mb-4">
                        <div class="d-flex align-items-center justify-content-center">
                            <div class="rating-big me-3">{{ number_format($foodBeverage->average_rating, 1) }}</div>
                            <div>
                                <div class="stars">
                                    @for($i = 1; $i <= 5; $i++)
                                        @if($i <= round($foodBeverage->average_rating))
                                            <i class="iconoir-star-solid text-warning"></i>
                                        @else
                                            <i class="iconoir-star text-warning"></i>
                                        @endif
                                    @endfor
                                </div>
                                <div class="text-muted">{{ $foodBeverage->rating_count }} ratings</div>
                            </div>
                        </div>
                    </div>

                    <div class="d-grid">
                        <a href="{{ route('admin.food-beverages.show', $foodBeverage) }}" class="btn btn-outline-primary">
                            <i class="iconoir-eye me-1"></i> View Item Details
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <!-- Ratings List Card -->
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Customer Ratings</h4>
                </div>
                <div class="card-body">
                    @if($foodBeverage->ratings->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Customer</th>
                                        <th>Rating</th>
                                        <th>Review</th>
                                        <th>Date</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($foodBeverage->ratings as $rating)
                                    <tr>
                                        <td>{{ $rating->user->name }}</td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                @for($i = 1; $i <= 5; $i++)
                                                    @if($i <= $rating->rating)
                                                        <i class="iconoir-star-solid text-warning small"></i>
                                                    @else
                                                        <i class="iconoir-star text-muted small"></i>
                                                    @endif
                                                @endfor
                                            </div>
                                        </td>
                                        <td>
                                            @if($rating->review)
                                                <span class="text-truncate d-inline-block" style="max-width: 200px;" title="{{ $rating->review }}">
                                                    {{ $rating->review }}
                                                </span>
                                            @else
                                                <span class="text-muted">No review</span>
                                            @endif
                                        </td>
                                        <td>{{ $rating->created_at->format('d M Y') }}</td>
                                        <td>
                                            @if($rating->is_approved)
                                                <span class="badge bg-success">Approved</span>
                                            @else
                                                <span class="badge bg-danger">Hidden</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="d-flex gap-2">
                                                <form action="{{ route('admin.food-beverages.toggle-rating-approval', $rating->id) }}" method="POST">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm {{ $rating->is_approved ? 'btn-outline-danger' : 'btn-outline-success' }}">
                                                        {{ $rating->is_approved ? 'Hide' : 'Show' }}
                                                    </button>
                                                </form>
                                                <form action="{{ route('admin.food-beverages.delete-rating', $rating->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this rating?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger">
                                                        <i class="iconoir-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="alert alert-info">
                            No ratings available for this item yet.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .rating-big {
        font-size: 2.5rem;
        font-weight: bold;
        color: #343a40;
    }
</style>
@endpush
