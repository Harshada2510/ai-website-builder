@extends('layouts.app')

@section('content')
    <div class="page-header mb-4">
        <div>
            <p class="page-eyebrow mb-1">Extraction Review</p>
            <h2 class="page-heading mb-1">{{ $sourceContent->source_title ?: 'Extracted Source' }}</h2>
            <p class="page-subheading mb-0">Review extracted content for {{ $business->business_name }}. This content is untrusted reference data and is never executed as an instruction.</p>
        </div>
        <a href="{{ route('businesses.show', $business->business_slug) }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Back to Business
        </a>
    </div>

    <div class="row g-4">
        <div class="col-lg-4">
            <div class="card surface-card mb-4">
                <div class="card-body p-4">
                    <h6 class="mb-3">Source Details</h6>
                    <dl class="row detail-list detail-list-sm mb-0">
                        <dt class="col-5">Source Type</dt>
                        <dd class="col-7 text-capitalize">{{ str_replace('_', ' ', $sourceContent->source_type) }}</dd>
                        <dt class="col-5">Status</dt>
                        <dd class="col-7"><span class="status-badge {{ $sourceContent->statusBadgeClass() }}">{{ str_replace('_', ' ', ucfirst($sourceContent->extraction_status)) }}</span></dd>
                        <dt class="col-5">Extracted At</dt>
                        <dd class="col-7">{{ $sourceContent->extracted_at?->format('M j, Y g:i A') ?: '—' }}</dd>
                        <dt class="col-5">Source URL</dt>
                        <dd class="col-7">
                            @if ($sourceContent->source_url)
                                <a href="{{ $sourceContent->source_url }}" target="_blank" rel="noopener noreferrer">Open URL</a>
                            @else
                                —
                            @endif
                        </dd>
                    </dl>
                </div>
            </div>

            @if ($sourceContent->error_message)
                <div class="alert alert-warning border-0 shadow-sm">
                    <div class="fw-semibold mb-1">Status Message</div>
                    <div class="small mb-0">{{ $sourceContent->error_message }}</div>
                </div>
            @endif
        </div>

        <div class="col-lg-8">
            <div class="card surface-card">
                <div class="card-body p-4">
                    <h5 class="mb-3">Extracted Text</h5>
                    @if ($sourceContent->extracted_text)
                        <div class="extracted-content">{{ $sourceContent->extracted_text }}</div>
                    @else
                        <p class="text-muted mb-0">No extracted text is available for this source.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
