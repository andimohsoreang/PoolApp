@extends('layouts.customer')

@section('title', 'Menu F&B')

@section('styles')
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

.food-beverages-page .hero-section {
    background: linear-gradient(135deg, var(--secondary-color) 0%, var(--primary-color) 100%);
    padding: 4rem 0;
    color: var(--white);
    position: relative;
    overflow: hidden;
}

.food-beverages-page .hero-content {
    position: relative;
    text-align: center;
    max-width: 800px;
    margin: 0 auto;
    padding: 0 1rem;
}

.food-beverages-page .hero-title {
    font-size: 3rem;
    font-weight: 700;
    margin-bottom: 1rem;
    line-height: 1.2;
    text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.food-beverages-page .hero-subtitle {
    font-size: 1.25rem;
    opacity: 0.9;
    max-width: 600px;
    margin: 0 auto;
}

.food-beverages-page .main-content {
    padding: 3rem 0;
    padding-top: 6rem;
    padding-bottom: 5rem;
}

.food-beverages-page .container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 1rem;
}

.food-beverages-page .section-header {
    margin-bottom: 2rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.food-beverages-page .section-title {
    font-size: 1.75rem;
    font-weight: 600;
    color: var(--text-color);
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.food-beverages-page .featured-section {
    margin-bottom: 3rem;
}

.food-beverages-page .featured-carousel {
    background: var(--white);
    border-radius: var(--border-radius-lg);
    padding: 1.5rem;
    box-shadow: var(--shadow-md);
}

.food-beverages-page .feature-card {
    background: var(--white);
    border-radius: var(--border-radius-md);
    overflow: hidden;
    box-shadow: var(--shadow-sm);
    transition: var(--transition);
    height: 100%;
    position: relative;
}

.food-beverages-page .feature-card:hover {
    transform: translateY(-5px);
    box-shadow: var(--shadow-lg);
}

.food-beverages-page .feature-img {
    position: relative;
    height: 200px;
    overflow: hidden;
}

.food-beverages-page .feature-img img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: var(--transition);
}

.food-beverages-page .feature-card:hover .feature-img img {
    transform: scale(1.05);
}

.food-beverages-page .feature-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    padding: 1rem;
    background: linear-gradient(to bottom, rgba(0,0,0,0.4) 0%, transparent 100%);
}

.food-beverages-page .feature-category {
    background: rgba(0, 0, 0, 0.7);
    color: var(--white);
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-size: 0.875rem;
    font-weight: 500;
    backdrop-filter: blur(4px);
}

.food-beverages-page .feature-content {
    padding: 1.5rem;
}

.food-beverages-page .feature-title {
    font-size: 1.25rem;
    font-weight: 600;
    margin-bottom: 0.75rem;
    color: var(--text-color);
}

.food-beverages-page .feature-desc {
    color: var(--text-light);
    font-size: 0.9375rem;
    margin-bottom: 1rem;
    line-height: 1.5;
}

.food-beverages-page .feature-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 1rem;
}

.food-beverages-page .feature-price {
    font-size: 1.125rem;
    font-weight: 600;
    color: var(--primary-color);
}

.food-beverages-page .search-filter-section {
    margin-bottom: 3rem;
}

.food-beverages-page .search-filter-form {
    background: var(--white);
    padding: 1.5rem;
    border-radius: var(--border-radius-lg);
    box-shadow: var(--shadow-md);
    z-index: 10;
    position: relative;
}

.food-beverages-page .search-box {
    position: relative;
}

.food-beverages-page .search-box i {
    position: absolute;
    left: 1rem;
    top: 50%;
    transform: translateY(-50%);
    color: var(--text-light);
    font-size: 1rem;
}

.food-beverages-page .search-box input {
    padding: 0.75rem 1rem 0.75rem 2.5rem;
    height: 48px;
    border-radius: var(--border-radius-sm);
    font-size: 1rem;
    border: 1px solid #e2e8f0;
    width: 100%;
    transition: var(--transition);
}

.food-beverages-page .search-box input:focus {
    border-color: var(--primary-color);
    box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
}

.food-beverages-page .select-box select {
    height: 48px;
    border-radius: var(--border-radius-sm);
    font-size: 1rem;
    border: 1px solid #e2e8f0;
    padding: 0 1rem;
    width: 100%;
    transition: var(--transition);
    background-color: var(--white);
    cursor: pointer;
}

.food-beverages-page .select-box select:focus {
    border-color: var(--primary-color);
    box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
}

