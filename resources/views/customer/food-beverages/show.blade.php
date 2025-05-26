@extends('layouts.customer')

@section('title', $foodBeverage->name)

@section('styles')
<!-- Swiper CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.css" />
<style>
/* Food & Beverages Page Specific Styles */
.food-beverages-page {
    /* Variables */
    --primary-color: #3498db;
    --secondary-color: #2c3e50;
    --accent-color: #e74c3c;
    --text-color: #2c3e50;
    --text-light: #666;
    --bg-light: #f8f9fa;
    --white: #ffffff;
    --shadow-sm: 0 2px 4px rgba(0, 0, 0, 0.05);
    --shadow-md: 0 4px 6px rgba(0, 0, 0, 0.1);
    --shadow-lg: 0 10px 15px rgba(0, 0, 0, 0.1);
    --border-radius-sm: 8px;
    --border-radius-md: 12px;
    --border-radius-lg: 16px;
    --transition: all 0.3s ease;
}

.food-beverages-page .breadcrumb-wrapper {
    background: var(--bg-light);
    padding: 2rem 0;
    margin-bottom: 2rem;
    margin-top: 4rem;
}

.food-beverages-page .website-title h1 {
    font-size: 1.75rem;
    font-weight: 600;
    color: var(--text-color);
    margin-bottom: 0.5rem;
}

.food-beverages-page .breadcrumb {
    margin-bottom: 0;
}

.food-beverages-page .breadcrumb-item a {
    color: var(--primary-color);
    text-decoration: none;
}

.food-beverages-page .breadcrumb-item.active {
    color: var(--text-light);
}

.food-beverages-page .main-wrapper {
    padding-bottom: 5rem;
}

.food-beverages-page .item-detail-wrapper {
    background: var(--white);
    border-radius: var(--border-radius-lg);
    overflow: hidden;
    box-shadow: var(--shadow-md);
    padding: 2.5rem;
    margin-bottom: 2.5rem;
}

.food-beverages-page .gallery-container {
    margin-bottom: 1.5rem;
}

.food-beverages-page .main-swiper {
    width: 100%;
    border-radius: 16px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.07);
    background: #fff;
    margin-bottom: 1rem;
    position: relative;
}

.food-beverages-page .main-image-container {
    height: 340px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 16px;
    overflow: hidden;
    background: var(--bg-light);
}

.food-beverages-page .main-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.food-beverages-page .thumbs-swiper {
    height: 70px;
    box-sizing: border-box;
    padding: 0 10px;
}

.food-beverages-page .thumbnail-item {
    height: 64px;
    border-radius: 10px;
    overflow: hidden;
    cursor: pointer;
    border: 2px solid transparent;
    transition: border 0.2s;
    background: #fff;
    box-shadow: 0 1px 4px rgba(0,0,0,0.04);
}

.food-beverages-page .thumbnail-item img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.food-beverages-page .swiper-slide-thumb-active .thumbnail-item {
    border-color: var(--primary-color);
}

.food-beverages-page .swiper-button-next,
.food-beverages-page .swiper-button-prev {
    width: 36px;
    height: 36px;
    background: #fff;
    border-radius: 50%;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    transition: background 0.2s;
    top: 50%;
    transform: translateY(-50%);
}

.food-beverages-page .swiper-button-next:after,
.food-beverages-page .swiper-button-prev:after {
    font-size: 1.2rem;
    color: var(--primary-color);
}

.food-beverages-page .swiper-button-next:hover,
.food-beverages-page .swiper-button-prev:hover {
    background: var(--primary-color);
}

.food-beverages-page .swiper-button-next:hover:after,
.food-beverages-page .swiper-button-prev:hover:after {
    color: #fff;
}

.food-beverages-page .item-info {
    height: 100%;
    display: flex;
    flex-direction: column;
}

.food-beverages-page .item-info h2 {
    font-size: 2rem;
    font-weight: 700;
    color: var(--text-color);
    margin-bottom: 1rem;
}

.food-beverages-page .category-badge {
    display: inline-block;
    padding: 0.5rem 1rem;
    background: var(--bg-light);
    color: var(--text-color);
    border-radius: 20px;
    font-size: 0.875rem;
    font-weight: 500;
    margin-bottom: 1rem;
}

.food-beverages-page .rating-container {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin-bottom: 1rem;
}

.food-beverages-page .stars {
    color: #ffc107;
    font-size: 1rem;
}

.food-beverages-page .rating-text {
    color: var(--text-light);
    font-size: 0.875rem;
}

.food-beverages-page .price-container {
    margin: 1.5rem 0;
}

