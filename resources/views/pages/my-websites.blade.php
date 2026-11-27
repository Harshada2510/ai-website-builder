@extends('layouts.app')

@section('content')
    <div class="page-header mb-4">
        <div>
            <p class="page-eyebrow mb-1">Website Management</p>
            <h2 class="page-heading mb-1">My Websites</h2>
            <p class="page-subheading mb-0">View and manage all the business websites you have created.</p>
        </div>
        <a href="{{ route('create-website') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-2"></i>Create Website
        </a>
    </div>

    <div class="card surface-card">
        <div class="card-header surface-card-header">
            <div>
                <h5 class="mb-1">Saved Businesses</h5>
                <p class="text-muted mb-0 small">Businesses you have saved are listed below.</p>
            </div>
            <span class="badge rounded-pill text-bg-light border">{{ $businesses->count() }} total</span>
        </div>
        <div class="card-body p-0">
            @if ($businesses->isEmpty())
                <div class="empty-state py-5">
                    <div class="empty-state-icon"><i class="bi bi-window-stack"></i></div>
                    <h6 class="empty-state-title">No websites to display</h6>
                    <p class="empty-state-text">You haven't saved any business information yet. Create your first website to get started.</p>
                    <a href="{{ route('create-website') }}" class="btn btn-primary">
                        <i class="bi bi-plus-lg me-2"></i>Create Your First Website
                    </a>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table dashboard-table mb-0 align-middle">
                        <thead>
                            <tr>
                                <th>Business Name</th>
                                <th>Category</th>
                                <th>City</th>
                                <th>Status</th>
                                <th>Date Created</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($businesses as $business)
                                <tr>
                                    <td>
                                        <div class="fw-semibold text-dark">{{ $business->business_name }}</div>
                                        <div class="small text-muted">{{ $business->business_slug }}</div>
                                    </td>
                                    <td>{{ $business->business_category ?: '—' }}</td>
                                    <td>{{ $business->city }}</td>
                                    <td>
                                        <span class="status-badge status-{{ $business->status }}">
                                            {{ ucfirst($business->status) }}
                                        </span>
                                    </td>
                                    <td>{{ $business->created_at?->format('M j, Y') }}</td>
                                    <td class="text-end">
                                        <a href="{{ route('businesses.show', $business->business_slug) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-eye me-1"></i>View
                                        </a>
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
