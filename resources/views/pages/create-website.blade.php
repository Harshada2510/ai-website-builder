@extends('layouts.app')

@section('content')
    <div class="page-header mb-4">
        <div>
            <p class="page-eyebrow mb-1">Website Setup</p>
            <h2 class="page-heading mb-1">Create a New Website</h2>
            <p class="page-subheading mb-0">Enter your business information to get started. You can refine these details later.</p>
        </div>
    </div>

    @if (session('duplicate_warning'))
        <div class="alert alert-warning border-0 shadow-sm mb-4" role="alert">
            <div class="d-flex gap-2">
                <i class="bi bi-exclamation-triangle-fill mt-1"></i>
                <div>
                    <div class="fw-semibold mb-1">Possible duplicate business found</div>
                    <div>{{ session('duplicate_warning') }}</div>
                </div>
            </div>
        </div>
    @endif

    <form action="{{ route('businesses.store') }}" method="POST" novalidate>
        @csrf

        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card surface-card mb-4">
                    <div class="card-body p-4">
                        <div class="section-heading mb-4">
                            <h5 class="mb-1">Basic Business Information</h5>
                            <p class="text-muted small mb-0">Tell us the core details about your business.</p>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="business_name" class="form-label">Business Name <span class="text-danger">*</span></label>
                                <input
                                    type="text"
                                    class="form-control @error('business_name') is-invalid @enderror"
                                    id="business_name"
                                    name="business_name"
                                    value="{{ old('business_name') }}"
                                    placeholder="e.g., Sunrise Cafe"
                                    maxlength="255"
                                    required
                                >
                                @error('business_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="business_category" class="form-label">Business Category</label>
                                <select
                                    class="form-select @error('business_category') is-invalid @enderror"
                                    id="business_category"
                                    name="business_category"
                                >
                                    <option value="">Select a category</option>
                                    @foreach (($businessCategories ?? []) as $category)
                                        <option value="{{ $category }}" @selected(old('business_category') === $category)>
                                            {{ $category }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('business_category')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="city" class="form-label">City <span class="text-danger">*</span></label>
                                <input
                                    type="text"
                                    class="form-control @error('city') is-invalid @enderror"
                                    id="city"
                                    name="city"
                                    value="{{ old('city') }}"
                                    placeholder="e.g., Mumbai"
                                    maxlength="150"
                                    required
                                >
                                @error('city')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="address" class="form-label">Full Address</label>
                                <input
                                    type="text"
                                    class="form-control @error('address') is-invalid @enderror"
                                    id="address"
                                    name="address"
                                    value="{{ old('address') }}"
                                    placeholder="e.g., 123 Main Street, Andheri West"
                                    maxlength="255"
                                >
                                @error('address')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card surface-card mb-4">
                    <div class="card-body p-4">
                        <div class="section-heading mb-4">
                            <h5 class="mb-1">Business Description</h5>
                            <p class="text-muted small mb-0">Describe what your business does and what makes it special.</p>
                        </div>

                        <label for="description" class="form-label">Short Description</label>
                        <textarea
                            class="form-control @error('description') is-invalid @enderror"
                            id="description"
                            name="description"
                            rows="5"
                            maxlength="3000"
                            placeholder="Example: Sunrise Cafe is a cozy neighborhood cafe in Mumbai serving freshly brewed coffee, breakfast, and handmade desserts. We focus on quality ingredients and a warm customer experience."
                        >{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Maximum 3000 characters.</div>
                    </div>
                </div>

                <div class="card surface-card mb-4">
                    <div class="card-body p-4">
                        <div class="section-heading mb-4">
                            <h5 class="mb-1">Contact Information</h5>
                            <p class="text-muted small mb-0">Add ways customers can reach or find your business.</p>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="phone" class="form-label">Phone Number</label>
                                <input
                                    type="text"
                                    class="form-control @error('phone') is-invalid @enderror"
                                    id="phone"
                                    name="phone"
                                    value="{{ old('phone') }}"
                                    placeholder="e.g., +91 98765 43210"
                                    maxlength="30"
                                >
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="email" class="form-label">Email Address</label>
                                <input
                                    type="email"
                                    class="form-control @error('email') is-invalid @enderror"
                                    id="email"
                                    name="email"
                                    value="{{ old('email') }}"
                                    placeholder="e.g., hello@sunrisecafe.com"
                                    maxlength="255"
                                >
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label for="website_url" class="form-label">Business Website URL</label>
                                <input
                                    type="url"
                                    class="form-control @error('website_url') is-invalid @enderror"
                                    id="website_url"
                                    name="website_url"
                                    value="{{ old('website_url') }}"
                                    placeholder="https://www.example.com"
                                    maxlength="255"
                                >
                                @error('website_url')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card surface-card mb-4">
                    <div class="card-body p-4">
                        <div class="section-heading mb-4">
                            <h5 class="mb-1">Social Media Links</h5>
                            <p class="text-muted small mb-0">Optional links to your business profiles.</p>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="instagram_url" class="form-label">Instagram URL</label>
                                <input type="url" class="form-control @error('instagram_url') is-invalid @enderror" id="instagram_url" name="instagram_url" value="{{ old('instagram_url') }}" placeholder="https://instagram.com/yourbusiness" maxlength="255">
                                @error('instagram_url')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="facebook_url" class="form-label">Facebook URL</label>
                                <input type="url" class="form-control @error('facebook_url') is-invalid @enderror" id="facebook_url" name="facebook_url" value="{{ old('facebook_url') }}" placeholder="https://facebook.com/yourbusiness" maxlength="255">
                                @error('facebook_url')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="linkedin_url" class="form-label">LinkedIn URL</label>
                                <input type="url" class="form-control @error('linkedin_url') is-invalid @enderror" id="linkedin_url" name="linkedin_url" value="{{ old('linkedin_url') }}" placeholder="https://linkedin.com/company/yourbusiness" maxlength="255">
                                @error('linkedin_url')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="google_business_url" class="form-label">Google Business Profile URL</label>
                                <input type="url" class="form-control @error('google_business_url') is-invalid @enderror" id="google_business_url" name="google_business_url" value="{{ old('google_business_url') }}" placeholder="https://g.page/yourbusiness" maxlength="255">
                                @error('google_business_url')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                @if (session('duplicate_warning'))
                    <div class="card surface-card mb-4 border-warning-subtle">
                        <div class="card-body p-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="1" id="confirm_duplicate" name="confirm_duplicate">
                                <label class="form-check-label fw-medium" for="confirm_duplicate">
                                    I confirm this is a different business and want to save it as a new record.
                                </label>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="d-flex flex-wrap gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-2"></i>Save Business Information
                    </button>
                    <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card surface-card mb-4">
                    <div class="card-body p-4">
                        <h6 class="mb-3">Before you submit</h6>
                        <ul class="list-unstyled mb-0 placeholder-list">
                            <li><i class="bi bi-check-circle-fill text-primary"></i><span>Business name and city are required.</span></li>
                            <li><i class="bi bi-check-circle-fill text-primary"></i><span>Use full URLs, including https://.</span></li>
                            <li><i class="bi bi-check-circle-fill text-primary"></i><span>Your information is saved as a draft.</span></li>
                        </ul>
                    </div>
                </div>

                <div class="card surface-card info-card">
                    <div class="card-body p-4">
                        <div class="info-card-icon mb-3"><i class="bi bi-shield-check"></i></div>
                        <h6 class="mb-2">Your data is handled safely</h6>
                        <p class="text-muted small mb-0">Only business information is collected in this phase. Do not enter passwords, API keys, or other sensitive credentials.</p>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection
