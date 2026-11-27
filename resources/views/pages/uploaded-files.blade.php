@extends('layouts.app')

@section('content')
    <div class="page-header mb-4">
        <div>
            <p class="page-eyebrow mb-1">Assets</p>
            <h2 class="page-heading mb-1">Uploaded Files</h2>
            <p class="page-subheading mb-0">Upload and manage documents and images for your business websites.</p>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-4">
            <div class="card surface-card mb-4">
                <div class="card-body p-4">
                    <div class="section-heading mb-4">
                        <h5 class="mb-1">Upload Files</h5>
                        <p class="text-muted small mb-0">Select a business and upload one or more files.</p>
                    </div>

                    @if ($errors->any())
                        <div class="alert alert-danger border-0 mb-4">
                            <div class="fw-semibold mb-1">Please fix the following:</div>
                            <ul class="mb-0 ps-3">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('files.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="mb-3">
                            <label for="business_id" class="form-label">Business <span class="text-danger">*</span></label>
                            <select name="business_id" id="business_id" class="form-select @error('business_id') is-invalid @enderror" required>
                                <option value="">Select a business</option>
                                @foreach ($businesses as $business)
                                    <option value="{{ $business->id }}" @selected((string) old('business_id', $selectedBusinessId) === (string) $business->id)>
                                        {{ $business->business_name }} ({{ $business->city }})
                                    </option>
                                @endforeach
                            </select>
                            @error('business_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="files" class="form-label">Files <span class="text-danger">*</span></label>
                            <input
                                type="file"
                                name="files[]"
                                id="files"
                                class="form-control @error('files') is-invalid @enderror @error('files.*') is-invalid @enderror"
                                multiple
                                accept=".pdf,.jpg,.jpeg,.png,.webp,application/pdf,image/jpeg,image/png,image/webp"
                                required
                            >
                            @error('files')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                            @error('files.*')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="small text-muted mb-4">
                            <div><i class="bi bi-check2 me-1 text-primary"></i>Accepted: PDF, JPG, JPEG, PNG, WEBP</div>
                            <div><i class="bi bi-check2 me-1 text-primary"></i>Max size: 10 MB per file</div>
                            <div><i class="bi bi-check2 me-1 text-primary"></i>Up to 10 files per upload</div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-upload me-2"></i>Upload Files
                        </button>
                    </form>
                </div>
            </div>

            <div class="card surface-card info-card">
                <div class="card-body p-4">
                    <div class="info-card-icon mb-3"><i class="bi bi-shield-lock"></i></div>
                    <h6 class="mb-2">Private storage</h6>
                    <p class="text-muted small mb-0">Files are stored in private application storage and served through controlled download routes. Authentication/ownership checks are not implemented yet in this phase.</p>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card surface-card">
                <div class="card-header surface-card-header">
                    <div>
                        <h5 class="mb-1">Uploaded File Records</h5>
                        <p class="text-muted mb-0 small">Files linked to your businesses.</p>
                    </div>
                    <span class="badge rounded-pill text-bg-light border">{{ $files->total() }} total</span>
                </div>
                <div class="card-body border-bottom bg-light-subtle">
                    <form action="{{ route('files.index') }}" method="GET" class="row g-2 align-items-end">
                        <div class="col-sm-8 col-md-6">
                            <label for="filter_business_id" class="form-label small mb-1">Filter by business</label>
                            <select name="business_id" id="filter_business_id" class="form-select form-select-sm">
                                <option value="">All businesses</option>
                                @foreach ($businesses as $business)
                                    <option value="{{ $business->id }}" @selected((string) $selectedBusinessId === (string) $business->id)>
                                        {{ $business->business_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-sm-auto">
                            <button type="submit" class="btn btn-sm btn-outline-primary">Filter</button>
                            <a href="{{ route('files.index') }}" class="btn btn-sm btn-outline-secondary">Clear</a>
                        </div>
                    </form>
                </div>

                <div class="card-body p-0">
                    @if ($files->isEmpty())
                        <div class="empty-state py-5">
                            <div class="empty-state-icon"><i class="bi bi-file-earmark-arrow-up"></i></div>
                            <h6 class="empty-state-title">No files uploaded yet</h6>
                            <p class="empty-state-text">Upload documents or images for a business to see them here.</p>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table dashboard-table mb-0 align-middle">
                                <thead>
                                    <tr>
                                        <th>File</th>
                                        <th>Business</th>
                                        <th>Type</th>
                                        <th>Size</th>
                                        <th>Uploaded</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($files as $file)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center gap-2">
                                                    <span class="file-type-icon {{ $file->isImage() ? 'file-type-image' : 'file-type-doc' }}">
                                                        <i class="bi {{ $file->isImage() ? 'bi-image' : 'bi-file-earmark-pdf' }}"></i>
                                                    </span>
                                                    <div>
                                                        <div class="fw-semibold text-dark">{{ $file->original_name }}</div>
                                                        <div class="small text-muted">{{ strtoupper(pathinfo($file->original_name, PATHINFO_EXTENSION)) }}</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>{{ $file->business?->business_name ?? 'Unknown business' }}</td>
                                            <td>
                                                <span class="badge rounded-pill text-bg-light border">{{ ucfirst($file->file_type) }}</span>
                                            </td>
                                            <td>{{ $file->formattedSize() }}</td>
                                            <td>{{ $file->created_at?->format('M j, Y') }}</td>
                                            <td class="text-end">
                                                <div class="d-inline-flex gap-2 flex-wrap justify-content-end">
                                                    @if ($file->mime_type === 'application/pdf')
                                                        <form action="{{ route('files.extract', $file) }}" method="POST" class="m-0">
                                                            @csrf
                                                            <button type="submit" class="btn btn-sm btn-outline-secondary">
                                                                <i class="bi bi-file-earmark-text me-1"></i>Extract
                                                            </button>
                                                        </form>
                                                    @endif
                                                    <a href="{{ route('files.download', $file) }}" class="btn btn-sm btn-outline-primary">
                                                        <i class="bi bi-download me-1"></i>Download
                                                    </a>
                                                    <form action="{{ route('files.destroy', $file) }}" method="POST" class="m-0" onsubmit="return confirm('Delete this file? This action cannot be undone.');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                                            <i class="bi bi-trash me-1"></i>Delete
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        @if ($files->hasPages())
                            <div class="p-3 border-top">
                                {{ $files->links() }}
                            </div>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