.food-beverages-page .menu-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 2rem;
    margin-bottom: 3rem;
}

.food-beverages-page .menu-card {
    background: var(--white);
    border-radius: var(--border-radius-md);
    overflow: hidden;
    box-shadow: var(--shadow-sm);
    transition: var(--transition);
    height: 100%;
    position: relative;
}

.food-beverages-page .menu-card:hover {
    transform: translateY(-5px);
    box-shadow: var(--shadow-lg);
}

.food-beverages-page .menu-img {
    position: relative;
    height: 200px;
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

.food-beverages-page .menu-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    padding: 1rem;
    background: linear-gradient(to bottom, rgba(0,0,0,0.4) 0%, transparent 100%);
}

.food-beverages-page .menu-category {
    background: rgba(0, 0, 0, 0.7);
    color: var(--white);
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-size: 0.875rem;
    font-weight: 500;
    backdrop-filter: blur(4px);
}

.food-beverages-page .menu-content {
    padding: 1.5rem;
}

.food-beverages-page .menu-title {
    font-size: 1.25rem;
    font-weight: 600;
    margin-bottom: 0.75rem;
    color: var(--text-color);
}

.food-beverages-page .menu-rating {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 0.75rem;
}

.food-beverages-page .stars {
    color: #ffc107;
    font-size: 0.875rem;
}

.food-beverages-page .rating-count {
    color: var(--text-light);
    font-size: 0.875rem;
}

.food-beverages-page .menu-desc {
    color: var(--text-light);
    font-size: 0.9375rem;
    margin-bottom: 1rem;
    line-height: 1.5;
}

.food-beverages-page .menu-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 1rem;
}

.food-beverages-page .menu-price {
    font-size: 1.125rem;
    font-weight: 600;
    color: var(--primary-color);
}

.food-beverages-page .empty-state {
    text-align: center;
    padding: 4rem 2rem;
    background: var(--white);
    border-radius: var(--border-radius-lg);
    box-shadow: var(--shadow-md);
}

.food-beverages-page .empty-state-icon {
    font-size: 3rem;
    color: var(--text-light);
    margin-bottom: 1.5rem;
}

.food-beverages-page .empty-state h3 {
    font-size: 1.5rem;
    color: var(--text-color);
    margin-bottom: 0.75rem;
}

