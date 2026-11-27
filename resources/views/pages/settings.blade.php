@extends('layouts.app')

@section('content')
    <div class="page-header mb-4">
        <div>
            <p class="page-eyebrow mb-1">Preferences</p>
            <h2 class="page-heading mb-1">Settings</h2>
            <p class="page-subheading mb-0">Manage application defaults for AI generation and website themes. API keys are not displayed or editable here.</p>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success border-0 shadow-sm mb-4">{{ session('success') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger border-0 shadow-sm mb-4">
            <div class="fw-semibold mb-1">Please fix the following errors:</div>
            <ul class="mb-0 ps-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('settings.update') }}">
        @csrf
        @method('PUT')

        <div class="row g-4">
            <div class="col-lg-6">
                <div class="card surface-card h-100">
                    <div class="card-body p-4">
                        <h5 class="mb-1">AI Generation</h5>
                        <p class="text-muted small mb-4">Current AI configuration is read-only here. The Gemini API key is never displayed.</p>

                        <div class="mb-3">
                            <label class="form-label">Configured AI Model</label>
                            <input type="text" class="form-control" value="{{ $aiModel ?: 'Not configured' }}" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">API Base URL</label>
                            <input type="text" class="form-control" value="{{ $aiBaseUrl ?: 'Not configured' }}" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Request Timeout (seconds)</label>
                            <input type="text" class="form-control" value="{{ $aiTimeout }}" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">API Key Status</label>
                            <input type="text" class="form-control" value="{{ $aiConfigured ? 'Configured (hidden)' : 'Not configured' }}" readonly>
                        </div>

                        <hr class="my-4">

                        <div class="mb-0">
                            <label for="generation_tone" class="form-label">Default Generation Tone</label>
                            <select name="generation_tone" id="generation_tone" class="form-select @error('generation_tone') is-invalid @enderror">
                                @foreach (\App\Models\AppSetting::toneOptions() as $value => $label)
                                    <option value="{{ $value }}" @selected(old('generation_tone', $settings->generation_tone) === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('generation_tone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            <div class="form-text">Used as a default preference for future website generation behavior.</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card surface-card h-100">
                    <div class="card-body p-4">
                        <h5 class="mb-1">Default Website Theme</h5>
                        <p class="text-muted small mb-4">These defaults are used for newly generated websites and can still be customized in the Website Editor.</p>

                        <div class="mb-3">
                            <label for="default_theme_preset" class="form-label">Theme Preset</label>
                            <select name="default_theme_preset" id="default_theme_preset" class="form-select @error('default_theme_preset') is-invalid @enderror">
                                <option value="">Custom</option>
                                @foreach (\App\Models\AppSetting::themePresets() as $value => $label)
                                    <option value="{{ $value }}" @selected(old('default_theme_preset', $settings->default_theme_preset) === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('default_theme_preset') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="d-flex flex-wrap gap-2 mb-3" id="themePresetButtons">
                            <button type="button" class="btn btn-sm btn-outline-secondary theme-preset-btn" data-preset="indigo" data-primary="#4F46E5" data-secondary="#0EA5E9" data-bg="#FFFFFF" data-text="#1F2937">Indigo</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary theme-preset-btn" data-preset="teal" data-primary="#0F766E" data-secondary="#14B8A6" data-bg="#F0FDFA" data-text="#134E4A">Teal</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary theme-preset-btn" data-preset="warm" data-primary="#B45309" data-secondary="#F59E0B" data-bg="#FFFBEB" data-text="#78350F">Warm</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary theme-preset-btn" data-preset="blue" data-primary="#1D4ED8" data-secondary="#60A5FA" data-bg="#EFF6FF" data-text="#1E3A8A">Blue</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary theme-preset-btn" data-preset="mono" data-primary="#111827" data-secondary="#4B5563" data-bg="#F9FAFB" data-text="#111827">Mono</button>
                        </div>

                        <div class="row g-3">
                            <div class="col-sm-6">
                                <label for="default_primary_color" class="form-label">Primary Color</label>
                                <input type="color" id="default_primary_color" name="default_primary_color" class="form-control form-control-color w-100 @error('default_primary_color') is-invalid @enderror"
                                       value="{{ old('default_primary_color', $settings->default_primary_color) }}">
                                @error('default_primary_color') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-sm-6">
                                <label for="default_secondary_color" class="form-label">Secondary Color</label>
                                <input type="color" id="default_secondary_color" name="default_secondary_color" class="form-control form-control-color w-100 @error('default_secondary_color') is-invalid @enderror"
                                       value="{{ old('default_secondary_color', $settings->default_secondary_color) }}">
                                @error('default_secondary_color') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-sm-6">
                                <label for="default_background_color" class="form-label">Background Color</label>
                                <input type="color" id="default_background_color" name="default_background_color" class="form-control form-control-color w-100 @error('default_background_color') is-invalid @enderror"
                                       value="{{ old('default_background_color', $settings->default_background_color) }}">
                                @error('default_background_color') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-sm-6">
                                <label for="default_text_color" class="form-label">Text Color</label>
                                <input type="color" id="default_text_color" name="default_text_color" class="form-control form-control-color w-100 @error('default_text_color') is-invalid @enderror"
                                       value="{{ old('default_text_color', $settings->default_text_color) }}">
                                @error('default_text_color') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="mt-4 p-3 rounded-3 border" id="themePreviewBox" style="background: {{ old('default_background_color', $settings->default_background_color) }}; color: {{ old('default_text_color', $settings->default_text_color) }};">
                            <div class="small fw-semibold mb-2" style="color: {{ old('default_primary_color', $settings->default_primary_color) }};">Theme Preview</div>
                            <div class="small mb-2">This preview reflects your default website theme colors.</div>
                            <span class="badge" style="background: {{ old('default_secondary_color', $settings->default_secondary_color) }}; color: #fff;">Sample Accent</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex gap-2 mt-4">
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-save me-2"></i>Save Settings
            </button>
        </div>
    </form>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const presetSelect = document.getElementById('default_theme_preset');
        const primary = document.getElementById('default_primary_color');
        const secondary = document.getElementById('default_secondary_color');
        const bg = document.getElementById('default_background_color');
        const text = document.getElementById('default_text_color');
        const preview = document.getElementById('themePreviewBox');

        function updatePreview() {
            preview.style.background = bg.value;
            preview.style.color = text.value;
            preview.querySelector('.fw-semibold').style.color = primary.value;
            preview.querySelector('.badge').style.background = secondary.value;
        }

        document.querySelectorAll('.theme-preset-btn').forEach(btn => {
            btn.addEventListener('click', function () {
                primary.value = this.dataset.primary;
                secondary.value = this.dataset.secondary;
                bg.value = this.dataset.bg;
                text.value = this.dataset.text;
                presetSelect.value = this.dataset.preset || '';
                updatePreview();
            });
        });

        [primary, secondary, bg, text].forEach(input => input.addEventListener('input', function () {
            presetSelect.value = '';
            updatePreview();
        }));
        presetSelect.addEventListener('change', function () {
            if (!this.value) return;
            const btn = document.querySelector('.theme-preset-btn[data-preset="' + this.value + '"]');
            if (btn) btn.click();
        });
    });
    </script>
@endsection
