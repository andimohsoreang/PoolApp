@extends('layouts.app')

@section('title', 'View Food & Beverage Item')

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
                        <li class="breadcrumb-item active">{{ $foodBeverage->name }}</li>
                    </ol>
                </div>
                <h4 class="page-title">{{ $foodBeverage->name }}</h4>
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
        <div class="col-lg-6">
            <!-- Item Details -->
            <div class="card">
                <div class="card-header">
                    <div class="row align-items-center">
                        <div class="col">
                            <h4 class="card-title">Item Details</h4>
                        </div>
                        <div class="col-auto">
                            <div class="d-flex gap-2">
                                <a href="{{ route('admin.food-beverages.edit', $foodBeverage) }}" class="btn btn-primary btn-sm">
                                    <i class="iconoir-edit me-1"></i> Edit
                                </a>
                                <form action="{{ route('admin.food-beverages.destroy', $foodBeverage) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this item?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm">
                                        <i class="iconoir-trash me-1"></i> Delete
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="item-image-container">
                                @if($foodBeverage->thumbnail)
                                    <img src="{{ asset('storage/' . $foodBeverage->thumbnail) }}" alt="{{ $foodBeverage->name }}" class="img-fluid rounded">
                                @elseif($foodBeverage->images->count() > 0)
                                    <img src="{{ asset('storage/' . $foodBeverage->images->first()->image_path) }}" alt="{{ $foodBeverage->name }}" class="img-fluid rounded">
                                @else
                                    <div class="placeholder-image rounded" style="height: 200px; background-color: #f5f5f5; display: flex; align-items: center; justify-content: center;">
                                        <i class="iconoir-food" style="font-size: 3rem; color: #ccc;"></i>
                                    </div>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-2">
                                <span class="badge rounded-pill bg-info">{{ ucfirst($foodBeverage->category) }}</span>
                                @if($foodBeverage->is_available)
                                    <span class="badge rounded-pill bg-success">Available</span>
                                @else
                                    <span class="badge rounded-pill bg-danger">Unavailable</span>
                                @endif
                                @if($foodBeverage->is_featured)
                                    <span class="badge rounded-pill bg-warning">Featured</span>
                                @endif
                            </div>
                            <h3>{{ $foodBeverage->name }}</h3>
                            <p class="text-muted">{{ $foodBeverage->description ?? 'No description available.' }}</p>
                            <div class="d-flex align-items-center mb-2">
                                <h5 class="me-2 mb-0">Price:</h5>
                                <h4 class="text-primary mb-0">Rp {{ number_format($foodBeverage->price, 0, ',', '.') }}</h4>
                            </div>
                            <div class="d-flex align-items-center">
                                <h5 class="me-2 mb-0">Rating:</h5>
                                <div class="d-flex align-items-center">
                                    @if($foodBeverage->rating_count > 0)
                                        <span class="me-1">{{ number_format($foodBeverage->average_rating, 1) }}</span>
                                        <i class="iconoir-star-solid text-warning me-1"></i>
                                        <span class="text-muted">({{ $foodBeverage->rating_count }} ratings)</span>
                                    @else
                                        <span class="text-muted">No ratings yet</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-12">
                            <h5>Additional Information</h5>
                            <table class="table table-bordered">
                                <tbody>
                                    <tr>
                                        <th>ID</th>
                                        <td>{{ $foodBeverage->id }}</td>
                                    </tr>
                                    <tr>
                                        <th>Created At</th>
                                        <td>{{ $foodBeverage->created_at->format('d M Y, H:i') }}</td>
                                    </tr>
                                    <tr>
                                        <th>Last Updated</th>
                                        <td>{{ $foodBeverage->updated_at->format('d M Y, H:i') }}</td>
                                    </tr>
                                    <tr>
                                        <th>Status</th>
                                        <td>{{ $foodBeverage->is_available ? 'Available' : 'Unavailable' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Featured</th>
                                        <td>{{ $foodBeverage->is_featured ? 'Yes' : 'No' }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <!-- Item Gallery -->
            <div class="card mb-4">
                <div class="card-header">
                    <h4 class="card-title">Gallery Images</h4>
                </div>
                <div class="card-body">
                    @if($foodBeverage->images->count() > 0)
                        <div class="row g-3">
                            @foreach($foodBeverage->images as $image)
                                <div class="col-md-4">
                                    <div class="position-relative gallery-item">
                                        <img src="{{ asset('storage/' . $image->image_path) }}" alt="{{ $image->alt_text ?? $foodBeverage->name }}" class="img-fluid rounded">
                                        @if($image->is_primary)
                                            <div class="badge bg-warning position-absolute top-0 end-0 m-2">Primary</div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4">
                            <p>No gallery images available.</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Ratings -->
            <div class="card">
                <div class="card-header">
                    <div class="row align-items-center">
                        <div class="col">
                            <h4 class="card-title">Customer Ratings</h4>
                        </div>
                        <div class="col-auto">
                            @if($foodBeverage->ratings->count() > 0)
                                <a href="{{ route('admin.food-beverages.manage-ratings', $foodBeverage) }}" class="btn btn-sm btn-primary">
                                    <i class="iconoir-star me-1"></i> Manage Ratings
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    @if($foodBeverage->ratings->count() > 0)
                        <div class="ratings-summary mb-3">
                            <div class="d-flex align-items-center">
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

                        <div class="latest-ratings">
                            <h6>Latest Reviews</h6>
                            @foreach($foodBeverage->ratings->take(5) as $rating)
                                <div class="review-item p-3 mb-2 border rounded">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <strong>{{ $rating->user->name }}</strong>
                                            <div class="d-flex align-items-center">
                                                @for($i = 1; $i <= 5; $i++)
                                                    @if($i <= $rating->rating)
                                                        <i class="iconoir-star-solid text-warning small"></i>
                                                    @else
                                                        <i class="iconoir-star text-warning small"></i>
                                                    @endif
                                                @endfor
                                                <span class="text-muted ms-2 small">{{ $rating->created_at->format('d M Y') }}</span>
                                            </div>
                                        </div>
                                        <div>
                                            @if($rating->is_approved)
                                                <span class="badge bg-success">Approved</span>
                                            @else
                                                <span class="badge bg-danger">Hidden</span>
                                            @endif
                                        </div>
                                    </div>
                                    @if($rating->review)
                                        <p class="mt-2 mb-0">{{ $rating->review }}</p>
                                    @else
                                        <p class="mt-2 mb-0 text-muted"><em>No review provided</em></p>
                                    @endif
                                </div>
                            @endforeach

                            @if($foodBeverage->ratings->count() > 5)
                                <div class="text-center mt-3">
                                    <a href="{{ route('admin.food-beverages.manage-ratings', $foodBeverage) }}" class="btn btn-sm btn-outline-primary">
                                        View All Ratings
                                    </a>
                                </div>
                            @endif
                        </div>
                    @else
                        <div class="text-center py-4">
                            <p>No ratings available for this item yet.</p>
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

    .gallery-item {
        height: 150px;
        overflow: hidden;
    }

    .gallery-item img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
</style>
@endpush
