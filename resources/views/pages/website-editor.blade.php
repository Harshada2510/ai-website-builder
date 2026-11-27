@extends('layouts.app')

@section('content')
    @php
        $businessName = $business->business_name ?? 'Business';
        $editorContent = $content;
    @endphp

    <div class="page-header mb-4">
        <div>
            <p class="page-eyebrow mb-1">Website Editor</p>
            <h2 class="page-heading mb-1">Edit: {{ $generatedWebsite->site_title ?: $businessName }}</h2>
            <p class="page-subheading mb-0">Customize text, theme, section visibility, and regenerate sections with AI. Changes are saved to this generated website.</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('generated-websites.preview', $generatedWebsite) }}" class="btn btn-outline-secondary">
                <i class="bi bi-eye me-2"></i>Preview
            </a>
            <button type="button" class="btn btn-primary" id="saveEditorBtn">
                <span class="save-label"><i class="bi bi-save me-2"></i>Save Changes</span>
                <span class="save-loading d-none"><span class="spinner-border spinner-border-sm me-2" role="status"></span>Saving...</span>
            </button>
        </div>
    </div>

    <div id="editorAlert" class="alert d-none border-0 shadow-sm mb-4" role="alert"></div>

    <div class="card surface-card mb-4" id="publishingPanel">
        <div class="card-body p-4">
            <div class="d-flex flex-column flex-lg-row justify-content-between gap-3">
                <div>
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <h5 class="mb-0">Publishing</h5>
                        @php $isPublished = $generatedWebsite->isPublished(); @endphp
                        <span id="publishStatusBadge" class="status-badge {{ $isPublished ? 'status-published' : 'status-draft' }}">
                            {{ $isPublished ? 'Published' : 'Draft' }}
                        </span>
                    </div>
                    <p class="text-muted small mb-2">Publish to make this website accessible through a public URL. Unpublish to return it to draft.</p>
                    <div id="publicUrlWrap" class="{{ $isPublished && $generatedWebsite->slug ? '' : 'd-none' }}">
                        <label class="form-label small mb-1">Public URL</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="publicUrlInput" value="{{ $generatedWebsite->isPublished() ? $generatedWebsite->publicUrl() : ($generatedWebsite->slug ? route('public-site.show', $generatedWebsite->slug) : '') }}" readonly>
                            <button class="btn btn-outline-secondary" type="button" id="copyPublicUrlBtn">
                                <i class="bi bi-link-45deg me-1"></i>Copy Link
                            </button>
                            <a class="btn btn-outline-primary {{ $isPublished ? '' : 'disabled' }}" id="openPublicUrlBtn" href="{{ $generatedWebsite->isPublished() ? $generatedWebsite->publicUrl() : '#' }}" target="_blank" rel="noopener noreferrer">
                                <i class="bi bi-box-arrow-up-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="d-flex gap-2 align-items-start flex-wrap">
                    <button type="button" class="btn btn-success" id="publishWebsiteBtn" {{ $isPublished ? 'disabled' : '' }}>
                        <span class="publish-label"><i class="bi bi-globe2 me-2"></i>Publish Website</span>
                        <span class="publish-loading d-none"><span class="spinner-border spinner-border-sm me-2"></span>Publishing...</span>
                    </button>
                    <button type="button" class="btn btn-outline-danger {{ $isPublished ? '' : 'd-none' }}" id="unpublishWebsiteBtn">
                        <span class="unpublish-label"><i class="bi bi-eye-slash me-2"></i>Unpublish</span>
                        <span class="unpublish-loading d-none"><span class="spinner-border spinner-border-sm me-2"></span>Unpublishing...</span>
                    </button>
                </div>
            </div>
            <div class="small text-muted mt-3 mb-0">
                <i class="bi bi-info-circle me-1"></i>After saving edits, the public page shows the latest saved content. This project has no authentication system; publishing controls are not owner-restricted.
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-xl-5">
            <div class="card surface-card mb-4">
                <div class="card-body p-4">
                    <h5 class="mb-3">General</h5>
                    <div class="mb-3">
                        <label class="form-label">Site Title</label>
                        <input type="text" class="form-control" id="siteTitle" maxlength="255" value="{{ $editorContent['site_title'] ?? '' }}">
                    </div>
                    <div class="mb-0">
                        <label class="form-label">Tagline</label>
                        <input type="text" class="form-control" id="tagline" maxlength="500" value="{{ $editorContent['tagline'] ?? '' }}">
                    </div>
                </div>
            </div>

            <div class="card surface-card mb-4">
                <div class="card-body p-4">
                    <h5 class="mb-3">Theme Customization</h5>
                    <div class="d-flex flex-wrap gap-2 mb-3" id="themePresets">
                        <button type="button" class="btn btn-sm btn-outline-secondary theme-preset" data-primary="#4F46E5" data-secondary="#0EA5E9" data-bg="#FFFFFF" data-text="#1F2937">Indigo</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary theme-preset" data-primary="#0F766E" data-secondary="#14B8A6" data-bg="#F0FDFA" data-text="#134E4A">Teal</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary theme-preset" data-primary="#B45309" data-secondary="#F59E0B" data-bg="#FFFBEB" data-text="#78350F">Warm</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary theme-preset" data-primary="#1D4ED8" data-secondary="#60A5FA" data-bg="#EFF6FF" data-text="#1E3A8A">Blue</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary theme-preset" data-primary="#111827" data-secondary="#4B5563" data-bg="#F9FAFB" data-text="#111827">Mono</button>
                    </div>
                    <div class="row g-3">
                        <div class="col-6">
                            <label class="form-label">Primary Color</label>
                            <input type="color" class="form-control form-control-color w-100" id="primaryColor" value="{{ $editorContent['theme']['primary_color'] ?? '#4F46E5' }}">
                        </div>
                        <div class="col-6">
                            <label class="form-label">Secondary Color</label>
                            <input type="color" class="form-control form-control-color w-100" id="secondaryColor" value="{{ $editorContent['theme']['secondary_color'] ?? '#0EA5E9' }}">
                        </div>
                        <div class="col-6">
                            <label class="form-label">Background Color</label>
                            <input type="color" class="form-control form-control-color w-100" id="backgroundColor" value="{{ $editorContent['theme']['background_color'] ?? '#FFFFFF' }}">
                        </div>
                        <div class="col-6">
                            <label class="form-label">Text Color</label>
                            <input type="color" class="form-control form-control-color w-100" id="textColor" value="{{ $editorContent['theme']['text_color'] ?? '#1F2937' }}">
                        </div>
                    </div>
                </div>
            </div>

            <div class="card surface-card mb-4">
                <div class="card-body p-4">
                    <h5 class="mb-3">Sections</h5>
                    <p class="text-muted small">Edit content, toggle visibility, or regenerate a section with AI. Hidden sections keep their content.</p>
                    <div class="accordion" id="sectionsAccordion">
                        @foreach ($editorContent['sections'] ?? [] as $i => $section)
                            @php
                                $type = $section['type'] ?? 'section';
                                $label = ucwords(str_replace('_', ' ', $type));
                                $visible = (bool) ($section['visible'] ?? true);
                            @endphp
                            <div class="accordion-item border mb-2 rounded-3 overflow-hidden section-editor" data-section-type="{{ $type }}">
                                <h2 class="accordion-header">
                                    <button class="accordion-button {{ $i > 0 ? 'collapsed' : '' }}" type="button" data-bs-toggle="collapse" data-bs-target="#section-{{ $type }}" aria-expanded="{{ $i === 0 ? 'true' : 'false' }}">
                                        <span class="me-auto">{{ $label }}</span>
                                    </button>
                                </h2>
                                <div id="section-{{ $type }}" class="accordion-collapse collapse {{ $i === 0 ? 'show' : '' }}" data-bs-parent="#sectionsAccordion">
                                    <div class="accordion-body">
                                        <div class="d-flex justify-content-between align-items-center mb-3 gap-2 flex-wrap">
                                            <div class="form-check form-switch mb-0">
                                                <input class="form-check-input section-visible" type="checkbox" id="visible-{{ $type }}" {{ $visible ? 'checked' : '' }}>
                                                <label class="form-check-label" for="visible-{{ $type }}">Visible</label>
                                            </div>
                                            <button type="button" class="btn btn-sm btn-outline-primary regenerate-btn" data-section-type="{{ $type }}">
                                                <span class="regen-label"><i class="bi bi-stars me-1"></i>Regenerate with AI</span>
                                                <span class="regen-loading d-none"><span class="spinner-border spinner-border-sm me-1"></span>Regenerating...</span>
                                            </button>
                                        </div>

                                        @if (in_array($type, ['hero', 'about', 'contact']))
                                            <div class="mb-3">
                                                <label class="form-label">Heading</label>
                                                <input type="text" class="form-control section-title" maxlength="160" value="{{ $section['title'] ?? '' }}">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">{{ $type === 'hero' ? 'Subtitle / Content' : 'Description / Content' }}</label>
                                                <textarea class="form-control section-content" rows="4" maxlength="5000">{{ $section['content'] ?? '' }}</textarea>
                                            </div>
                                            @if ($type === 'hero')
                                                <div class="row g-2 mb-3">
                                                    <div class="col-md-6"><label class="form-label">Primary Button Label</label><input type="text" class="form-control section-primary-btn-text" maxlength="60" value="{{ $section['primary_button_text'] ?? 'Call Now' }}"></div>
                                                    <div class="col-md-6"><label class="form-label">Primary Button URL</label><input type="text" class="form-control section-primary-btn-url" maxlength="500" value="{{ $section['primary_button_url'] ?? '' }}" placeholder="https://, tel:, or mailto:"></div>
                                                    <div class="col-md-6"><label class="form-label">Secondary Button Label</label><input type="text" class="form-control section-secondary-btn-text" maxlength="60" value="{{ $section['secondary_button_text'] ?? 'Email Us' }}"></div>
                                                    <div class="col-md-6"><label class="form-label">Secondary Button URL</label><input type="text" class="form-control section-secondary-btn-url" maxlength="500" value="{{ $section['secondary_button_url'] ?? '' }}" placeholder="https://, tel:, or mailto:"></div>
                                                </div>
                                            @endif
                                            @if ($type === 'contact')
                                                <div class="row g-2">
                                                    <div class="col-md-6"><label class="form-label">Address</label><input type="text" class="form-control section-address" maxlength="500" value="{{ $section['address'] ?? '' }}"></div>
                                                    <div class="col-md-6"><label class="form-label">Phone</label><input type="text" class="form-control section-phone" maxlength="50" value="{{ $section['phone'] ?? '' }}"></div>
                                                    <div class="col-md-6"><label class="form-label">Email</label><input type="email" class="form-control section-email" maxlength="255" value="{{ $section['email'] ?? '' }}"></div>
                                                    <div class="col-md-6"><label class="form-label">Website URL</label><input type="url" class="form-control section-website-url" maxlength="500" value="{{ $section['website_url'] ?? '' }}"></div>
                                                </div>
                                            @endif
                                        @elseif ($type === 'services')
                                            <div class="mb-3">
                                                <label class="form-label">Heading</label>
                                                <input type="text" class="form-control section-title" maxlength="160" value="{{ $section['title'] ?? '' }}">
                                            </div>
                                            <label class="form-label">Service Items</label>
                                            <div class="service-items">
                                                @foreach (is_array($section['items'] ?? null) ? $section['items'] : [] as $item)
                                                    <div class="border rounded-3 p-3 mb-2 service-item">
                                                        <div class="mb-2"><input type="text" class="form-control form-control-sm service-name" placeholder="Service name" maxlength="120" value="{{ is_array($item) ? ($item['name'] ?? '') : $item }}"></div>
                                                        <div class="mb-2"><textarea class="form-control form-control-sm service-desc" rows="2" placeholder="Description" maxlength="500">{{ is_array($item) ? ($item['description'] ?? '') : '' }}</textarea></div>
                                                        <button type="button" class="btn btn-sm btn-outline-danger remove-item-btn">Remove</button>
                                                    </div>
                                                @endforeach
                                            </div>
                                            <button type="button" class="btn btn-sm btn-outline-secondary add-service-item-btn"><i class="bi bi-plus-lg me-1"></i>Add Service</button>
                                        @elseif ($type === 'why_choose_us')
                                            <div class="mb-3">
                                                <label class="form-label">Heading</label>
                                                <input type="text" class="form-control section-title" maxlength="160" value="{{ $section['title'] ?? '' }}">
                                            </div>
                                            <label class="form-label">Items</label>
                                            <div class="why-items">
                                                @foreach (is_array($section['items'] ?? null) ? $section['items'] : [] as $item)
                                                    @php $text = is_array($item) ? ($item['text'] ?? $item['title'] ?? '') : $item; @endphp
                                                    <div class="input-group mb-2 why-item">
                                                        <input type="text" class="form-control form-control-sm why-text" maxlength="200" value="{{ $text }}">
                                                        <button type="button" class="btn btn-outline-danger remove-item-btn">Remove</button>
                                                    </div>
                                                @endforeach
                                            </div>
                                            <button type="button" class="btn btn-sm btn-outline-secondary add-why-item-btn"><i class="bi bi-plus-lg me-1"></i>Add Item</button>
                                        @elseif ($type === 'footer')
                                            <div class="mb-0">
                                                <label class="form-label">Footer Text</label>
                                                <input type="text" class="form-control section-content" maxlength="300" value="{{ $section['content'] ?? '' }}">
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-7">
            <div class="card surface-card sticky-top" style="top: 1rem;">
                <div class="card-body p-3 p-md-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0">Live Preview</h5>
                        <span class="small text-muted">Updates as you edit</span>
                    </div>
                    <div class="border rounded-3 overflow-hidden">
                        <div id="editorPreview" class="generated-preview" style="min-height: 400px;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .generated-preview .gw-hero { background: linear-gradient(135deg, var(--gw-primary), var(--gw-secondary)); }
        .generated-preview .min-vh-25 { min-height: 20vh; }
        .generated-preview .gw-section-hidden { display: none; }
        #editorPreview { background: var(--gw-bg, #fff); color: var(--gw-text, #1f2937); }
        #editorPreview .text-muted, #editorPreview .text-secondary { color: color-mix(in srgb, var(--gw-text, #1f2937) 65%, transparent) !important; }
    </style>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const initialContent = @json($editorContent);
        const business = @json(['name' => $businessName, 'category' => $business->business_category ?? '', 'city' => $business->city ?? '']);
        const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const updateUrl = @json(route('generated-websites.update', $generatedWebsite));
        const regenUrl = @json(route('generated-websites.regenerate-section', $generatedWebsite));
        const publishUrl = @json(route('generated-websites.publish', $generatedWebsite));
        const unpublishUrl = @json(route('generated-websites.unpublish', $generatedWebsite));

        const state = JSON.parse(JSON.stringify(initialContent));
        const preview = document.getElementById('editorPreview');
        const alertBox = document.getElementById('editorAlert');

        function esc(str) {
            return String(str ?? '')
                .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;').replace(/'/g, '&#039;');
        }
        function nl2br(str) { return esc(str).replace(/\n/g, '<br>'); }
        function showAlert(type, msg) {
            alertBox.className = 'alert border-0 shadow-sm mb-4 alert-' + type;
            alertBox.textContent = msg;
            alertBox.classList.remove('d-none');
            setTimeout(() => { if (alertBox.textContent === msg) alertBox.classList.add('d-none'); }, 5000);
        }
        function getTheme() {
            return {
                primary_color: document.getElementById('primaryColor').value.toUpperCase(),
                secondary_color: document.getElementById('secondaryColor').value.toUpperCase(),
                background_color: document.getElementById('backgroundColor').value.toUpperCase(),
                text_color: document.getElementById('textColor').value.toUpperCase(),
            };
        }
        function getSectionsFromDOM() {
            const sections = [];
            document.querySelectorAll('.section-editor').forEach(el => {
                const type = el.dataset.sectionType;
                const visible = el.querySelector('.section-visible').checked;
                const title = el.querySelector('.section-title')?.value || '';
                const content = el.querySelector('.section-content')?.value || '';
                const section = { type, visible, title, content };
                if (type === 'services') {
                    section.items = [];
                    el.querySelectorAll('.service-item').forEach(item => {
                        section.items.push({
                            name: item.querySelector('.service-name').value || '',
                            description: item.querySelector('.service-desc').value || ''
                        });
                    });
                }
                if (type === 'why_choose_us') {
                    section.items = [];
                    el.querySelectorAll('.why-item').forEach(item => {
                        section.items.push(item.querySelector('.why-text').value || '');
                    });
                }
                if (type === 'hero') {
                    section.primary_button_text = el.querySelector('.section-primary-btn-text')?.value || '';
                    section.primary_button_url = el.querySelector('.section-primary-btn-url')?.value || '';
                    section.secondary_button_text = el.querySelector('.section-secondary-btn-text')?.value || '';
                    section.secondary_button_url = el.querySelector('.section-secondary-btn-url')?.value || '';
                }
                if (type === 'contact') {
                    section.address = el.querySelector('.section-address')?.value || null;
                    section.phone = el.querySelector('.section-phone')?.value || null;
                    section.email = el.querySelector('.section-email')?.value || null;
                    section.website_url = el.querySelector('.section-website-url')?.value || null;
                    section.social_links = (state.sections.find(s => s.type === 'contact') || {}).social_links || {};
                }
                sections.push(section);
            });
            return sections;
        }
        function collectState() {
            state.site_title = document.getElementById('siteTitle').value;
            state.tagline = document.getElementById('tagline').value;
            state.theme = getTheme();
            state.sections = getSectionsFromDOM();
            return state;
        }

        function renderPreview() {
            const s = collectState();
            const theme = s.theme;
            preview.style.setProperty('--gw-primary', theme.primary_color);
            preview.style.setProperty('--gw-secondary', theme.secondary_color);
            preview.style.setProperty('--gw-bg', theme.background_color);
            preview.style.setProperty('--gw-text', theme.text_color);
            preview.style.background = theme.background_color;
            preview.style.color = theme.text_color;

            let html = '';
            (s.sections || []).forEach(section => {
                if (!section.visible) return;
                if (section.type === 'hero') {
                    const pText = (section.primary_button_text || '').trim();
                    const pUrl = (section.primary_button_url || '').trim();
                    const sText = (section.secondary_button_text || '').trim();
                    const sUrl = (section.secondary_button_url || '').trim();
                    let btns = '';
                    if (pText && pUrl) btns += `<a href="${esc(pUrl)}" class="btn btn-light btn-lg me-2 mb-2">${esc(pText)}</a>`;
                    if (sText && sUrl) btns += `<a href="${esc(sUrl)}" class="btn btn-outline-light btn-lg mb-2">${esc(sText)}</a>`;
                    html += `<section class="gw-hero text-white"><div class="container py-5"><div class="py-4"><span class="badge bg-white text-dark mb-3 px-3 py-2">${esc(business.category || 'Business')}${business.city ? ' · ' + esc(business.city) : ''}</span><h1 class="display-6 fw-bold mb-3">${esc(section.title || s.site_title || 'Welcome')}</h1><p class="lead mb-3 opacity-90">${nl2br(section.content || '')}</p>${btns ? `<div class="d-flex flex-wrap">${btns}</div>` : ''}</div></div></section>`;
                } else if (section.type === 'about') {
                    html += `<section class="py-5"><div class="container py-2"><p class="text-uppercase small fw-semibold mb-2" style="color:var(--gw-primary)">About</p><h2 class="fw-bold mb-3">${esc(section.title || 'About Us')}</h2><p class="mb-0" style="white-space:pre-line;opacity:.85">${esc(section.content || '')}</p></div></section>`;
                } else if (section.type === 'services') {
                    const items = (section.items || []).filter(i => i && (i.name || '').trim() !== '');
                    html += `<section class="py-5" style="background:color-mix(in srgb, var(--gw-bg) 92%, var(--gw-text) 8%)"><div class="container py-2"><p class="text-uppercase small fw-semibold mb-2 text-center" style="color:var(--gw-primary)">What We Offer</p><h2 class="fw-bold text-center mb-4">${esc(section.title || 'Services')}</h2><div class="row g-3">${items.map(item => `<div class="col-md-6"><div class="card h-100 border-0 shadow-sm"><div class="card-body p-3"><h6 class="fw-semibold mb-1">${esc(item.name)}</h6><p class="small mb-0 opacity-75">${esc(item.description || '')}</p></div></div></div>`).join('') || '<div class="col-12 text-center text-muted small">No service items added.</div>'}</div></div></section>`;
                } else if (section.type === 'why_choose_us') {
                    const items = (section.items || []).filter(i => String(i || '').trim() !== '');
                    html += `<section class="py-5"><div class="container py-2"><p class="text-uppercase small fw-semibold mb-2" style="color:var(--gw-primary)">Why Choose Us</p><h2 class="fw-bold mb-4">${esc(section.title || 'Why Choose Us')}</h2><div class="row g-2">${items.map(item => `<div class="col-md-6"><div class="p-3 rounded-3 small" style="background:color-mix(in srgb, var(--gw-bg) 92%, var(--gw-text) 8%)"><i class="bi bi-star-fill me-2" style="color:var(--gw-secondary)"></i>${esc(item)}</div></div>`).join('')}</div></div></section>`;
                } else if (section.type === 'contact') {
                    html += `<section class="py-5" style="background:color-mix(in srgb, var(--gw-bg) 92%, var(--gw-text) 8%)"><div class="container py-2"><div class="row g-3"><div class="col-lg-5"><p class="text-uppercase small fw-semibold mb-2" style="color:var(--gw-primary)">Contact</p><h2 class="fw-bold mb-3">${esc(section.title || 'Contact Us')}</h2><p class="opacity-75">${nl2br(section.content || '')}</p></div><div class="col-lg-7"><div class="card border-0 shadow-sm"><div class="card-body p-3"><dl class="row mb-0 small"><dt class="col-sm-4">Address</dt><dd class="col-sm-8">${esc(section.address || 'Contact the business for more information.')}</dd><dt class="col-sm-4">Phone</dt><dd class="col-sm-8">${esc(section.phone || 'Contact the business for more information.')}</dd><dt class="col-sm-4">Email</dt><dd class="col-sm-8">${esc(section.email || 'Contact the business for more information.')}</dd><dt class="col-sm-4">Website</dt><dd class="col-sm-8">${section.website_url ? `<a href="${esc(section.website_url)}" target="_blank" rel="noopener noreferrer">${esc(section.website_url)}</a>` : 'Contact the business for more information.'}</dd></dl></div></div></div></div></div></section>`;
                } else if (section.type === 'footer') {
                    html += `<footer class="py-4 text-white" style="background:#111827;"><div class="container small">${esc(section.content || '')}</div></footer>`;
                }
            });
            preview.innerHTML = html || '<div class="p-4 text-muted">No visible sections.</div>';
        }

        function setSectionToDOM(type, section) {
            const el = document.querySelector(`.section-editor[data-section-type="${type}"]`);
            if (!el) return;
            el.querySelector('.section-visible').checked = !!section.visible;
            const title = el.querySelector('.section-title'); if (title) title.value = section.title || '';
            const content = el.querySelector('.section-content'); if (content) content.value = section.content || '';
            if (type === 'hero') {
                const pbt = el.querySelector('.section-primary-btn-text'); if (pbt) pbt.value = section.primary_button_text || '';
                const pbu = el.querySelector('.section-primary-btn-url'); if (pbu) pbu.value = section.primary_button_url || '';
                const sbt = el.querySelector('.section-secondary-btn-text'); if (sbt) sbt.value = section.secondary_button_text || '';
                const sbu = el.querySelector('.section-secondary-btn-url'); if (sbu) sbu.value = section.secondary_button_url || '';
            }
            if (type === 'contact') {
                el.querySelector('.section-address').value = section.address || '';
                el.querySelector('.section-phone').value = section.phone || '';
                el.querySelector('.section-email').value = section.email || '';
                el.querySelector('.section-website-url').value = section.website_url || '';
            }
            if (type === 'services') {
                const container = el.querySelector('.service-items');
                container.innerHTML = '';
                (section.items || []).forEach(item => addServiceItem(container, item.name || '', item.description || ''));
            }
            if (type === 'why_choose_us') {
                const container = el.querySelector('.why-items');
                container.innerHTML = '';
                (section.items || []).forEach(item => addWhyItem(container, typeof item === 'string' ? item : (item.text || '')));
            }
        }

        function addServiceItem(container, name = '', desc = '') {
            const div = document.createElement('div');
            div.className = 'border rounded-3 p-3 mb-2 service-item';
            div.innerHTML = `<div class="mb-2"><input type="text" class="form-control form-control-sm service-name" placeholder="Service name" maxlength="120" value="${esc(name)}"></div><div class="mb-2"><textarea class="form-control form-control-sm service-desc" rows="2" placeholder="Description" maxlength="500">${esc(desc)}</textarea></div><button type="button" class="btn btn-sm btn-outline-danger remove-item-btn">Remove</button>`;
            container.appendChild(div);
            bindDynamic(div);
        }
        function addWhyItem(container, text = '') {
            const div = document.createElement('div');
            div.className = 'input-group mb-2 why-item';
            div.innerHTML = `<input type="text" class="form-control form-control-sm why-text" maxlength="200" value="${esc(text)}"><button type="button" class="btn btn-outline-danger remove-item-btn">Remove</button>`;
            container.appendChild(div);
            bindDynamic(div);
        }
        function bindDynamic(scope) {
            scope.querySelectorAll('input,textarea').forEach(i => i.addEventListener('input', renderPreview));
            scope.querySelectorAll('.remove-item-btn').forEach(btn => btn.addEventListener('click', function () {
                this.closest('.service-item, .why-item').remove();
                renderPreview();
            }));
        }

        document.querySelectorAll('input,textarea').forEach(i => i.addEventListener('input', renderPreview));
        document.querySelectorAll('input[type="checkbox"]').forEach(i => i.addEventListener('change', renderPreview));
        document.querySelectorAll('.theme-preset').forEach(btn => btn.addEventListener('click', function () {
            document.getElementById('primaryColor').value = this.dataset.primary;
            document.getElementById('secondaryColor').value = this.dataset.secondary;
            document.getElementById('backgroundColor').value = this.dataset.bg;
            document.getElementById('textColor').value = this.dataset.text;
            renderPreview();
        }));
        document.querySelectorAll('.add-service-item-btn').forEach(btn => btn.addEventListener('click', function () {
            addServiceItem(this.closest('.accordion-body').querySelector('.service-items'));
            renderPreview();
        }));
        document.querySelectorAll('.add-why-item-btn').forEach(btn => btn.addEventListener('click', function () {
            addWhyItem(this.closest('.accordion-body').querySelector('.why-items'));
            renderPreview();
        }));
        document.querySelectorAll('.remove-item-btn').forEach(btn => btn.addEventListener('click', function () {
            this.closest('.service-item, .why-item').remove();
            renderPreview();
        }));

        document.querySelectorAll('.regenerate-btn').forEach(btn => btn.addEventListener('click', async function () {
            const type = this.dataset.sectionType;
            const label = this.querySelector('.regen-label');
            const loading = this.querySelector('.regen-loading');
            this.disabled = true; label.classList.add('d-none'); loading.classList.remove('d-none');
            try {
                const res = await fetch(regenUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                    body: JSON.stringify({ section_type: type })
                });
                const data = await res.json();
                if (!res.ok || !data.ok) throw new Error(data.message || 'Regeneration failed.');
                setSectionToDOM(type, data.section);
                if (data.content && data.content.sections) {
                    const idx = state.sections.findIndex(s => s.type === type);
                    if (idx >= 0) state.sections[idx] = data.section;
                }
                renderPreview();
                showAlert('success', data.message || 'Section regenerated.');
            } catch (e) {
                showAlert('danger', e.message || 'Unable to regenerate section.');
            } finally {
                this.disabled = false; label.classList.remove('d-none'); loading.classList.add('d-none');
            }
        }));

        document.getElementById('saveEditorBtn').addEventListener('click', async function () {
            const btn = this;
            const label = btn.querySelector('.save-label');
            const loading = btn.querySelector('.save-loading');
            btn.disabled = true; label.classList.add('d-none'); loading.classList.remove('d-none');
            try {
                const payload = collectState();
                const res = await fetch(updateUrl, {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                    body: JSON.stringify(payload)
                });
                const data = await res.json();
                if (!res.ok || !data.ok) {
                    const msg = data.message || (data.errors ? Object.values(data.errors).flat().join(' ') : 'Save failed.');
                    throw new Error(msg);
                }
                if (data.content) {
                    state.theme = data.content.theme || state.theme;
                    state.sections = data.content.sections || state.sections;
                }
                renderPreview();
                showAlert('success', data.message || 'Saved.');
            } catch (e) {
                showAlert('danger', e.message || 'Unable to save changes.');
            } finally {
                btn.disabled = false; label.classList.remove('d-none'); loading.classList.add('d-none');
            }
        });

        function setPublishUI(isPublished, publicUrl) {
            const badge = document.getElementById('publishStatusBadge');
            const wrap = document.getElementById('publicUrlWrap');
            const input = document.getElementById('publicUrlInput');
            const openBtn = document.getElementById('openPublicUrlBtn');
            const publishBtn = document.getElementById('publishWebsiteBtn');
            const unpublishBtn = document.getElementById('unpublishWebsiteBtn');
            if (badge) {
                badge.className = 'status-badge ' + (isPublished ? 'status-published' : 'status-draft');
                badge.textContent = isPublished ? 'Published' : 'Draft';
            }
            if (input && publicUrl) input.value = publicUrl;
            if (openBtn) {
                if (isPublished && publicUrl) {
                    openBtn.href = publicUrl;
                    openBtn.classList.remove('disabled');
                } else {
                    openBtn.href = '#';
                    openBtn.classList.add('disabled');
                }
            }
            if (wrap) wrap.classList.toggle('d-none', !isPublished || !publicUrl);
            if (publishBtn) publishBtn.disabled = !!isPublished;
            if (unpublishBtn) unpublishBtn.classList.toggle('d-none', !isPublished);
        }

        document.getElementById('copyPublicUrlBtn')?.addEventListener('click', async function () {
            const url = document.getElementById('publicUrlInput')?.value || '';
            if (!url) return;
            try {
                await navigator.clipboard.writeText(url);
                showAlert('success', 'Public URL copied to clipboard.');
            } catch (e) {
                const input = document.getElementById('publicUrlInput');
                input?.select();
                document.execCommand('copy');
                showAlert('success', 'Public URL copied to clipboard.');
            }
        });

        document.getElementById('publishWebsiteBtn')?.addEventListener('click', async function () {
            const btn = this;
            const label = btn.querySelector('.publish-label');
            const loading = btn.querySelector('.publish-loading');
            btn.disabled = true; label.classList.add('d-none'); loading.classList.remove('d-none');
            try {
                const res = await fetch(publishUrl, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }
                });
                const data = await res.json();
                if (!res.ok || !data.ok) throw new Error(data.message || 'Unable to publish website.');
                setPublishUI(true, data.public_url || '');
                showAlert('success', data.message || 'Website published.');
            } catch (e) {
                btn.disabled = false;
                showAlert('danger', e.message || 'Unable to publish website.');
            } finally {
                label.classList.remove('d-none'); loading.classList.add('d-none');
            }
        });

        document.getElementById('unpublishWebsiteBtn')?.addEventListener('click', async function () {
            const btn = this;
            const label = btn.querySelector('.unpublish-label');
            const loading = btn.querySelector('.unpublish-loading');
            btn.disabled = true; label.classList.add('d-none'); loading.classList.remove('d-none');
            try {
                const res = await fetch(unpublishUrl, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }
                });
                const data = await res.json();
                if (!res.ok || !data.ok) throw new Error(data.message || 'Unable to unpublish website.');
                setPublishUI(false, data.public_url || document.getElementById('publicUrlInput')?.value || '');
                showAlert('success', data.message || 'Website unpublished.');
            } catch (e) {
                showAlert('danger', e.message || 'Unable to unpublish website.');
            } finally {
                btn.disabled = false; label.classList.remove('d-none'); loading.classList.add('d-none');
            }
        });

        renderPreview();
    });
    </script>
@endsection
