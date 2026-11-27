@extends('layouts.app')

@section('content')
    <div class="page-header mb-4">
        <div>
            <p class="page-eyebrow mb-1">AI Tools</p>
            <h2 class="page-heading mb-1">AI Website Generator</h2>
            <p class="page-subheading mb-0">Select a business and generate a professional website draft using verified saved data and extracted content.</p>
        </div>
    </div>

    @if (! $aiConfigured)
        <div class="alert alert-warning border-0 shadow-sm mb-4">
            <div class="fw-semibold mb-1"><i class="bi bi-exclamation-triangle me-1"></i>AI API key not configured</div>
            <div class="small">Add <code>AI_API_KEY</code>, <code>AI_MODEL</code>, and <code>AI_BASE_URL</code> to your <code>.env</code> file before generating a website. You can still review businesses and source readiness below.</div>
        </div>
    @endif

    <div class="card surface-card">
        <div class="card-body p-4">
            <div class="d-flex align-items-center justify-content-between gap-3 mb-4 flex-wrap">
                <div>
                    <h5 class="mb-1">Select a Business</h5>
                    <p class="text-muted small mb-0">Only the selected business's verified data and usable extracted sources will be used for generation.</p>
                </div>
                <a href="{{ route('create-website') }}" class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-plus-lg me-1"></i>Add Business
                </a>
            </div>

            @if ($businesses->isEmpty())
                <div class="text-center py-5">
                    <div class="text-muted mb-3"><i class="bi bi-building fs-1"></i></div>
                    <h5 class="mb-2">No businesses found</h5>
                    <p class="text-muted mb-3">Create a business first, then return here to generate its website.</p>
                    <a href="{{ route('create-website') }}" class="btn btn-primary">Create Website</a>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table dashboard-table mb-0 align-middle">
                        <thead>
                            <tr>
                                <th>Business</th>
                                <th>Category / City</th>
                                <th>Generations</th>
                                <th>Latest Status</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($businesses as $business)
                                @php $latest = $business->generatedWebsites->first(); @endphp
                                <tr>
                                    <td>
                                        <div class="fw-semibold text-dark">{{ $business->business_name }}</div>
                                        <div class="small text-muted">{{ $business->email ?: 'No email' }}{{ $business->phone ? ' · '.$business->phone : '' }}</div>
                                    </td>
                                    <td>
                                        <div>{{ $business->business_category ?: '—' }}</div>
                                        <div class="small text-muted">{{ $business->city }}</div>
                                    </td>
                                    <td>{{ $business->generated_websites_count }}</td>
                                    <td>
                                        @if ($latest)
                                            <span class="status-badge {{ $latest->statusBadgeClass() }}">{{ ucfirst($latest->generation_status) }}</span>
                                            <div class="small text-muted mt-1">{{ $latest->created_at?->format('M j, Y g:i A') }}</div>
                                        @else
                                            <span class="text-muted small">Not generated</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <div class="d-inline-flex gap-2 flex-wrap justify-content-end">
                                            <a href="{{ route('businesses.generate', $business->business_slug) }}" class="btn btn-sm btn-primary">
                                                <i class="bi bi-magic me-1"></i>Generate Website
                                            </a>
                                            @if ($latest && $latest->isCompleted())
                                                <a href="{{ route('generated-websites.preview', $latest) }}" class="btn btn-sm btn-outline-secondary">Preview Latest</a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
@endsection