@extends('layouts.app')

@section('content')
    <div class="page-header mb-4">
        <div>
            <p class="page-eyebrow mb-1">AI Website Generator</p>
            <h2 class="page-heading mb-1">Generate Website: {{ $business->business_name }}</h2>
            <p class="page-subheading mb-0">Review the available information before generating. The AI will use only verified data for this business and will not invent missing facts.</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('ai-generator') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-2"></i>Back to AI Generator
            </a>
            <a href="{{ route('businesses.show', $business->business_slug) }}" class="btn btn-outline-primary">Business Details</a>
        </div>
    </div>

    @if (! $aiConfigured)
        <div class="alert alert-warning border-0 shadow-sm mb-4">
            <div class="fw-semibold mb-1"><i class="bi bi-exclamation-triangle me-1"></i>AI API key not configured</div>
            <div class="small">Generation is unavailable until <code>AI_API_KEY</code> is set in <code>.env</code>. You can still review data readiness on this page.</div>
        </div>
    @endif

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card surface-card mb-4">
                <div class="card-body p-4">
                    <h5 class="mb-3">Selected Business</h5>
                    <dl class="row detail-list mb-0">
                        <dt class="col-sm-4">Business Name</dt>
                        <dd class="col-sm-8">{{ $business->business_name }}</dd>
                        <dt class="col-sm-4">Category</dt>
                        <dd class="col-sm-8">{{ $business->business_category ?: 'Not provided' }}</dd>
                        <dt class="col-sm-4">City</dt>
                        <dd class="col-sm-8">{{ $business->city }}</dd>
                        <dt class="col-sm-4">Phone / Email</dt>
                        <dd class="col-sm-8">{{ $business->phone ?: 'Phone not provided' }}{{ $business->email ? ' · '.$business->email : ' · Email not provided' }}</dd>
                        <dt class="col-sm-4">Address</dt>
                        <dd class="col-sm-8">{{ $business->address ?: 'Not provided' }}</dd>
                        <dt class="col-sm-4">Website URL</dt>
                        <dd class="col-sm-8">{{ $business->website_url ?: 'Not provided' }}</dd>
                    </dl>
                </div>
            </div>

            <div class="card surface-card mb-4">
                <div class="card-body p-4">
                    <h5 class="mb-3">Available Source Information</h5>
                    <p class="text-muted small">Only completed extracted sources with usable text are sent as factual reference content. Failed, empty, pending, or needs-review extractions are excluded from AI factual context.</p>

                    @if ($business->sourceContents->isEmpty())
                        <div class="alert alert-light border mb-0">No extracted sources found. The generator will use only saved business form details.</div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-sm align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Source</th>
                                        <th>Type</th>
                                        <th>Status</th>
                                        <th>Used for AI</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($business->sourceContents as $source)
                                        @php
                                            $usable = $source->extraction_status === 'completed' && trim((string) $source->extracted_text) !== '';
                                        @endphp
                                        <tr>
                                            <td>
                                                <div class="fw-medium">{{ $source->source_title ?: ucfirst(str_replace('_', ' ', $source->source_type)) }}</div>
                                                @if ($source->source_url)
                                                    <div class="small text-muted">{{ \Illuminate\Support\Str::limit($source->source_url, 60) }}</div>
                                                @endif
                                            </td>
                                            <td class="text-capitalize">{{ str_replace('_', ' ', $source->source_type) }}</td>
                                            <td><span class="status-badge {{ $source->statusBadgeClass() }}">{{ str_replace('_', ' ', ucfirst($source->extraction_status)) }}</span></td>
                                            <td>
                                                @if ($usable)
                                                    <span class="badge text-bg-success">Yes</span>
                                                @else
                                                    <span class="badge text-bg-secondary">No</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>

            <div class="card surface-card mb-4">
                <div class="card-body p-4">
                    <h5 class="mb-3">Missing / Conflicting Information Review</h5>
                    @php $missing = $analysis['missing'] ?? []; $conflicts = $analysis['conflicts'] ?? []; @endphp
                    @if (empty($missing) && empty($conflicts))
                        <div class="alert alert-success border-0 mb-0">No obvious missing fields or conflicts were detected by the basic review. Please still confirm important details before publishing.</div>
                    @else
                        @if (!empty($missing))
                            <div class="alert alert-warning border-0">
                                <div class="fw-semibold mb-1">Possibly missing information</div>
                                <div class="small">{{ implode(', ', $missing) }}. Missing information will not be invented; placeholders or hidden sections may be used.</div>
                            </div>
                        @endif
                        @if (!empty($conflicts))
                            <div class="alert alert-danger border-0 mb-0">
                                <div class="fw-semibold mb-1">Possible conflicts</div>
                                <div class="small mb-2">Conflicting values are not silently resolved. Review them before treating generated content as fact.</div>
                                <ul class="small mb-0 ps-3">
                                    @foreach ($conflicts as $conflict)
                                        <li><strong>{{ $conflict['field'] }}:</strong> form "{{ $conflict['business_value'] }}" vs {{ $conflict['source'] }} "{{ $conflict['extracted_value'] }}".</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card surface-card mb-4">
                <div class="card-body p-4">
                    <h5 class="mb-3">Generate Website</h5>
                    <p class="text-muted small">The AI will generate a structured website draft. It will use only this business's verified information and completed extracted sources.</p>
                    <ul class="small text-muted ps-3 mb-4">
                        <li>No invented addresses, prices, reviews, or claims.</li>
                        <li>Missing information uses placeholders or hidden sections.</li>
                        <li>Extracted content is treated as untrusted reference data.</li>
                    </ul>

                    <form action="{{ route('businesses.generate.store', $business->business_slug) }}" method="POST" id="generateWebsiteForm">
                        @csrf
                        <button type="submit" class="btn btn-primary w-100" id="generateWebsiteBtn" {{ $aiConfigured ? '' : 'disabled' }}>
                            <span class="btn-label"><i class="bi bi-magic me-2"></i>Generate Website</span>
                            <span class="btn-loading d-none"><span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Generating...</span>
                        </button>
                    </form>
                    @if (! $aiConfigured)
                        <div class="small text-muted mt-2">Configure <code>AI_API_KEY</code> in <code>.env</code> to enable generation.</div>
                    @endif
                </div>
            </div>

            <div class="card surface-card">
                <div class="card-body p-4">
                    <h6 class="mb-3">Previous Generations</h6>
                    @if ($business->generatedWebsites->isEmpty())
                        <p class="text-muted small mb-0">No previous generations for this business.</p>
                    @else
                        <div class="d-flex flex-column gap-3">
                            @foreach ($business->generatedWebsites->take(5) as $generation)
                                <div class="border rounded-3 p-3">
                                    <div class="d-flex align-items-center justify-content-between gap-2 mb-1">
                                        <div class="fw-medium small">{{ $generation->site_title ?: $business->business_name }}</div>
                                        <span class="status-badge {{ $generation->statusBadgeClass() }}">{{ ucfirst($generation->generation_status) }}</span>
                                    </div>
                                    <div class="small text-muted mb-2">{{ $generation->created_at?->format('M j, Y g:i A') }}{{ $generation->ai_model ? ' · '.$generation->ai_model : '' }}</div>
                                    @if ($generation->isCompleted())
                                        <a href="{{ route('generated-websites.preview', $generation) }}" class="btn btn-sm btn-outline-primary">Preview</a>
                                    @elseif ($generation->error_message)
                                        <div class="small text-danger">{{ \Illuminate\Support\Str::limit($generation->error_message, 120) }}</div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('generateWebsiteForm');
            const btn = document.getElementById('generateWebsiteBtn');
            if (!form || !btn) return;
            form.addEventListener('submit', function () {
                if (btn.disabled) return;
                btn.disabled = true;
                btn.querySelector('.btn-label').classList.add('d-none');
                btn.querySelector('.btn-loading').classList.remove('d-none');
            });
        });
    </script>
@endsection