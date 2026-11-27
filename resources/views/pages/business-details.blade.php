@extends('layouts.app')

@section('content')
    <div class="page-header mb-4">
        <div>
            <p class="page-eyebrow mb-1">Business Details</p>
            <h2 class="page-heading mb-1">{{ $business->business_name }}</h2>
            <p class="page-subheading mb-0">Saved business information for this website project.</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('my-websites') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-2"></i>Back to My Websites
            </a>
            <a href="{{ route('businesses.generate', $business->business_slug) }}" class="btn btn-success">
                <i class="bi bi-magic me-2"></i>Generate Website
            </a>
            <a href="{{ route('create-website') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg me-2"></i>Create Another
            </a>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card surface-card mb-4">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between gap-3 mb-4 flex-wrap">
                        <div>
                            <h5 class="mb-1">Basic Business Information</h5>
                            <p class="text-muted small mb-0">Core details about the business.</p>
                        </div>
                        <span class="status-badge status-{{ $business->status }}">{{ ucfirst($business->status) }}</span>
                    </div>

                    <dl class="row detail-list mb-0">
                        <dt class="col-sm-4">Business Name</dt>
                        <dd class="col-sm-8">{{ $business->business_name }}</dd>

                        <dt class="col-sm-4">Business Slug</dt>
                        <dd class="col-sm-8"><code>{{ $business->business_slug }}</code></dd>

                        <dt class="col-sm-4">Category</dt>
                        <dd class="col-sm-8">{{ $business->business_category ?: 'Not provided' }}</dd>

                        <dt class="col-sm-4">City</dt>
                        <dd class="col-sm-8">{{ $business->city }}</dd>

                        <dt class="col-sm-4">Full Address</dt>
                        <dd class="col-sm-8">{{ $business->address ?: 'Not provided' }}</dd>
                    </dl>
                </div>
            </div>

            <div class="card surface-card mb-4">
                <div class="card-body p-4">
                    <h5 class="mb-3">Business Description</h5>
                    <p class="mb-0 text-secondary" style="white-space: pre-line;">{{ $business->description ?: 'No description provided.' }}</p>
                </div>
            </div>

            <div class="card surface-card mb-4">
                <div class="card-body p-4">
                    <h5 class="mb-3">Contact Information</h5>
                    <dl class="row detail-list mb-0">
                        <dt class="col-sm-4">Phone Number</dt>
                        <dd class="col-sm-8">{{ $business->phone ?: 'Not provided' }}</dd>

                        <dt class="col-sm-4">Email Address</dt>
                        <dd class="col-sm-8">
                            @if ($business->email)
                                <a href="mailto:{{ $business->email }}">{{ $business->email }}</a>
                            @else
                                Not provided
                            @endif
                        </dd>

                        <dt class="col-sm-4">Website URL</dt>
                        <dd class="col-sm-8">
                            @if ($business->website_url)
                                <a href="{{ $business->website_url }}" target="_blank" rel="noopener noreferrer">{{ $business->website_url }}</a>
                            @else
                                Not provided
                            @endif
                        </dd>
                    </dl>
                </div>
            </div>

            <div class="card surface-card mb-4">
                <div class="card-body p-4">
                    <h5 class="mb-3">Social Media Links</h5>
                    <dl class="row detail-list mb-0">
                        <dt class="col-sm-4">Instagram</dt>
                        <dd class="col-sm-8">
                            @if ($business->instagram_url)
                                <a href="{{ $business->instagram_url }}" target="_blank" rel="noopener noreferrer">{{ $business->instagram_url }}</a>
                            @else
                                Not provided
                            @endif
                        </dd>

                        <dt class="col-sm-4">Facebook</dt>
                        <dd class="col-sm-8">
                            @if ($business->facebook_url)
                                <a href="{{ $business->facebook_url }}" target="_blank" rel="noopener noreferrer">{{ $business->facebook_url }}</a>
                            @else
                                Not provided
                            @endif
                        </dd>

                        <dt class="col-sm-4">LinkedIn</dt>
                        <dd class="col-sm-8">
                            @if ($business->linkedin_url)
                                <a href="{{ $business->linkedin_url }}" target="_blank" rel="noopener noreferrer">{{ $business->linkedin_url }}</a>
                            @else
                                Not provided
                            @endif
                        </dd>

                        <dt class="col-sm-4">Google Business Profile</dt>
                        <dd class="col-sm-8">
                            @if ($business->google_business_url)
                                <a href="{{ $business->google_business_url }}" target="_blank" rel="noopener noreferrer">{{ $business->google_business_url }}</a>
                            @else
                                Not provided
                            @endif
                        </dd>
                    </dl>
                </div>
            </div>

            <div class="card surface-card">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between gap-3 mb-3 flex-wrap">
                        <div>
                            <h5 class="mb-1">Uploaded Files</h5>
                            <p class="text-muted small mb-0">Documents and images for this business.</p>
                        </div>
                        <a href="{{ route('files.index', ['business_id' => $business->id]) }}" class="btn btn-sm btn-outline-primary">Manage Files</a>
                    </div>

                    @php
                        $documents = $business->files->where('file_type', 'document');
                        $images = $business->files->where('file_type', 'image');
                    @endphp

                    @if ($business->files->isEmpty())
                        <div class="text-center py-4">
                            <div class="text-muted mb-2"><i class="bi bi-folder2-open fs-3"></i></div>
                            <p class="text-muted mb-0">No files uploaded for this business yet.</p>
                        </div>
                    @else
                        <div class="mb-4">
                            <h6 class="small text-uppercase text-muted fw-semibold mb-2">Documents</h6>
                            @forelse ($documents as $file)
                                <div class="file-list-item">
                                    <div>
                                        <div class="fw-medium text-dark">{{ $file->original_name }}</div>
                                        <div class="small text-muted">{{ ucfirst($file->file_type) }} · {{ $file->formattedSize() }}</div>
                                    </div>
                                    <div class="d-flex gap-2">
                                        @if ($file->mime_type === 'application/pdf')
                                            <form action="{{ route('files.extract', $file) }}" method="POST" class="m-0">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-secondary">Extract</button>
                                            </form>
                                        @endif
                                        <a href="{{ route('files.download', $file) }}" class="btn btn-sm btn-outline-primary">Download</a>
                                    </div>
                                </div>
                            @empty
                                <p class="text-muted small mb-0">No documents uploaded.</p>
                            @endforelse
                        </div>

                        <div>
                            <h6 class="small text-uppercase text-muted fw-semibold mb-2">Images</h6>
                            @forelse ($images as $file)
                                <div class="file-list-item">
                                    <div>
                                        <div class="fw-medium text-dark">{{ $file->original_name }}</div>
                                        <div class="small text-muted">{{ ucfirst($file->file_type) }} · {{ $file->formattedSize() }}</div>
                                    </div>
                                    <a href="{{ route('files.download', $file) }}" class="btn btn-sm btn-outline-primary">Download</a>
                                </div>
                            @empty
                                <p class="text-muted small mb-0">No images uploaded.</p>
                            @endforelse
                        </div>
                    @endif
                </div>
            </div>
            <div class="card surface-card">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between gap-3 mb-3 flex-wrap">
                        <div>
                            <h5 class="mb-1">Extracted Business Information</h5>
                            <p class="text-muted small mb-0">Review content extracted from PDFs, website, and saved social links. Extracted data is untrusted reference content and is never executed.</p>
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <div class="border rounded-3 p-3 h-100 bg-light-subtle">
                                <div class="fw-semibold mb-1">Website Extraction</div>
                                <div class="small text-muted mb-3">Extract readable public content from the saved business website URL.</div>
                                <form action="{{ route('businesses.extract-website', $business->business_slug) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-globe2 me-1"></i>Extract Website Information
                                    </button>
                                </form>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="border rounded-3 p-3 h-100 bg-light-subtle">
                                <div class="fw-semibold mb-1">Social URL Review</div>
                                <div class="small text-muted mb-2">Social platforms may block automated access. URLs are marked for manual review.</div>
                                <div class="d-flex flex-wrap gap-2">
                                    @foreach (['instagram' => 'Instagram', 'facebook' => 'Facebook', 'linkedin' => 'LinkedIn', 'google_business' => 'Google Business'] as $type => $label)
                                        <form action="{{ route('businesses.extract-social', [$business->business_slug, $type]) }}" method="POST" class="m-0">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-secondary">{{ $label }}</button>
                                        </form>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>

                    @php $analysis = $extractionAnalysis ?? ['missing' => [], 'conflicts' => []]; @endphp

                    @if (!empty($analysis['missing']) || !empty($analysis['conflicts']))
                        <div class="alert alert-warning border-0 mb-4">
                            <div class="fw-semibold mb-2"><i class="bi bi-exclamation-triangle me-1"></i>Review Needed</div>
                            @if (!empty($analysis['missing']))
                                <div class="small mb-2"><strong>Possibly missing information:</strong> {{ implode(', ', $analysis['missing']) }}. Missing values are not treated as proof that the information does not exist.</div>
                            @endif
                            @if (!empty($analysis['conflicts']))
                                <div class="small fw-semibold mb-1">Possible conflicts (original business details were not changed):</div>
                                <ul class="small mb-0 ps-3">
                                    @foreach ($analysis['conflicts'] as $conflict)
                                        <li>
                                            <strong>{{ $conflict['field'] }}:</strong> form has "{{ $conflict['business_value'] }}", {{ $conflict['source'] }} found "{{ $conflict['extracted_value'] }}".
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                    @endif

                    @if ($business->sourceContents->isEmpty())
                        <div class="text-center py-4">
                            <div class="text-muted mb-2"><i class="bi bi-file-earmark-text fs-3"></i></div>
                            <p class="text-muted mb-0">No extracted content yet. Extract a PDF, website, or social source to review information here.</p>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table dashboard-table mb-0 align-middle">
                                <thead>
                                    <tr>
                                        <th>Source</th>
                                        <th>Status</th>
                                        <th>Extracted</th>
                                        <th>Preview</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($business->sourceContents as $source)
                                        <tr>
                                            <td>
                                                <div class="fw-semibold text-dark">{{ $source->source_title ?: ucfirst($source->source_type) }}</div>
                                                <div class="small text-muted text-capitalize">{{ str_replace('_', ' ', $source->source_type) }}</div>
                                                @if ($source->source_url)
                                                    <div class="small"><a href="{{ $source->source_url }}" target="_blank" rel="noopener noreferrer">Source URL</a></div>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="status-badge {{ $source->statusBadgeClass() }}">{{ str_replace('_', ' ', ucfirst($source->extraction_status)) }}</span>
                                                @if ($source->error_message)
                                                    <div class="small text-muted mt-1">{{ \Illuminate\Support\Str::limit($source->error_message, 90) }}</div>
                                                @endif
                                            </td>
                                            <td>{{ $source->extracted_at?->format('M j, Y g:i A') ?: '—' }}</td>
                                            <td class="small text-muted">{{ \Illuminate\Support\Str::limit((string) $source->extracted_text, 120) ?: 'No preview available.' }}</td>
                                            <td class="text-end">
                                                <div class="d-inline-flex gap-2 flex-wrap justify-content-end">
                                                    <a href="{{ route('source-contents.show', $source) }}" class="btn btn-sm btn-outline-primary">View</a>
                                                    @if ($source->source_type === 'pdf' && $source->business_file_id)
                                                        <form action="{{ route('files.extract', $source->business_file_id) }}" method="POST" class="m-0">
                                                            @csrf
                                                            <button type="submit" class="btn btn-sm btn-outline-secondary">Retry</button>
                                                        </form>
                                                    @elseif ($source->source_type === 'website')
                                                        <form action="{{ route('businesses.extract-website', $business->business_slug) }}" method="POST" class="m-0">
                                                            @csrf
                                                            <button type="submit" class="btn btn-sm btn-outline-secondary">Retry</button>
                                                        </form>
                                                    @elseif (in_array($source->source_type, ['instagram','facebook','linkedin','google_business'], true))
                                                        <form action="{{ route('businesses.extract-social', [$business->business_slug, $source->source_type]) }}" method="POST" class="m-0">
                                                            @csrf
                                                            <button type="submit" class="btn btn-sm btn-outline-secondary">Retry</button>
                                                        </form>
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
        </div>

        <div class="col-lg-4">
            <div class="card surface-card mb-4">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h6 class="mb-0">AI Website Generation</h6>
                        <a href="{{ route('businesses.generate', $business->business_slug) }}" class="btn btn-sm btn-primary">Generate</a>
                    </div>
                    @php $latestGeneration = $business->generatedWebsites->first(); @endphp
                    @if ($latestGeneration)
                        <div class="small text-muted mb-2">Latest generation: {{ $latestGeneration->created_at?->format('M j, Y g:i A') }}</div>
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <span class="status-badge {{ $latestGeneration->statusBadgeClass() }}">{{ ucfirst($latestGeneration->generation_status) }}</span>
                            @if ($latestGeneration->isCompleted())
                                <a href="{{ route('generated-websites.preview', $latestGeneration) }}" class="btn btn-sm btn-outline-primary">Preview</a>
                            @endif
                        </div>
                        @if ($business->generatedWebsites->count() > 1)
                            <div class="small text-muted">Total generations: {{ $business->generatedWebsites->count() }}</div>
                        @endif
                    @else
                        <p class="text-muted small mb-0">No website has been generated yet. Use the Generate button to create an AI-powered draft from verified business data.</p>
                    @endif
                </div>
            </div>

            <div class="card surface-card mb-4">
                <div class="card-body p-4">
                    <h6 class="mb-3">Record Information</h6>
                    <dl class="row detail-list detail-list-sm mb-0">
                        <dt class="col-5">Status</dt>
                        <dd class="col-7"><span class="status-badge status-{{ $business->status }}">{{ ucfirst($business->status) }}</span></dd>
                        <dt class="col-5">Created</dt>
                        <dd class="col-7">{{ $business->created_at?->format('M j, Y, g:i A') }}</dd>
                        <dt class="col-5">Updated</dt>
                        <dd class="col-7">{{ $business->updated_at?->format('M j, Y, g:i A') }}</dd>
                    </dl>
                </div>
            </div>

            <div class="card surface-card info-card">
                <div class="card-body p-4">
                    <div class="info-card-icon mb-3"><i class="bi bi-info-circle"></i></div>
                    <h6 class="mb-2">Next steps</h6>
                    <p class="text-muted small mb-0">Review extracted information, generate an AI website draft, and preview it. Public publishing and advanced editing will be added in future phases.</p>
                </div>
            </div>
        </div>
    </div>
@endsection
