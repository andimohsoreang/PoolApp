@extends('layouts.app')

@section('title', 'Edit Food & Beverage Item')

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
                        <li class="breadcrumb-item active">Edit {{ $foodBeverage->name }}</li>
                    </ol>
                </div>
                <h4 class="page-title">Edit Food & Beverage Item</h4>
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
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Item Details</h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.food-beverages.update', $foodBeverage) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $foodBeverage->name) }}" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="category" class="form-label">Category <span class="text-danger">*</span></label>
                                    <select class="form-select @error('category') is-invalid @enderror" id="category" name="category" required>
                                        <option value="food" {{ (old('category', $foodBeverage->category) == 'food') ? 'selected' : '' }}>Food</option>
                                        <option value="beverage" {{ (old('category', $foodBeverage->category) == 'beverage') ? 'selected' : '' }}>Beverage</option>
                                        <option value="snack" {{ (old('category', $foodBeverage->category) == 'snack') ? 'selected' : '' }}>Snack</option>
                                        <option value="other" {{ (old('category', $foodBeverage->category) == 'other') ? 'selected' : '' }}>Other</option>
                                    </select>
                                    @error('category')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="price" class="form-label">Price (Rp) <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control @error('price') is-invalid @enderror" id="price" name="price" value="{{ old('price', $foodBeverage->price) }}" min="0" step="1000" required>
                                    @error('price')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="description" class="form-label">Description</label>
                                    <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="4">{{ old('description', $foodBeverage->description) }}</textarea>
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Current Thumbnail</label>
                                    <div class="current-thumbnail mb-2">
                                        @if($foodBeverage->thumbnail)
                                            <img src="{{ asset('storage/' . $foodBeverage->thumbnail) }}" alt="{{ $foodBeverage->name }}" class="img-thumbnail" style="max-height: 200px;">
                                        @else
                                            <div class="placeholder-image rounded" style="height: 200px; background-color: #f5f5f5; display: flex; align-items: center; justify-content: center;">
                                                <i class="iconoir-food" style="font-size: 3rem; color: #ccc;"></i>
                                            </div>
                                        @endif
                                    </div>

                                    <label class="form-label">Change Thumbnail</label>
                                    <div class="input-group">
                                        <input type="file" class="form-control @error('thumbnail') is-invalid @enderror" id="thumbnail" name="thumbnail" accept="image/*">
                                        <label class="input-group-text" for="thumbnail">
                                            <i class="iconoir-camera"></i>
                                        </label>
                                    </div>
                                    <div class="form-text">Leave empty to keep the current thumbnail. Recommended size: 500x500px. Max file size: 2MB</div>
                                    @error('thumbnail')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                    <div id="thumbnailPreview" class="mt-2 d-none">
                                        <img src="" alt="Thumbnail Preview" class="img-thumbnail" style="max-height: 200px;">
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="is_available" name="is_available" value="1" {{ old('is_available', $foodBeverage->is_available) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_available">Available for customers</label>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="is_featured" name="is_featured" value="1" {{ old('is_featured', $foodBeverage->is_featured) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_featured">Featured item (shown in highlights)</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr>

                        <!-- Additional Images Section -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h5>Additional Images</h5>

                                @if($foodBeverage->images->count() > 0)
                                    <div class="row g-3 mb-3">
                                        @foreach($foodBeverage->images as $image)
                                            <div class="col-md-3">
                                                <div class="card h-100">
                                                    <div class="position-relative">
                                                        <img src="{{ asset('storage/' . $image->image_path) }}" class="card-img-top" alt="{{ $image->alt_text ?? $foodBeverage->name }}" style="height: 150px; object-fit: cover;">
                                                        @if($image->is_primary)
                                                            <div class="badge bg-warning position-absolute top-0 end-0 m-2">Primary</div>
                                                        @endif
                                                    </div>
                                                    <div class="card-body">
                                                        <div class="d-flex justify-content-between">
                                                            @if(!$image->is_primary)
                                                                <form action="{{ route('admin.food-beverages.set-primary-image', $image) }}" method="POST">
                                                                    @csrf
                                                                    <button type="submit" class="btn btn-sm btn-outline-warning">
                                                                        Set as Primary
                                                                    </button>
                                                                </form>
                                                            @endif
                                                            <form action="{{ route('admin.food-beverages.delete-image', $image) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this image?');">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                                                    <i class="iconoir-trash"></i>
                                                                </button>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="alert alert-info">
                                        No additional images uploaded yet.
                                    </div>
                                @endif

                                <div class="mb-3">
                                    <label class="form-label">Add New Images</label>
                                    <div class="input-group">
                                        <input type="file" class="form-control @error('images.*') is-invalid @enderror" id="images" name="images[]" accept="image/*" multiple>
                                        <label class="input-group-text" for="images">
                                            <i class="iconoir-album"></i>
                                        </label>
                                    </div>
                                    <div class="form-text">You can select multiple images. Max file size: 2MB each</div>
                                    @error('images.*')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                    <div id="imagesPreview" class="mt-2 row g-2"></div>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-12 text-end">
                                <a href="{{ route('admin.food-beverages.index') }}" class="btn btn-secondary me-2">
                                    <i class="iconoir-cancel me-1"></i> Cancel
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="iconoir-save-floppy me-1"></i> Update Item
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Thumbnail preview
        const thumbnailInput = document.getElementById('thumbnail');
        const thumbnailPreview = document.getElementById('thumbnailPreview');

        thumbnailInput.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                const reader = new FileReader();

                reader.onload = function(e) {
                    thumbnailPreview.classList.remove('d-none');
                    thumbnailPreview.querySelector('img').src = e.target.result;
                }

                reader.readAsDataURL(this.files[0]);
            } else {
                thumbnailPreview.classList.add('d-none');
            }
        });

        // Multiple images preview
        const imagesInput = document.getElementById('images');
        const imagesPreview = document.getElementById('imagesPreview');

        imagesInput.addEventListener('change', function() {
            imagesPreview.innerHTML = '';

            if (this.files) {
                Array.from(this.files).forEach(file => {
                    const reader = new FileReader();

                    reader.onload = function(e) {
                        const col = document.createElement('div');
                        col.className = 'col-4 col-md-3';

                        const img = document.createElement('img');
                        img.src = e.target.result;
                        img.className = 'img-thumbnail w-100';
                        img.style.height = '120px';
                        img.style.objectFit = 'cover';

                        col.appendChild(img);
                        imagesPreview.appendChild(col);
                    }

                    reader.readAsDataURL(file);
                });
            }
        });
    });
</script>
@endpush
