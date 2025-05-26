@extends('layouts.app')

@section('title', 'Add Food & Beverage Item')

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
                        <li class="breadcrumb-item active">Add New Item</li>
                    </ol>
                </div>
                <h4 class="page-title">Add New Food & Beverage Item</h4>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Item Details</h4>
                </div>
                <div class="card-body">
                    <form id="createFoodBeverageForm" action="{{ route('admin.food-beverages.store') }}" method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
                        @csrf

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required minlength="3" maxlength="255">
                                    <div class="invalid-feedback">
                                        @error('name')
                                            {{ $message }}
                                        @else
                                            Please enter a valid name (3-255 characters)
                                        @enderror
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="category" class="form-label">Category <span class="text-danger">*</span></label>
                                    <select class="form-select @error('category') is-invalid @enderror" id="category" name="category" required>
                                        <option value="" disabled {{ old('category') ? '' : 'selected' }}>Select a category</option>
                                        <option value="food" {{ old('category') == 'food' ? 'selected' : '' }}>Food</option>
                                        <option value="beverage" {{ old('category') == 'beverage' ? 'selected' : '' }}>Beverage</option>
                                        <option value="snack" {{ old('category') == 'snack' ? 'selected' : '' }}>Snack</option>
                                        <option value="other" {{ old('category') == 'other' ? 'selected' : '' }}>Other</option>
                                    </select>
                                    <div class="invalid-feedback">
                                        @error('category')
                                            {{ $message }}
                                        @else
                                            Please select a category
                                        @enderror
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="price" class="form-label">Price (Rp) <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text">Rp</span>
                                        <input type="number" class="form-control @error('price') is-invalid @enderror" id="price" name="price" value="{{ old('price') }}" min="1000" step="1000" required>
                                        <div class="invalid-feedback">
                                            @error('price')
                                                {{ $message }}
                                            @else
                                                Please enter a valid price (minimum Rp 1,000)
                                            @enderror
                                        </div>
                                    </div>
                                    <small class="form-text text-muted">Enter price in Rupiah (without decimal)</small>
                                </div>

                                <div class="mb-3">
                                    <label for="description" class="form-label">Description</label>
                                    <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="4" maxlength="1000">{{ old('description') }}</textarea>
                                    <div class="invalid-feedback">
                                        @error('description')
                                            {{ $message }}
                                        @else
                                            Description cannot exceed 1000 characters
                                        @enderror
                                    </div>
                                    <div class="form-text">
                                        <span id="descriptionCharCount">0</span>/1000 characters
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Thumbnail Image</label>
                                    <div class="input-group">
                                        <input type="file" class="form-control @error('thumbnail') is-invalid @enderror" id="thumbnail" name="thumbnail" accept="image/jpeg,image/png,image/jpg,image/gif" data-max-size="2048">
                                        <label class="input-group-text" for="thumbnail">
                                            <i class="iconoir-camera"></i>
                                        </label>
                                    </div>
                                    <div class="form-text">Recommended size: 500x500px. Max file size: 2MB</div>
                                    @error('thumbnail')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                    @else
                                        <div class="invalid-feedback" id="thumbnailFeedback">Please select a valid image file (max 2MB)</div>
                                    @enderror
                                    <div id="thumbnailPreview" class="mt-2 d-none">
                                        <div class="position-relative">
                                            <img src="" alt="Thumbnail Preview" class="img-thumbnail" style="max-height: 200px;">
                                            <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 rounded-circle" id="removeThumbnail" style="margin-top: -10px; margin-right: -10px;">
                                                <i class="iconoir-cancel"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Additional Images</label>
                                    <div class="input-group">
                                        <input type="file" class="form-control @error('images.*') is-invalid @enderror" id="images" name="images[]" accept="image/jpeg,image/png,image/jpg,image/gif" multiple data-max-size="2048" data-max-files="5">
                                        <label class="input-group-text" for="images">
                                            <i class="iconoir-album"></i>
                                        </label>
                                    </div>
                                    <div class="form-text">You can select up to 5 images. Max file size: 2MB each</div>
                                    @error('images.*')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                    @else
                                        <div class="invalid-feedback" id="imagesFeedback">Please select valid image files (max 2MB each)</div>
                                    @enderror
                                    <div id="imagesPreview" class="mt-2 row g-2"></div>
                                </div>

                                <div class="alert alert-info mb-3">
                                    <i class="iconoir-info-circle me-1"></i> Status Options
                                    <hr class="mt-2 mb-2">
                                    <div class="mb-2">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="is_available" name="is_available" value="1" {{ old('is_available', true) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="is_available">Available for customers</label>
                                        </div>
                                        <small class="text-muted">When unchecked, this item will not be visible to customers</small>
                                    </div>

                                    <div class="mb-0">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="is_featured" name="is_featured" value="1" {{ old('is_featured', false) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="is_featured">Featured item (shown in highlights)</label>
                                        </div>
                                        <small class="text-muted">Featured items appear in the carousel on the customer menu page</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-12 text-end">
                                <a href="{{ route('admin.food-beverages.index') }}" class="btn btn-secondary me-2">
                                    <i class="iconoir-cancel me-1"></i> Cancel
                                </a>
                                <button type="submit" class="btn btn-primary" id="submitBtn">
                                    <i class="iconoir-save-floppy me-1"></i> Save Item
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

@push('styles')
<style>
    /* Custom styling for validation */
    .was-validated .form-control:valid, .form-control.is-valid {
        border-color: #198754;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 8 8'%3e%3cpath fill='%23198754' d='M2.3 6.73L.6 4.53c-.4-1.04.46-1.4 1.1-.8l1.1 1.4 3.4-3.8c.6-.63 1.6-.27 1.2.7l-4 4.6c-.43.5-.8.4-1.1.1z'/%3e%3c/svg%3e");
        background-repeat: no-repeat;
        background-position: right calc(0.375em + 0.1875rem) center;
        background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
        padding-right: calc(1.5em + 0.75rem);
    }

    .was-validated .form-select:valid, .form-select.is-valid {
        border-color: #198754;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 8 8'%3e%3cpath fill='%23198754' d='M2.3 6.73L.6 4.53c-.4-1.04.46-1.4 1.1-.8l1.1 1.4 3.4-3.8c.6-.63 1.6-.27 1.2.7l-4 4.6c-.43.5-.8.4-1.1.1z'/%3e%3c/svg%3e"), url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23343a40' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3e%3c/svg%3e");
        background-position: right 0.75rem center, center right 2.25rem;
        background-size: 16px 12px, 16px 12px;
    }

    /* Image preview improvements */
    #imagesPreview .img-preview-wrapper {
        position: relative;
        margin-bottom: 10px;
    }

    #imagesPreview .remove-btn {
        position: absolute;
        top: -8px;
        right: -8px;
        background: #dc3545;
        color: white;
        border-radius: 50%;
        width: 20px;
        height: 20px;
        font-size: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        box-shadow: 0 2px 5px rgba(0,0,0,0.2);
    }

    /* Required field indicator */
    .form-label .text-danger {
        font-weight: bold;
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('createFoodBeverageForm');
        const submitBtn = document.getElementById('submitBtn');
        const nameInput = document.getElementById('name');
        const categorySelect = document.getElementById('category');
        const priceInput = document.getElementById('price');
        const descriptionTextarea = document.getElementById('description');
        const descriptionCharCount = document.getElementById('descriptionCharCount');
        const thumbnailInput = document.getElementById('thumbnail');
        const thumbnailPreview = document.getElementById('thumbnailPreview');
        const thumbnailFeedback = document.getElementById('thumbnailFeedback');
        const removeThumbnailBtn = document.getElementById('removeThumbnail');
        const imagesInput = document.getElementById('images');
        const imagesPreview = document.getElementById('imagesPreview');
        const imagesFeedback = document.getElementById('imagesFeedback');

        // Character counter for description
        descriptionTextarea.addEventListener('input', function() {
            const count = this.value.length;
            descriptionCharCount.textContent = count;

            if (count > 1000) {
                this.value = this.value.substring(0, 1000);
                descriptionCharCount.textContent = 1000;
            }
        });

        // Initialize character count
        descriptionCharCount.textContent = descriptionTextarea.value.length;

        // Thumbnail preview
        thumbnailInput.addEventListener('change', function() {
            validateImageFile(this, thumbnailFeedback);

            if (this.files && this.files[0] && this.validity.valid) {
                const reader = new FileReader();

                reader.onload = function(e) {
                    thumbnailPreview.classList.remove('d-none');
                    thumbnailPreview.querySelector('img').src = e.target.result;
                }

                reader.readAsDataURL(this.files[0]);
            } else if (!this.files || !this.files[0]) {
                thumbnailPreview.classList.add('d-none');
            }
        });

        // Remove thumbnail
        removeThumbnailBtn.addEventListener('click', function() {
            thumbnailInput.value = '';
            thumbnailPreview.classList.add('d-none');
            thumbnailInput.classList.remove('is-invalid', 'is-valid');
        });

        // Multiple images preview
        imagesInput.addEventListener('change', function() {
            validateMultipleImageFiles(this, imagesFeedback);
            imagesPreview.innerHTML = '';

            if (this.files && this.validity.valid) {
                Array.from(this.files).forEach((file, index) => {
                    const reader = new FileReader();

                    reader.onload = function(e) {
                        const wrapper = document.createElement('div');
                        wrapper.className = 'col-4 col-md-3';

                        const innerWrapper = document.createElement('div');
                        innerWrapper.className = 'img-preview-wrapper';

                        const img = document.createElement('img');
                        img.src = e.target.result;
                        img.className = 'img-thumbnail w-100';
                        img.style.height = '120px';
                        img.style.objectFit = 'cover';

                        innerWrapper.appendChild(img);
                        wrapper.appendChild(innerWrapper);
                        imagesPreview.appendChild(wrapper);
                    }

                    reader.readAsDataURL(file);
                });
            }
        });

        // Form validation
        form.addEventListener('submit', function(event) {
            if (!validateForm()) {
                event.preventDefault();
                event.stopPropagation();
            }

            form.classList.add('was-validated');
        });

        // Validate individual form fields
        function validateForm() {
            let isValid = true;

            // Validate name
            if (!nameInput.value || nameInput.value.length < 3 || nameInput.value.length > 255) {
                nameInput.setCustomValidity('Please enter a valid name (3-255 characters)');
                isValid = false;
            } else {
                nameInput.setCustomValidity('');
            }

            // Validate category
            if (!categorySelect.value) {
                categorySelect.setCustomValidity('Please select a category');
                isValid = false;
            } else {
                categorySelect.setCustomValidity('');
            }

            // Validate price
            if (!priceInput.value || priceInput.value < 1000) {
                priceInput.setCustomValidity('Please enter a valid price (minimum Rp 1,000)');
                isValid = false;
            } else {
                priceInput.setCustomValidity('');
            }

            // Validate thumbnail if selected
            if (thumbnailInput.files.length > 0) {
                isValid = validateImageFile(thumbnailInput, thumbnailFeedback) && isValid;
            }

            // Validate additional images if selected
            if (imagesInput.files.length > 0) {
                isValid = validateMultipleImageFiles(imagesInput, imagesFeedback) && isValid;
            }

            return isValid;
        }

        // Validate a single image file
        function validateImageFile(input, feedbackElement) {
            const maxSizeInBytes = parseInt(input.dataset.maxSize) * 1024; // Convert KB to bytes

            if (input.files.length > 0) {
                const file = input.files[0];

                // Check file type
                const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
                if (!validTypes.includes(file.type)) {
                    input.setCustomValidity('Invalid file type. Please select a JPEG, JPG, PNG, or GIF image.');
                    feedbackElement.textContent = 'Invalid file type. Please select a JPEG, JPG, PNG, or GIF image.';
                    input.classList.add('is-invalid');
                    return false;
                }

                // Check file size
                if (file.size > maxSizeInBytes) {
                    input.setCustomValidity('File size exceeds the maximum limit of 2MB.');
                    feedbackElement.textContent = 'File size exceeds the maximum limit of 2MB.';
                    input.classList.add('is-invalid');
                    return false;
                }

                input.setCustomValidity('');
                input.classList.remove('is-invalid');
                input.classList.add('is-valid');
                return true;
            }

            input.setCustomValidity('');
            input.classList.remove('is-invalid', 'is-valid');
            return true;
        }

        // Validate multiple image files
        function validateMultipleImageFiles(input, feedbackElement) {
            const maxSizeInBytes = parseInt(input.dataset.maxSize) * 1024; // Convert KB to bytes
            const maxFiles = parseInt(input.dataset.maxFiles);

            if (input.files.length > 0) {
                // Check number of files
                if (input.files.length > maxFiles) {
                    input.setCustomValidity(`You can only upload a maximum of ${maxFiles} images.`);
                    feedbackElement.textContent = `You can only upload a maximum of ${maxFiles} images.`;
                    input.classList.add('is-invalid');
                    return false;
                }

                // Check each file
                for (let i = 0; i < input.files.length; i++) {
                    const file = input.files[i];

                    // Check file type
                    const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
                    if (!validTypes.includes(file.type)) {
                        input.setCustomValidity(`File "${file.name}" has an invalid file type. Please select JPEG, JPG, PNG, or GIF images.`);
                        feedbackElement.textContent = `File "${file.name}" has an invalid file type. Please select JPEG, JPG, PNG, or GIF images.`;
                        input.classList.add('is-invalid');
                        return false;
                    }

                    // Check file size
                    if (file.size > maxSizeInBytes) {
                        input.setCustomValidity(`File "${file.name}" exceeds the maximum size limit of 2MB.`);
                        feedbackElement.textContent = `File "${file.name}" exceeds the maximum size limit of 2MB.`;
                        input.classList.add('is-invalid');
                        return false;
                    }
                }

                input.setCustomValidity('');
                input.classList.remove('is-invalid');
                input.classList.add('is-valid');
                return true;
            }

            input.setCustomValidity('');
            input.classList.remove('is-invalid', 'is-valid');
            return true;
        }
    });
</script>
@endpush