.food-beverages-page .empty-state p {
    color: var(--text-light);
    margin-bottom: 1.5rem;
    font-size: 1rem;
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

.food-beverages-page .pagination-wrapper {
    margin-top: 3rem;
}

.food-beverages-page .pagination {
    display: flex;
    justify-content: center;
    gap: 0.5rem;
    list-style: none;
}

.food-beverages-page .pagination .page-item .page-link {
    padding: 0.5rem 1rem;
    font-size: 1rem;
    border-radius: var(--border-radius-sm);
    color: var(--text-color);
    background-color: var(--white);
    border: 1px solid #e2e8f0;
    transition: var(--transition);
}

.food-beverages-page .pagination .page-item.active .page-link {
    background-color: var(--primary-color);
    color: var(--white);
    border-color: var(--primary-color);
}

.food-beverages-page .pagination .page-item .page-link:hover {
    background-color: #f8f9fa;
    border-color: var(--primary-color);
}

@media (max-width: 1200px) {
    .food-beverages-page .container {
        max-width: 960px;
    }
}

@media (max-width: 992px) {
    .food-beverages-page .container {
        max-width: 720px;
    }

    .food-beverages-page .hero-title {
        font-size: 2.5rem;
    }

    .food-beverages-page .menu-grid {
        grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
    }
}

@media (max-width: 768px) {
    .food-beverages-page .container {
        max-width: 540px;
    }

    .food-beverages-page .hero-section {
        padding: 3rem 0;
    }

    .food-beverages-page .hero-title {
        font-size: 2rem;
    }

    .food-beverages-page .hero-subtitle {
        font-size: 1.125rem;
    }

    .food-beverages-page .section-title {
        font-size: 1.5rem;
    }

    .food-beverages-page .search-filter-form {
        padding: 1.25rem;
    }

    .food-beverages-page .menu-grid {
        grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
        gap: 1.5rem;
    }

    .food-beverages-page .feature-img,
    .food-beverages-page .menu-img {
        height: 180px;
    }
}

@media (max-width: 576px) {
    .food-beverages-page .hero-section {
        padding: 2.5rem 0;
    }

    .food-beverages-page .hero-title {
        font-size: 1.75rem;
    }

    .food-beverages-page .hero-subtitle {
        font-size: 1rem;
    }

    .food-beverages-page .section-title {
        font-size: 1.25rem;
    }

    .food-beverages-page .menu-grid {
        grid-template-columns: 1fr;
    }

    .food-beverages-page .feature-img,
    .food-beverages-page .menu-img {
        height: 220px;
    }

    .food-beverages-page .search-box input,
    .food-beverages-page .select-box select {
        height: 44px;
    }

    .food-beverages-page .feature-content,
    .food-beverages-page .menu-content {
        padding: 1.25rem;
    }

    .food-beverages-page .feature-title,
    .food-beverages-page .menu-title {
        font-size: 1.125rem;
    }

    .food-beverages-page .feature-desc,
    .food-beverages-page .menu-desc {
        font-size: 0.875rem;
    }

    .food-beverages-page .btn {
        padding: 0.625rem 1.25rem;
        font-size: 0.9375rem;
    }
}
</style>
@endsection

@section('content')
<div class="food-beverages-page">
    <!-- Hero Section -->
    <div class="hero-section">
        <div class="container">
            <div class="hero-content">
                <h1 class="hero-title">Menu Makanan & Minuman</h1>
                <p class="hero-subtitle">Temukan makanan dan minuman yang tersedia di Billiard Pool kami.</p>
            </div>
        </div>
    </div>

    <div class="main-content">
        <div class="container">
            <!-- Flash Messages -->
            @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @endif

            @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @endif

            <!-- Featured Items Carousel -->
            @if($featuredItems->count() > 0)
            <section class="featured-section">
                <div class="section-header">
                    <h2 class="section-title">
                        <i class="fas fa-star me-2"></i>
                        Menu Pilihan
                    </h2>
                </div>
                <div class="featured-carousel">
                    <div class="swiper featured-swiper">
                        <div class="swiper-wrapper">
                            @foreach($featuredItems as $featuredItem)
                            <div class="swiper-slide">
                                <div class="feature-card">
                                    <div class="feature-img">
                                        @if($featuredItem->thumbnail)
                                            <img src="{{ asset('storage/' . $featuredItem->thumbnail) }}"
                                                 alt="{{ $featuredItem->name }}"
                                                 loading="lazy">
                                        @else
                                            <div class="placeholder-img">
                                                <i class="fas fa-utensils"></i>
                                            </div>
                                        @endif
                                        <div class="feature-overlay">
                                            <span class="feature-category">{{ ucfirst($featuredItem->category) }}</span>
                                        </div>
                                    </div>
                                    <div class="feature-content">
                                        <h3 class="feature-title">{{ $featuredItem->name }}</h3>
                                        <p class="feature-desc">{{ Str::limit($featuredItem->description, 60) }}</p>
                                        <div class="feature-footer">
                                            <div class="feature-price">Rp {{ number_format($featuredItem->price, 0, ',', '.') }}</div>
                                            <a href="{{ route('customer.food-beverages.show', $featuredItem) }}"
                                               class="btn btn-primary btn-sm">
                                                <i class="fas fa-info-circle me-1"></i>
                                                Detail
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        <div class="swiper-pagination"></div>
                    </div>
                </div>
            </section>
            @endif

            <!-- Search and Filter Section -->
            <section class="search-filter-section">
                <form action="{{ route('customer.food-beverages.index') }}" method="GET" class="search-filter-form">
                    <div class="row g-3">
                        <div class="col-12 col-md-5 mb-2">
                            <div class="search-box">
                                <input type="text"
                                       name="search"
                                       class="form-control"
                                       placeholder="Cari menu..."
                                       value="{{ $search }}"
                                       autocomplete="off">
                            </div>
                        </div>
                        <div class="col-12 col-md-5 mb-2">
                            <div class="select-box">
                                <select name="category" class="form-select">
                                    <option value="all" {{ $category == 'all' ? 'selected' : '' }}>Semua Kategori</option>
                                    @foreach($categories as $cat)
                                        <option value="{{ $cat }}" {{ $category == $cat ? 'selected' : '' }}>
                                            {{ ucfirst($cat) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-12 col-md-2">
                            <button type="submit" class="btn btn-primary w-100">
                                Filter
                            </button>
                        </div>
                    </div>
                </form>
            </section>

            <!-- Menu Items Section -->
            <section class="menu-section">
                <div class="section-header">
                    <h2 class="section-title">
                        <i class="fas fa-utensils me-2"></i>
                        Menu Kami
                    </h2>
                </div>

                @if($items->count() > 0)
                <div class="menu-grid">
                    @foreach($items as $item)
                    <div class="menu-card">
                        <div class="menu-img">
                            @if($item->thumbnail)
                                <img src="{{ asset('storage/' . $item->thumbnail) }}"
                                     alt="{{ $item->name }}"
                                     loading="lazy">
                            @else
                                @if($item->images->count() > 0)
                                    <img src="{{ asset('storage/' . $item->images->first()->image_path) }}"
                                         alt="{{ $item->name }}"
                                         loading="lazy">
                                @else
                                    <div class="placeholder-img">
                                        <i class="fas fa-utensils"></i>
                                    </div>
                                @endif
                            @endif
                            <div class="menu-overlay">
                                <span class="menu-category">{{ ucfirst($item->category) }}</span>
                            </div>
                        </div>
                        <div class="menu-content">
                            <h3 class="menu-title">{{ $item->name }}</h3>
                            <div class="menu-rating">
                                <div class="stars">
                                    @for($i = 1; $i <= 5; $i++)
                                        @if($i <= round($item->average_rating))
                                            <img src="{{ asset('Travgo/preview/assets/svg/star-yellow.svg') }}" alt="star" class="star-icon">
                                        @else
                                            <img src="{{ asset('Travgo/preview/assets/svg/star-yellow.svg') }}" alt="star" class="star-icon" style="opacity: 0.3">
                                        @endif
                                    @endfor
                                </div>
                                <span class="rating-count">
                                    @if($item->rating_count > 0)
                                        {{ number_format($item->average_rating, 1) }}
                                    @else
                                        0.0
                                    @endif
                                </span>
                            </div>
                            <p class="menu-desc">{{ Str::limit($item->description, 80) }}</p>
                            <div class="menu-footer">
                                <div class="menu-price">Rp {{ number_format($item->price, 0, ',', '.') }}</div>
                                <a href="{{ route('customer.food-beverages.show', $item) }}"
                                   class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Detail
                                </a>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>

                <div class="pagination-wrapper">
                    {{ $items->appends(['category' => $category, 'search' => $search])->links() }}
                </div>
                @else
                <div class="empty-state">
                    <div class="empty-state-icon">
                        <i class="fas fa-utensils"></i>
                    </div>
                    <h3>Tidak ada menu yang ditemukan</h3>
                    <p>Coba ubah filter atau kata kunci pencarian Anda</p>
                    @if(!empty($search) || $category != 'all')
                        <a href="{{ route('customer.food-beverages.index') }}" class="btn btn-primary">
                            <i class="fas fa-redo me-1"></i>
                            Lihat Semua Menu
                        </a>
                    @endif
                </div>
                @endif
            </section>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize featured items swiper with improved settings
    new Swiper('.featured-swiper', {
        slidesPerView: 1,
        spaceBetween: 15,
        pagination: {
            el: '.swiper-pagination',
            clickable: true,
        },
        navigation: {
            nextEl: '.swiper-button-next',
            prevEl: '.swiper-button-prev',
        },
        breakpoints: {
            640: {
                slidesPerView: 2,
                spaceBetween: 15,
            },
            1024: {
                slidesPerView: 3,
                spaceBetween: 20,
            },
        },
        autoplay: {
            delay: 4000,
            disableOnInteraction: false,
        },
        loop: true,
        effect: 'slide',
        speed: 600,
    });

    // Lazy loading for images
    if ('loading' in HTMLImageElement.prototype) {
        const images = document.querySelectorAll('img[loading="lazy"]');
        images.forEach(img => {
            img.src = img.dataset.src;
        });
    } else {
        const script = document.createElement('script');
        script.src = 'https://cdnjs.cloudflare.com/ajax/libs/lazysizes/5.3.2/lazysizes.min.js';
        document.body.appendChild(script);
    }

    // Smooth scroll for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            document.querySelector(this.getAttribute('href')).scrollIntoView({
                behavior: 'smooth'
            });
        });
    });

    // Add touch support for better mobile experience
    document.querySelectorAll('.menu-card, .feature-card').forEach(card => {
        card.addEventListener('touchstart', function() {
            this.classList.add('touch-active');
        });

        card.addEventListener('touchend', function() {
            this.classList.remove('touch-active');
        });
    });
});
</script>
@endpush
