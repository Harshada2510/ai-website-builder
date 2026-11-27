@php
    $mobile = $mobile ?? false;
    $navItems = [
        ['route' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'bi-grid-1x2-fill'],
        ['route' => 'create-website', 'label' => 'Create Website', 'icon' => 'bi-plus-square-fill'],
        ['route' => 'my-websites', 'label' => 'My Websites', 'icon' => 'bi-window-stack'],
        ['route' => 'business-information', 'label' => 'Business Information', 'icon' => 'bi-building'],
        ['route' => 'uploaded-files', 'label' => 'Uploaded Files', 'icon' => 'bi-folder2-open'],
        ['route' => 'ai-generator', 'label' => 'AI Generator', 'icon' => 'bi-magic'],
        ['route' => 'website-editor', 'label' => 'Website Editor', 'icon' => 'bi-pencil-square'],
        ['route' => 'settings', 'label' => 'Settings', 'icon' => 'bi-gear'],
    ];
@endphp

<div class="sidebar-inner d-flex flex-column h-100">
    @unless($mobile)
        <div class="sidebar-brand">
            <a href="{{ route('dashboard') }}" class="brand-link">
                <span class="brand-icon"><i class="bi bi-stars"></i></span>
                <div class="brand-text">
                    <span class="brand-name">AI Website Builder</span>
                    <span class="brand-tagline">Build smarter, faster</span>
                </div>
            </a>
        </div>
    @endunless

    <nav class="sidebar-nav flex-grow-1">
        <p class="sidebar-section-label">Main Menu</p>
        <ul class="nav flex-column gap-1">
            @foreach($navItems as $item)
                @php
                    $active = request()->routeIs($item['route']);
                    if ($item['route'] === 'website-editor') {
                        $active = $active || request()->routeIs('generated-websites.edit');
                    }
                @endphp
                <li class="nav-item">
                    <a href="{{ route($item['route']) }}"
                       class="sidebar-link {{ $active ? 'active' : '' }}"
                       @if($active) aria-current="page" @endif>
                        <span class="sidebar-link-icon"><i class="bi {{ $item['icon'] }}"></i></span>
                        <span class="sidebar-link-label">{{ $item['label'] }}</span>
                    </a>
                </li>
            @endforeach
        </ul>
    </nav>

    <div class="sidebar-footer">
        <div class="sidebar-user">
            <div class="sidebar-user-avatar">U</div>
            <div class="sidebar-user-info">
                <span class="sidebar-user-name">User Name</span>
                <span class="sidebar-user-role">Free Plan</span>
            </div>
        </div>
        <a href="#" class="sidebar-link sidebar-logout" onclick="event.preventDefault();" title="Logout placeholder">
            <span class="sidebar-link-icon"><i class="bi bi-box-arrow-right"></i></span>
            <span class="sidebar-link-label">Logout</span>
        </a>
    </div>
</div>