.food-beverages-page .price {
    font-size: 2rem;
    font-weight: 700;
    color: var(--primary-color);
}

.food-beverages-page .description {
    margin-top: 2rem;
}

.food-beverages-page .description h5 {
    font-size: 1.25rem;
    font-weight: 600;
    color: var(--text-color);
    margin-bottom: 1rem;
}

.food-beverages-page .description p {
    color: var(--text-light);
    line-height: 1.6;
    font-size: 1rem;
}

.food-beverages-page .rating-form-container {
    margin-top: 2rem;
    padding-top: 2rem;
    border-top: 1px solid #e2e8f0;
}

.food-beverages-page .rating-form-container h5 {
    font-size: 1.25rem;
    font-weight: 600;
    color: var(--text-color);
    margin-bottom: 1rem;
}

.food-beverages-page .modern-rating-stars {
    display: flex;
    flex-direction: row-reverse;
    justify-content: flex-start;
    font-size: 2rem;
    gap: 0.2rem;
}

.food-beverages-page .modern-rating-stars input[type="radio"] {
    display: none;
}

.food-beverages-page .modern-rating-stars label {
    cursor: pointer;
    color: #e0e0e0;
    transition: color 0.2s;
    padding: 0 2px;
}

.food-beverages-page .modern-rating-stars label svg {
    width: 2.1rem;
    height: 2.1rem;
    vertical-align: middle;
}

.food-beverages-page .modern-rating-stars input[type="radio"]:checked ~ label,
.food-beverages-page .modern-rating-stars label:hover,
.food-beverages-page .modern-rating-stars label:hover ~ label {
    color: #FFCD1A;
}

.food-beverages-page .modern-rating-stars label:active {
    transform: scale(0.95);
}

.food-beverages-page .reviews-container {
    margin-top: 1.5rem;
}

.food-beverages-page .review-card {
    background: var(--white);
    border-radius: var(--border-radius-md);
    padding: 1.5rem;
    margin-bottom: 1rem;
    box-shadow: var(--shadow-sm);
}

.food-beverages-page .review-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
}

.food-beverages-page .reviewer-info {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.food-beverages-page .avatar-placeholder {
    width: 40px;
    height: 40px;
    background: var(--primary-color);
    color: var(--white);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 1.125rem;
}

.food-beverages-page .reviewer-name {
    font-weight: 600;
    color: var(--text-color);
    margin-bottom: 0.25rem;
}

.food-beverages-page .review-date {
    color: var(--text-light);
    font-size: 0.875rem;
}

.food-beverages-page .review-rating {
    color: #ffc107;
}

.food-beverages-page .review-content {
    color: var(--text-color);
    line-height: 1.6;
}

.food-beverages-page .menu-card {
    background: var(--white);
    border-radius: var(--border-radius-md);
    overflow: hidden;
    box-shadow: var(--shadow-sm);
    transition: var(--transition);
    height: 100%;
}

.food-beverages-page .menu-card:hover {
    transform: translateY(-5px);
    box-shadow: var(--shadow-lg);
}

.food-beverages-page .menu-img {
    height: 200px;
    position: relative;
    overflow: hidden;
}

.food-beverages-page .menu-img img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: var(--transition);
}

.food-beverages-page .menu-card:hover .menu-img img {
    transform: scale(1.05);
}

.food-beverages-page .menu-content {
    padding: 1.5rem;
}

.food-beverages-page .menu-content h3 {
    font-size: 1.25rem;
    font-weight: 600;
    color: var(--text-color);
    margin-bottom: 0.75rem;
}

.food-beverages-page .rating {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 0.75rem;
}

.food-beverages-page .rating .stars {
    color: #ffc107;
    font-size: 0.875rem;
}

.food-beverages-page .rating-count {
    color: var(--text-light);
    font-size: 0.875rem;
}

.food-beverages-page .btn {
    padding: 0.75rem 1.5rem;
    font-size: 1rem;
    border-radius: var(--border-radius-sm);
    font-weight: 500;
    transition: var(--transition);
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}

.food-beverages-page .btn-primary {
    background-color: var(--primary-color);
    color: var(--white);
    border: none;
}

.food-beverages-page .btn-primary:hover {
    background-color: #2980b9;
    transform: translateY(-2px);
}

.food-beverages-page .btn-outline-primary {
    background-color: transparent;
    color: var(--primary-color);
    border: 2px solid var(--primary-color);
}

.food-beverages-page .btn-outline-primary:hover {
    background-color: var(--primary-color);
    color: var(--white);
    transform: translateY(-2px);
}

