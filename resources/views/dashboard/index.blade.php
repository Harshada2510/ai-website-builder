@extends('layouts.app')

@section('content')
    <section class="welcome-card mb-4">
        <div class="row align-items-center g-4">
            <div class="col-lg-8">
                <p class="welcome-eyebrow mb-2">AI-Powered Website Creation</p>
                <h2 class="welcome-title">Welcome to AI Website Builder</h2>
                <p class="welcome-subtitle mb-4">Create professional business websites in minutes using AI.</p>
                <a href="{{ route('create-website') }}" class="btn btn-primary btn-lg dashboard-cta">
                    <i class="bi bi-plus-lg me-2"></i>Create New Website
                </a>
            </div>
            <div class="col-lg-4 d-none d-lg-block">
                <div class="welcome-visual">
                    <div class="welcome-visual-card">
                        <div class="welcome-visual-icon"><i class="bi bi-window-desktop"></i></div>
                        <div>
                            <div class="fw-semibold text-dark">Your next website</div>
                            <div class="text-muted small">Ready when you are</div>
                        </div>
                    </div>
                    <div class="welcome-visual-card welcome-visual-card-offset">
                        <div class="welcome-visual-icon welcome-visual-icon-alt"><i class="bi bi-stars"></i></div>
                        <div>
                            <div class="fw-semibold text-dark">AI-assisted setup</div>
                            <div class="text-muted small">Coming in future phases</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="mb-4">
        <div class="row g-3 g-xl-4">
            <div class="col-sm-6 col-xl-3">
                <div class="card stat-card h-100">
                    <div class="card-body">
                        <div class="stat-card-top">
                            <div>
                                <p class="stat-label mb-1">Total Websites</p>
                                <h3 class="stat-value mb-0">0</h3>
                            </div>
                            <div class="stat-icon stat-icon-primary">
                                <i class="bi bi-globe2"></i>
                            </div>
                        </div>
                        <p class="stat-foot mb-0">All websites in your workspace</p>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="card stat-card h-100">
                    <div class="card-body">
                        <div class="stat-card-top">
                            <div>
                                <p class="stat-label mb-1">Published Websites</p>
                                <h3 class="stat-value mb-0">0</h3>
                            </div>
                            <div class="stat-icon stat-icon-success">
                                <i class="bi bi-check-circle"></i>
                            </div>
                        </div>
                        <p class="stat-foot mb-0">Live and visible to visitors</p>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="card stat-card h-100">
                    <div class="card-body">
                        <div class="stat-card-top">
                            <div>
                                <p class="stat-label mb-1">Draft Websites</p>
                                <h3 class="stat-value mb-0">0</h3>
                            </div>
                            <div class="stat-icon stat-icon-warning">
                                <i class="bi bi-file-earmark-text"></i>
                            </div>
                        </div>
                        <p class="stat-foot mb-0">In progress and not published</p>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="card stat-card h-100">
                    <div class="card-body">
                        <div class="stat-card-top">
                            <div>
                                <p class="stat-label mb-1">Websites Generated</p>
                                <h3 class="stat-value mb-0">0</h3>
                            </div>
                            <div class="stat-icon stat-icon-indigo">
                                <i class="bi bi-stars"></i>
                            </div>
                        </div>
                        <p class="stat-foot mb-0">AI-generated website drafts</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="card surface-card">
        <div class="card-header surface-card-header">
            <div>
                <h5 class="mb-1">Recent Websites</h5>
                <p class="text-muted mb-0 small">Track and manage your latest website projects.</p>
            </div>
            <a href="{{ route('my-websites') }}" class="btn btn-outline-primary btn-sm">View All</a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table dashboard-table mb-0 align-middle">
                    <thead>
                        <tr>
                            <th>Website Name</th>
                            <th>Business Category</th>
                            <th>Creation Date</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="5">
                                <div class="empty-state">
                                    <div class="empty-state-icon">
                                        <i class="bi bi-window-plus"></i>
                                    </div>
                                    <h6 class="empty-state-title">No websites yet</h6>
                                    <p class="empty-state-text">You haven't created any websites. Start by creating your first AI-powered business website.</p>
                                    <a href="{{ route('create-website') }}" class="btn btn-primary">
                                        <i class="bi bi-plus-lg me-2"></i>Create Your First Website
                                    </a>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </section>
@endsection
