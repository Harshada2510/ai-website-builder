@php
    $sections = collect($content['sections'] ?? [])->filter(fn ($s) => is_array($s));
    $theme = is_array($content['theme'] ?? null) ? $content['theme'] : [];
    $primary = preg_match('/^#[0-9A-Fa-f]{6}$/', $theme['primary_color'] ?? '') ? $theme['primary_color'] : '#4f46e5';
    $secondary = preg_match('/^#[0-9A-Fa-f]{6}$/', $theme['secondary_color'] ?? '') ? $theme['secondary_color'] : '#0ea5e9';
    $bgColor = preg_match('/^#[0-9A-Fa-f]{6}$/', $theme['background_color'] ?? '') ? $theme['background_color'] : '#ffffff';
    $textColor = preg_match('/^#[0-9A-Fa-f]{6}$/', $theme['text_color'] ?? '') ? $theme['text_color'] : '#1f2937';
@endphp

<div class="generated-preview" style="--gw-primary: {{ $primary }}; --gw-secondary: {{ $secondary }}; --gw-bg: {{ $bgColor }}; --gw-text: {{ $textColor }}; background: var(--gw-bg); color: var(--gw-text);">
    @foreach ($sections as $section)
        @php
            $type = $section['type'] ?? '';
            $visible = (bool) ($section['visible'] ?? true);
        @endphp
        @continue(! $visible)

        @if ($type === 'hero')
            @php
                $pBtnText = trim((string) ($section['primary_button_text'] ?? ''));
                $pBtnUrl = trim((string) ($section['primary_button_url'] ?? ''));
                $sBtnText = trim((string) ($section['secondary_button_text'] ?? ''));
                $sBtnUrl = trim((string) ($section['secondary_button_url'] ?? ''));
                $validUrl = fn($u) => is_string($u) && preg_match('/^(https?:\/\/|mailto:|tel:)/i', $u);
                if ($pBtnText === '' && $pBtnUrl === '' && $sBtnText === '' && $sBtnUrl === '') {
                    $pBtnText = $business?->phone ? 'Call Now' : '';
                    $pBtnUrl = $business?->phone ? 'tel:'.preg_replace('/[^\d+]/', '', $business->phone) : '';
                    $sBtnText = $business?->email ? 'Email Us' : '';
                    $sBtnUrl = $business?->email ? 'mailto:'.$business->email : '';
                }
            @endphp
            <section class="gw-hero text-white">
                <div class="container py-5">
                    <div class="row align-items-center min-vh-25 py-4">
                        <div class="col-lg-8">
                            <span class="badge bg-white text-dark mb-3 px-3 py-2">{{ $business?->business_category ?: 'Business' }}{{ $business?->city ? ' · '.$business->city : '' }}</span>
                            <h1 class="display-5 fw-bold mb-3">{{ $section['title'] ?? ($generatedWebsite->site_title ?: 'Welcome') }}</h1>
                            <p class="lead mb-4 opacity-90">{{ $section['content'] ?? '' }}</p>
                            @if (($pBtnText && $validUrl($pBtnUrl)) || ($sBtnText && $validUrl($sBtnUrl)))
                                <div class="d-flex gap-2 flex-wrap">
                                    @if ($pBtnText && $validUrl($pBtnUrl))
                                        <a href="{{ $pBtnUrl }}" class="btn btn-light btn-lg">{{ $pBtnText }}</a>
                                    @endif
                                    @if ($sBtnText && $validUrl($sBtnUrl))
                                        <a href="{{ $sBtnUrl }}" class="btn btn-outline-light btn-lg">{{ $sBtnText }}</a>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </section>
        @elseif ($type === 'about')
            <section class="py-5">
                <div class="container py-3">
                    <div class="row justify-content-center">
                        <div class="col-lg-9">
                            <p class="text-uppercase small fw-semibold mb-2" style="color: var(--gw-primary);">About</p>
                            <h2 class="fw-bold mb-3">{{ $section['title'] ?? 'About Us' }}</h2>
                            <p class="mb-0" style="white-space: pre-line;">{{ $section['content'] ?? '' }}</p>
                        </div>
                    </div>
                </div>
            </section>
        @elseif ($type === 'services')
            @php $items = is_array($section['items'] ?? null) ? $section['items'] : []; @endphp
            <section class="py-5 bg-light" style="background: color-mix(in srgb, var(--gw-bg) 92%, var(--gw-text) 8%) !important;">
                <div class="container py-3">
                    <div class="text-center mb-4">
                        <p class="text-uppercase small fw-semibold mb-2" style="color: var(--gw-primary);">What We Offer</p>
                        <h2 class="fw-bold">{{ $section['title'] ?? 'Services' }}</h2>
                    </div>
                    <div class="row g-4">
                        @forelse ($items as $item)
                            @php
                                $name = is_array($item) ? ($item['name'] ?? '') : (string) $item;
                                $desc = is_array($item) ? ($item['description'] ?? '') : '';
                            @endphp
                            <div class="col-md-6 col-lg-4">
                                <div class="card h-100 border-0 shadow-sm">
                                    <div class="card-body p-4">
                                        <div class="mb-3" style="color: var(--gw-primary); font-size: 1.5rem;"><i class="bi bi-check-circle-fill"></i></div>
                                        <h5 class="fw-semibold mb-2">{{ $name }}</h5>
                                        <p class="small mb-0">{{ $desc }}</p>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-12"><p class="text-center text-muted mb-0">Contact the business for more information.</p></div>
                        @endforelse
                    </div>
                </div>
            </section>
        @elseif ($type === 'why_choose_us')
            @php $items = is_array($section['items'] ?? null) ? $section['items'] : []; @endphp
            <section class="py-5">
                <div class="container py-3">
                    <div class="row justify-content-center">
                        <div class="col-lg-9">
                            <p class="text-uppercase small fw-semibold mb-2" style="color: var(--gw-primary);">Why Choose Us</p>
                            <h2 class="fw-bold mb-4">{{ $section['title'] ?? 'Why Choose Us' }}</h2>
                            <div class="row g-3">
                                @foreach ($items as $item)
                                    @php $text = is_array($item) ? ($item['text'] ?? $item['title'] ?? '') : (string) $item; @endphp
                                    <div class="col-md-6">
                                        <div class="d-flex gap-3 p-3 rounded-3 h-100" style="background: color-mix(in srgb, var(--gw-bg) 92%, var(--gw-text) 8%);">
                                            <span style="color: var(--gw-secondary);"><i class="bi bi-star-fill"></i></span>
                                            <span>{{ $text }}</span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        @elseif ($type === 'contact')
            @php $social = is_array($section['social_links'] ?? null) ? $section['social_links'] : []; @endphp
            <section class="py-5" style="background: color-mix(in srgb, var(--gw-bg) 92%, var(--gw-text) 8%);">
                <div class="container py-3">
                    <div class="row g-4">
                        <div class="col-lg-5">
                            <p class="text-uppercase small fw-semibold mb-2" style="color: var(--gw-primary);">Contact</p>
                            <h2 class="fw-bold mb-3">{{ $section['title'] ?? 'Contact Us' }}</h2>
                            <p>{{ $section['content'] ?? '' }}</p>
                        </div>
                        <div class="col-lg-7">
                            <div class="card border-0 shadow-sm">
                                <div class="card-body p-4">
                                    <dl class="row mb-0 detail-list detail-list-sm">
                                        <dt class="col-sm-4">Address</dt>
                                        <dd class="col-sm-8">{{ $section['address'] ?? 'Contact the business for more information.' }}</dd>
                                        <dt class="col-sm-4">Phone</dt>
                                        <dd class="col-sm-8">
                                            @if (! empty($section['phone']))
                                                <a href="tel:{{ preg_replace('/[^\d+]/', '', $section['phone']) }}">{{ $section['phone'] }}</a>
                                            @else
                                                Contact the business for more information.
                                            @endif
                                        </dd>
                                        <dt class="col-sm-4">Email</dt>
                                        <dd class="col-sm-8">
                                            @if (! empty($section['email']))
                                                <a href="mailto:{{ $section['email'] }}">{{ $section['email'] }}</a>
                                            @else
                                                Contact the business for more information.
                                            @endif
                                        </dd>
                                        <dt class="col-sm-4">Website</dt>
                                        <dd class="col-sm-8">
                                            @if (! empty($section['website_url']))
                                                <a href="{{ $section['website_url'] }}" target="_blank" rel="noopener noreferrer">{{ $section['website_url'] }}</a>
                                            @else
                                                Contact the business for more information.
                                            @endif
                                        </dd>
                                    </dl>
                                    @if (! empty($social))
                                        <div class="d-flex gap-2 flex-wrap mt-3 pt-3 border-top">
                                            @foreach ($social as $platform => $url)
                                                <a href="{{ $url }}" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-secondary">{{ ucfirst(str_replace('_', ' ', $platform)) }}</a>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        @elseif ($type === 'footer')
            <footer class="py-4 text-white" style="background: #111827;">
                <div class="container">
                    <div class="d-flex flex-column flex-md-row justify-content-between gap-2 small">
                        <span>{{ $section['content'] ?? '' }}</span>
                        <span style="opacity:.7;">{{ !empty($publicView) ? ($business?->business_name ?? '') : 'Generated preview - not publicly published' }}</span>
                    </div>
                </div>
            </footer>
        @endif
    @endforeach
</div>

<style>
    .generated-preview .gw-hero {
        background: linear-gradient(135deg, var(--gw-primary), var(--gw-secondary));
    }
    .generated-preview .min-vh-25 { min-height: 25vh; }
</style>
