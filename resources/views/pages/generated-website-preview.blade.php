@extends('layouts.app')

@section('content')
    @php
        $sections = collect($content['sections'] ?? [])->filter(fn ($s) => is_array($s));
        $theme = is_array($content['theme'] ?? null) ? $content['theme'] : [];
        $primary = preg_match('/^#[0-9A-Fa-f]{6}$/', $theme['primary_color'] ?? '') ? $theme['primary_color'] : '#4f46e5';
        $secondary = preg_match('/^#[0-9A-Fa-f]{6}$/', $theme['secondary_color'] ?? '') ? $theme['secondary_color'] : '#0ea5e9';
        $bgColor = preg_match('/^#[0-9A-Fa-f]{6}$/', $theme['background_color'] ?? '') ? $theme['background_color'] : '#ffffff';
        $textColor = preg_match('/^#[0-9A-Fa-f]{6}$/', $theme['text_color'] ?? '') ? $theme['text_color'] : '#1f2937';
        $missing = is_array($content['missing_information'] ?? null) ? $content['missing_information'] : [];
        $conflicts = is_array($content['conflicting_information'] ?? null) ? $content['conflicting_information'] : [];
    @endphp

    <div class="page-header mb-4">
        <div>
            <p class="page-eyebrow mb-1">Generated Website Preview</p>
            <h2 class="page-heading mb-1">{{ $generatedWebsite->site_title ?: ($business->business_name ?? 'Generated Website') }}</h2>
            <p class="page-subheading mb-0">Preview of the saved AI-generated website draft for {{ $business->business_name ?? 'this business' }}. Review all content before using it publicly.</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-2"></i>Back to Dashboard
            </a>
            @if ($generatedWebsite->isCompleted())
                <a href="{{ route('generated-websites.edit', $generatedWebsite) }}" class="btn btn-primary">
                    <i class="bi bi-pencil-square me-2"></i>Edit Website
                </a>
            @endif
            @if ($business)
                <a href="{{ route('businesses.generate', $business->business_slug) }}" class="btn btn-outline-primary">
                    <i class="bi bi-magic me-2"></i>Generate Again
                </a>
            @endif
        </div>
    </div>

    @if (!empty($missing) || !empty($conflicts))
        <div class="alert alert-warning border-0 shadow-sm mb-4">
            <div class="fw-semibold mb-2"><i class="bi bi-exclamation-triangle me-1"></i>Review before using</div>
            @if (!empty($missing))
                <div class="small mb-1"><strong>Missing information noted by AI:</strong> {{ implode(', ', array_map(fn($i) => is_string($i) ? $i : '', $missing)) }}</div>
            @endif
            @if (!empty($conflicts))
                <div class="small"><strong>Conflicting information noted by AI:</strong> {{ implode(', ', array_map(fn($i) => is_string($i) ? $i : '', $conflicts)) }}</div>
            @endif
        </div>
    @endif

    <div class="card surface-card overflow-hidden mb-4">
        @include('pages.partials.generated-site-sections', ['publicView' => false])
    </div>

    <div class="card surface-card">
        <div class="card-body p-4">
            <h6 class="mb-3">Generation Details</h6>
            <dl class="row detail-list detail-list-sm mb-0">
                <dt class="col-sm-3">Status</dt>
                <dd class="col-sm-9"><span class="status-badge {{ $generatedWebsite->statusBadgeClass() }}">{{ ucfirst($generatedWebsite->generation_status) }}</span></dd>
                <dt class="col-sm-3">AI Model</dt>
                <dd class="col-sm-9">{{ $generatedWebsite->ai_model ?: 'Not recorded' }}</dd>
                <dt class="col-sm-3">Generated At</dt>
                <dd class="col-sm-9">{{ $generatedWebsite->created_at?->format('M j, Y g:i A') }}</dd>
            </dl>
        </div>
    </div>

@endsection