.food-beverages-page .btn-sm {
    padding: 0.5rem 1rem;
    font-size: 0.875rem;
}

.food-beverages-page .alert {
    padding: 1rem 1.5rem;
    margin-bottom: 1.5rem;
    border-radius: var(--border-radius-sm);
    font-size: 1rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.food-beverages-page .alert-success {
    background-color: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.food-beverages-page .alert-danger {
    background-color: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

@media (max-width: 992px) {
    .food-beverages-page .main-image-container {
        height: 350px;
    }
}

@media (max-width: 768px) {
    .food-beverages-page .breadcrumb-wrapper {
        padding: 1.5rem 0;
        margin-top: 3.5rem;
    }

    .food-beverages-page .main-wrapper {
        padding-bottom: 4rem;
    }

    .food-beverages-page .item-detail-wrapper {
        padding: 1.75rem;
        margin-bottom: 2rem;
    }

    .food-beverages-page .main-image-container {
        height: 220px;
    }

    .food-beverages-page .item-info h2 {
        font-size: 1.75rem;
    }

    .food-beverages-page .price {
        font-size: 1.75rem;
    }

    .food-beverages-page .thumbs-swiper {
        height: 48px;
    }

    .food-beverages-page .thumbnail-item {
        height: 40px;
    }
}

@media (max-width: 576px) {
    .food-beverages-page .breadcrumb-wrapper {
        padding: 1.25rem 0;
        margin-top: 3rem;
    }

    .food-beverages-page .main-wrapper {
        padding-bottom: 3.5rem;
    }

    .food-beverages-page .item-detail-wrapper {
        padding: 1.25rem;
        margin-bottom: 1.75rem;
    }

    .food-beverages-page .main-image-container {
        height: 250px;
    }

    .food-beverages-page .item-info h2 {
        font-size: 1.5rem;
    }

    .food-beverages-page .price {
        font-size: 1.5rem;
    }

    .food-beverages-page .thumbs-swiper {
        height: 50px;
    }

    .food-beverages-page .thumbnail-item {
        height: 50px;
    }

    .food-beverages-page .modern-rating-stars {
        font-size: 1.4rem;
    }

    .food-beverages-page .modern-rating-stars label svg {
        width: 1.5rem;
        height: 1.5rem;
    }
}
</style>
@endsection

@section('content')
<div class="food-beverages-page">
    <!-- Breadcrumb -->
    <div class="breadcrumb-wrapper">
        <div class="container">
            <div class="website-title">
                <div>
                    <h1>{{ $foodBeverage->name }}</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('customer.dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('customer.food-beverages.index') }}">Menu F&B</a></li>
                            <li class="breadcrumb-item active" aria-current="page">{{ $foodBeverage->name }}</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <div class="main-wrapper">
        <div class="container">
            <!-- Flash Messages -->
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

            <!-- Item Details -->
            <div class="section">
                <div class="item-detail-wrapper">
                    <div class="row">
                        <!-- Item Gallery -->
                        <div class="col-lg-6">
                            <div class="gallery-container">
                                <div class="swiper main-swiper">
                                    <div class="swiper-wrapper">
                                        @if($foodBeverage->thumbnail)
                                            <div class="swiper-slide">
                                                <div class="main-image-container">
                                                    <img src="{{ asset('storage/' . $foodBeverage->thumbnail) }}" class="main-image" alt="{{ $foodBeverage->name }}">
                                                </div>
                                            </div>
                                        @endif
                                        @foreach($foodBeverage->images as $image)
                                            <div class="swiper-slide">
                                                <div class="main-image-container">
                                                    <img src="{{ asset('storage/' . $image->image_path) }}" class="main-image" alt="{{ $image->alt_text ?? $foodBeverage->name }}">
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                    <div class="swiper-button-next"></div>
                                    <div class="swiper-button-prev"></div>
                                </div>

                                @if($foodBeverage->images->count() > 0 || $foodBeverage->thumbnail)
                                    <div class="swiper thumbs-swiper mt-3">
                                        <div class="swiper-wrapper">
                                            @if($foodBeverage->thumbnail)
                                                <div class="swiper-slide">
                                                    <div class="thumbnail-item">
                                                        <img src="{{ asset('storage/' . $foodBeverage->thumbnail) }}" alt="Thumbnail">
                                                    </div>
                                                </div>
                                            @endif
                                            @foreach($foodBeverage->images as $image)
                                                <div class="swiper-slide">
                                                    <div class="thumbnail-item">
                                                        <img src="{{ asset('storage/' . $image->image_path) }}" alt="{{ $image->alt_text ?? $foodBeverage->name }}">
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Item Info -->
                        <div class="col-lg-6">
                            <div class="item-info">
                                <h2>{{ $foodBeverage->name }}</h2>

                                <div class="category-badge">
                                    {{ ucfirst($foodBeverage->category) }}
                                </div>

                                <div class="rating-container">
                                    <div class="stars">
                                        @for($i = 1; $i <= 5; $i++)
                                            @if($i <= round($foodBeverage->average_rating))
                                                <i class="fas fa-star"></i>
                                            @else
                                                <i class="far fa-star"></i>
                                            @endif
                                        @endfor
                                    </div>
                                    <span class="rating-text">
                                        @if($foodBeverage->rating_count > 0)
                                            {{ number_format($foodBeverage->average_rating, 1) }} dari {{ $foodBeverage->rating_count }} ulasan
                                        @else
                                            Belum ada ulasan
                                        @endif
                                    </span>
                                </div>

                                <div class="price-container">
                                    <span class="price">Rp {{ number_format($foodBeverage->price, 0, ',', '.') }}</span>
                                </div>

                                <div class="description">
                                    <h5>Deskripsi</h5>
                                    <p>{{ $foodBeverage->description ?? 'Tidak ada deskripsi tersedia.' }}</p>
                                </div>

                                <!-- User Rating Form -->
                                <div class="rating-form-container">
                                    <h5>Berikan Ulasan</h5>

                                    @guest
                                        <div class="alert alert-info">
                                            <i class="fas fa-info-circle me-2"></i>
                                            Silakan <a href="{{ route('login') }}" class="alert-link">login</a> untuk memberikan ulasan.
                                        </div>
                                    @else
                                        @if($userRating)
                                            <div class="current-rating mb-3">
                                                <div class="alert alert-success">
                                                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start gap-3">
                                                        <div>
                                                            <h6 class="mb-2">Ulasan Anda</h6>
                                                            <div class="user-stars mb-2">
                                                                @for($i = 1; $i <= 5; $i++)
                                                                    @if($i <= $userRating->rating)
                                                                        <i class="fas fa-star text-warning"></i>
                                                                    @else
                                                                        <i class="far fa-star text-warning"></i>
                                                                    @endif
                                                                @endfor
                                                            </div>
                                                            @if($userRating->review)
                                                                <p class="mb-0">{{ $userRating->review }}</p>
                                                            @else
                                                                <p class="text-muted mb-0">Tidak ada komentar</p>
                                                            @endif
                                                        </div>
                                                        <div>
                                                            <form action="{{ route('customer.food-beverages.delete-rating', $foodBeverage) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus ulasan ini?')">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                                                    Hapus
                                                                </button>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <button type="button" class="btn btn-outline-primary" data-bs-toggle="collapse" data-bs-target="#editRatingForm">
                                                Edit Ulasan
                                            </button>

                                            <div class="collapse mt-3" id="editRatingForm">
                                                <form action="{{ route('customer.food-beverages.rate', $foodBeverage) }}" method="POST">
                                                    @csrf
                                                    <div class="mb-3">
                                                        <label class="form-label">Rating</label>
                                                        <div class="modern-rating-stars">
                                                            @for($i = 5; $i >= 1; $i--)
                                                                <input type="radio" name="rating" id="modern-rating-{{ $i }}" value="{{ $i }}" {{ $userRating->rating == $i ? 'checked' : '' }}>
                                                                <label for="modern-rating-{{ $i }}">
                                                                    <svg viewBox="0 0 20 20" fill="currentColor"><polygon points="10,1.5 12.59,7.36 19,8.27 14,13.14 15.18,19.5 10,16.27 4.82,19.5 6,13.14 1,8.27 7.41,7.36"/></svg>
                                                                </label>
                                                            @endfor
                                                        </div>
                                                    </div>

                                                    <div class="mb-3">
                                                        <label for="review" class="form-label">Komentar (Opsional)</label>
                                                        <textarea class="form-control" id="review" name="review" rows="3">{{ $userRating->review }}</textarea>
                                                    </div>

                                                    <button type="submit" class="btn btn-primary">
                                                        Simpan Perubahan
                                                    </button>
                                                </form>
                                            </div>
                                        @else
                                            <form action="{{ route('customer.food-beverages.rate', $foodBeverage) }}" method="POST">
                                                @csrf
                                                <div class="mb-3">
                                                    <label class="form-label">Rating</label>
                                                    <div class="modern-rating-stars">
                                                        @for($i = 5; $i >= 1; $i--)
                                                            <input type="radio" name="rating" id="modern-rating-{{ $i }}" value="{{ $i }}">
                                                            <label for="modern-rating-{{ $i }}">
                                                                <svg viewBox="0 0 20 20" fill="currentColor"><polygon points="10,1.5 12.59,7.36 19,8.27 14,13.14 15.18,19.5 10,16.27 4.82,19.5 6,13.14 1,8.27 7.41,7.36"/></svg>
                                                            </label>
                                                        @endfor
                                                    </div>
                                                    @error('rating')
                                                        <div class="text-danger">{{ $message }}</div>
                                                    @enderror
                                                </div>

                                                <div class="mb-3">
                                                    <label for="review" class="form-label">Komentar (Opsional)</label>
                                                    <textarea class="form-control" id="review" name="review" rows="3">{{ old('review') }}</textarea>
                                                    @error('review')
                                                        <div class="text-danger">{{ $message }}</div>
                                                    @enderror
                                                </div>

                                                <button type="submit" class="btn btn-primary">
                                                    Kirim Ulasan
                                                </button>
                                            </form>
                                        @endif
                                    @endguest
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Reviews Section -->
            @if($foodBeverage->ratings->count() > 0)
            <div class="section">
                <div class="section-header px-4">
                    <h2 class="section-title">Ulasan Pelanggan</h2>
                </div>

                <div class="reviews-container">
                    @foreach($foodBeverage->ratings as $rating)
                        @if($rating->is_approved)
                        <div class="review-card">
                            <div class="review-header">
                                <div class="reviewer-info">
                                    <div class="reviewer-avatar">
                                        <div class="avatar-placeholder">
                                            {{ strtoupper(substr($rating->user->name, 0, 1)) }}
                                        </div>
                                    </div>
                                    <div>
                                        <h6 class="reviewer-name">{{ $rating->user->name }}</h6>
                                        <div class="review-date">{{ $rating->created_at->format('d M Y') }}</div>
                                    </div>
                                </div>
                                <div class="review-rating">
                                    @for($i = 1; $i <= 5; $i++)
                                        @if($i <= $rating->rating)
                                            <i class="fas fa-star"></i>
                                        @else
                                            <i class="far fa-star"></i>
                                        @endif
                                    @endfor
                                </div>
                            </div>
                            <div class="review-content">
                                @if($rating->review)
                                    <p>{{ $rating->review }}</p>
                                @else
                                    <p class="text-muted"><em>Pelanggan tidak menulis komentar</em></p>
                                @endif
                            </div>
                        </div>
                        @endif
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Similar Items -->
            @if($similarItems->count() > 0)
            <div class="section">
                <div class="section-header px-4">
                    <h2 class="section-title">Menu Serupa</h2>
                </div>

                <div class="row g-4">
                    @foreach($similarItems as $item)
                    <div class="col-md-6 col-lg-3">
                        <div class="menu-card">
                            <div class="menu-img">
                                @if($item->thumbnail)
                                    <img src="{{ asset('storage/' . $item->thumbnail) }}" alt="{{ $item->name }}">
                                @elseif($item->images->count() > 0)
                                    <img src="{{ asset('storage/' . $item->images->first()->image_path) }}" alt="{{ $item->name }}">
                                @else
                                    <div class="placeholder-img">
                                        <i class="fas fa-utensils"></i>
                                    </div>
                                @endif
                            </div>
                            <div class="menu-content">
                                <h3>{{ $item->name }}</h3>
                                <div class="rating">
                                    <div class="stars">
                                        @for($i = 1; $i <= 5; $i++)
                                            @if($i <= round($item->average_rating))
                                                <i class="fas fa-star"></i>
                                            @else
                                                <i class="far fa-star"></i>
                                            @endif
                                        @endfor
                                    </div>
                                    <span class="rating-count">({{ $item->rating_count }})</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mt-3">
                                    <div class="price">Rp {{ number_format($item->price, 0, ',', '.') }}</div>
                                    <a href="{{ route('customer.food-beverages.show', $item) }}" class="btn btn-sm btn-outline-primary">Detail</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<!-- Swiper JS -->
<script src="https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Thumbs Swiper
    var thumbsSwiper = new Swiper('.thumbs-swiper', {
        spaceBetween: 10,
        slidesPerView: 4,
        freeMode: true,
        watchSlidesProgress: true,
    });
    // Main Swiper
    var mainSwiper = new Swiper('.main-swiper', {
        spaceBetween: 10,
        navigation: {
            nextEl: '.swiper-button-next',
            prevEl: '.swiper-button-prev',
        },
        thumbs: {
            swiper: thumbsSwiper
        }
    });
});
</script>
@endpush
