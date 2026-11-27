<header class="app-navbar">
    <div class="navbar-left">
        <button class="btn btn-light navbar-menu-btn d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarMobile" aria-controls="sidebarMobile">
            <i class="bi bi-list fs-4"></i>
        </button>
        <div>
            <h1 class="navbar-page-title mb-0">{{ $pageTitle ?? 'Dashboard' }}</h1>
            <p class="navbar-date mb-0">{{ now()->format('l, F j, Y') }}</p>
        </div>
    </div>

    <div class="navbar-right">
        <div class="navbar-search d-none d-md-block">
            <i class="bi bi-search navbar-search-icon"></i>
            <input type="text" class="form-control navbar-search-input" placeholder="Search..." aria-label="Search placeholder">
        </div>

        <button type="button" class="btn navbar-icon-btn" aria-label="Notifications">
            <i class="bi bi-bell"></i>
            <span class="navbar-notification-dot"></span>
        </button>

        <div class="navbar-profile">
            <div class="navbar-avatar">U</div>
            <div class="d-none d-sm-block">
                <div class="navbar-profile-name">User Name</div>
                <div class="navbar-profile-role">Administrator</div>
            </div>
        </div>
    </div>
</header>
