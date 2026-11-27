@extends('layouts.app')

@section('content')
    <div class="page-header mb-4">
        <div>
            <p class="page-eyebrow mb-1">Editor</p>
            <h2 class="page-heading mb-1">Website Editor</h2>
            <p class="page-subheading mb-0">Select a completed generated website to customize its content, theme, and sections.</p>
        </div>
        <a href="{{ route('ai-generator') }}" class="btn btn-primary">
            <i class="bi bi-magic me-2"></i>Go to AI Generator
        </a>
    </div>

    @if (session('success'))
        <div class="alert alert-success border-0 shadow-sm mb-4">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger border-0 shadow-sm mb-4">{{ session('error') }}</div>
    @endif

    <div class="card surface-card">
        <div class="card-body p-4">
            @if ($generatedWebsites->isEmpty())
                <div class="placeholder-panel">
                    <div class="placeholder-panel-icon"><i class="bi bi-pencil-square"></i></div>
                    <h5 class="mb-2">No completed websites to edit</h5>
                    <p class="text-muted mb-3">Generate a website first, then you can open it in the editor to customize text, theme, sections, and more.</p>
                    <a href="{{ route('ai-generator') }}" class="btn btn-primary">
                        <i class="bi bi-magic me-2"></i>Go to AI Generator
                    </a>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Business</th>
                                <th>Website Title</th>
                                <th>Generated</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($generatedWebsites as $generatedWebsite)
                                <tr>
                                    <td>
                                        <div class="fw-semibold">{{ $generatedWebsite->business?->business_name ?? 'Unknown business' }}</div>
                                        @if ($generatedWebsite->business?->city)
                                            <div class="small text-muted">{{ $generatedWebsite->business->city }}</div>
                                        @endif
                                    </td>
                                    <td>{{ $generatedWebsite->site_title ?: 'Untitled website' }}</td>
                                    <td>
                                        <span class="small text-muted">{{ $generatedWebsite->created_at?->format('M j, Y g:i A') }}</span>
                                    </td>
                                    <td class="text-end">
                                        <div class="d-inline-flex gap-2 flex-wrap justify-content-end">
                                            <a href="{{ route('generated-websites.preview', $generatedWebsite) }}" class="btn btn-sm btn-outline-secondary">
                                                <i class="bi bi-eye me-1"></i>Preview
                                            </a>
                                            <a href="{{ route('generated-websites.edit', $generatedWebsite) }}" class="btn btn-sm btn-primary">
                                                <i class="bi bi-pencil-square me-1"></i>Edit Website
                                            </a>
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
