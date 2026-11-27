@extends('layouts.app')

@section('content')
    <div class="page-header mb-4">
        <div>
            <p class="page-eyebrow mb-1">Business Profile</p>
            <h2 class="page-heading mb-1">Business Information</h2>
            <p class="page-subheading mb-0">View and edit the business details used across your websites, uploads, and AI generation.</p>
        </div>
        <a href="{{ route('create-website') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-2"></i>Add Business
        </a>
    </div>

    @if (session('success'))
        <div class="alert alert-success border-0 shadow-sm mb-4">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger border-0 shadow-sm mb-4">{{ session('error') }}</div>
    @endif

    @if ($businesses->isEmpty())
        <div class="card surface-card">
            <div class="card-body p-4">
                <div class="placeholder-panel">
                    <div class="placeholder-panel-icon"><i class="bi bi-building"></i></div>
                    <h5 class="mb-2">No businesses yet</h5>
                    <p class="text-muted mb-3">Add a business to manage its information here.</p>
                    <a href="{{ route('create-website') }}" class="btn btn-primary">Create Website</a>
                </div>
            </div>
        </div>
    @else
        <div class="accordion" id="businessInfoAccordion">
            @foreach ($businesses as $index => $business)
                @php
                    $bag = $errors->getBag('business_'.$business->id);
                    $isEditing = (string) session('edit_business_id') === (string) $business->id;
                    $open = $isEditing || $bag->any() || ($index === 0 && ! session('edit_business_id'));
                    $val = fn(string $field) => $isEditing ? old($field, $business->{$field}) : $business->{$field};
                @endphp
                <div class="card surface-card mb-3">
                    <div class="card-header bg-transparent border-0 p-0">
                        <button class="btn w-100 text-start p-4 d-flex justify-content-between align-items-center gap-3 {{ $open ? '' : 'collapsed' }}"
                                type="button" data-bs-toggle="collapse" data-bs-target="#business-{{ $business->id }}"
                                aria-expanded="{{ $open ? 'true' : 'false' }}">
                            <div>
                                <div class="fw-semibold fs-5 mb-1">{{ $business->business_name }}</div>
                                <div class="small text-muted">
                                    {{ $business->business_category ?: 'No category' }}
                                    @if ($business->city) · {{ $business->city }} @endif
                                </div>
                            </div>
                            <span class="text-muted"><i class="bi bi-chevron-down"></i></span>
                        </button>
                    </div>
                    <div id="business-{{ $business->id }}" class="collapse {{ $open ? 'show' : '' }}" data-bs-parent="#businessInfoAccordion">
                        <div class="card-body p-4 pt-0">
                            @if ($bag->any())
                                <div class="alert alert-danger border-0 shadow-sm mb-4">
                                    <div class="fw-semibold mb-1">Please fix the following errors:</div>
                                    <ul class="mb-0 ps-3">
                                        @foreach ($bag->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <form method="POST" action="{{ route('businesses.update', $business->business_slug) }}">
                                @csrf
                                @method('PUT')

                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Business Name <span class="text-danger">*</span></label>
                                        <input type="text" name="business_name" class="form-control @if($bag->has('business_name')) is-invalid @endif"
                                               value="{{ old('business_name', $business->business_name) }}" required maxlength="255">
                                        @if($bag->has('business_name')) <div class="invalid-feedback">{{ $bag->first('business_name') }}</div> @endif
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Category</label>
                                        <select name="business_category" class="form-select @if($bag->has('business_category')) is-invalid @endif">
                                            <option value="">Select category</option>
                                            @foreach ($businessCategories as $category)
                                                <option value="{{ $category }}" @selected(old('business_category', $business->business_category) === $category)>{{ $category }}</option>
                                            @endforeach
                                        </select>
                                        @if($bag->has('business_category')) <div class="invalid-feedback">{{ $bag->first('business_category') }}</div> @endif
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">Description</label>
                                        <textarea name="description" rows="4" class="form-control @if($bag->has('description')) is-invalid @endif" maxlength="3000">{{ old('description', $business->description) }}</textarea>
                                        @if($bag->has('description')) <div class="invalid-feedback">{{ $bag->first('description') }}</div> @endif
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Phone Number</label>
                                        <input type="text" name="phone" class="form-control @if($bag->has('phone')) is-invalid @endif"
                                               value="{{ old('phone', $business->phone) }}" maxlength="30">
                                        @if($bag->has('phone')) <div class="invalid-feedback">{{ $bag->first('phone') }}</div> @endif
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Email</label>
                                        <input type="email" name="email" class="form-control @if($bag->has('email')) is-invalid @endif"
                                               value="{{ old('email', $business->email) }}" maxlength="255">
                                        @if($bag->has('email')) <div class="invalid-feedback">{{ $bag->first('email') }}</div> @endif
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">City <span class="text-danger">*</span></label>
                                        <input type="text" name="city" class="form-control @if($bag->has('city')) is-invalid @endif"
                                               value="{{ old('city', $business->city) }}" required maxlength="150">
                                        @if($bag->has('city')) <div class="invalid-feedback">{{ $bag->first('city') }}</div> @endif
                                    </div>
                                    <div class="col-md-8">
                                        <label class="form-label">Address</label>
                                        <input type="text" name="address" class="form-control @if($bag->has('address')) is-invalid @endif"
                                               value="{{ old('address', $business->address) }}" maxlength="255">
                                        @if($bag->has('address')) <div class="invalid-feedback">{{ $bag->first('address') }}</div> @endif
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Website URL</label>
                                        <input type="url" name="website_url" class="form-control @if($bag->has('website_url')) is-invalid @endif"
                                               value="{{ old('website_url', $business->website_url) }}" maxlength="255" placeholder="https://">
                                        @if($bag->has('website_url')) <div class="invalid-feedback">{{ $bag->first('website_url') }}</div> @endif
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Instagram URL</label>
                                        <input type="url" name="instagram_url" class="form-control @if($bag->has('instagram_url')) is-invalid @endif"
                                               value="{{ old('instagram_url', $business->instagram_url) }}" maxlength="255" placeholder="https://">
                                        @if($bag->has('instagram_url')) <div class="invalid-feedback">{{ $bag->first('instagram_url') }}</div> @endif
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Facebook URL</label>
                                        <input type="url" name="facebook_url" class="form-control @if($bag->has('facebook_url')) is-invalid @endif"
                                               value="{{ old('facebook_url', $business->facebook_url) }}" maxlength="255" placeholder="https://">
                                        @if($bag->has('facebook_url')) <div class="invalid-feedback">{{ $bag->first('facebook_url') }}</div> @endif
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">LinkedIn URL</label>
                                        <input type="url" name="linkedin_url" class="form-control @if($bag->has('linkedin_url')) is-invalid @endif"
                                               value="{{ old('linkedin_url', $business->linkedin_url) }}" maxlength="255" placeholder="https://">
                                        @if($bag->has('linkedin_url')) <div class="invalid-feedback">{{ $bag->first('linkedin_url') }}</div> @endif
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Google Business URL</label>
                                        <input type="url" name="google_business_url" class="form-control @if($bag->has('google_business_url')) is-invalid @endif"
                                               value="{{ old('google_business_url', $business->google_business_url) }}" maxlength="255" placeholder="https://">
                                        @if($bag->has('google_business_url')) <div class="invalid-feedback">{{ $bag->first('google_business_url') }}</div> @endif
                                    </div>
                                </div>

                                <div class="d-flex gap-2 mt-4">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-save me-2"></i>Save Changes
                                    </button>
                                    <a href="{{ route('businesses.show', $business->business_slug) }}" class="btn btn-outline-secondary">View Details</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
@endsection
