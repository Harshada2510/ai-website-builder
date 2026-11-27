<?php

namespace App\Http\Controllers;

use App\Models\GeneratedWebsite;
use App\Services\AIWebsiteGenerationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Throwable;

class WebsiteEditorController extends Controller
{
    public function __construct(
        private readonly AIWebsiteGenerationService $aiService,
    ) {
    }

    public function edit(GeneratedWebsite $generatedWebsite): View|RedirectResponse
    {
        $generatedWebsite->load('business');

        if (! $generatedWebsite->isCompleted() || ! is_array($generatedWebsite->generated_content)) {
            return redirect()
                ->route('generated-websites.preview', $generatedWebsite)
                ->with('error', 'Only completed generated websites can be edited.');
        }

        $content = $this->normalizeEditorContent($generatedWebsite);

        return view('pages.website-editor', [
            'pageTitle' => 'Edit Website - '.($generatedWebsite->site_title ?: 'Generated Website'),
            'generatedWebsite' => $generatedWebsite,
            'business' => $generatedWebsite->business,
            'content' => $content,
            'aiConfigured' => $this->aiService->isConfigured(),
        ]);
    }

    public function update(Request $request, GeneratedWebsite $generatedWebsite): JsonResponse
    {
        $generatedWebsite->load('business');

        if (! $generatedWebsite->isCompleted() || ! $generatedWebsite->business) {
            return response()->json(['ok' => false, 'message' => 'This website cannot be edited.'], 422);
        }

        $validated = $request->validate([
            'site_title' => ['required', 'string', 'max:255'],
            'tagline' => ['nullable', 'string', 'max:500'],
            'theme' => ['required', 'array'],
            'theme.primary_color' => ['required', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'theme.secondary_color' => ['required', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'theme.background_color' => ['required', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'theme.text_color' => ['required', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'sections' => ['required', 'array', 'min:1'],
            'sections.*.type' => ['required', 'string', 'in:hero,about,services,why_choose_us,contact,footer'],
            'sections.*.visible' => ['required', 'boolean'],
            'sections.*.title' => ['nullable', 'string', 'max:160'],
            'sections.*.content' => ['nullable', 'string', 'max:5000'],
            'sections.*.items' => ['nullable', 'array'],
            'sections.*.items.*' => ['nullable'],
            'sections.*.address' => ['nullable', 'string', 'max:500'],
            'sections.*.phone' => ['nullable', 'string', 'max:50'],
            'sections.*.email' => ['nullable', 'string', 'max:255'],
            'sections.*.website_url' => ['nullable', 'string', 'max:500'],
            'sections.*.social_links' => ['nullable', 'array'],
            'sections.*.primary_button_text' => ['nullable', 'string', 'max:60'],
            'sections.*.primary_button_url' => ['nullable', 'string', 'max:500'],
            'sections.*.secondary_button_text' => ['nullable', 'string', 'max:60'],
            'sections.*.secondary_button_url' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $content = $this->normalizeEditorContent($generatedWebsite);
            $content['site_title'] = $this->clean($validated['site_title'], 255);
            $content['tagline'] = $this->clean($validated['tagline'] ?? '', 500);
            $content['theme'] = [
                'primary_color' => strtoupper($validated['theme']['primary_color']),
                'secondary_color' => strtoupper($validated['theme']['secondary_color']),
                'background_color' => strtoupper($validated['theme']['background_color']),
                'text_color' => strtoupper($validated['theme']['text_color']),
            ];

            $existingSections = collect($content['sections'] ?? [])->filter(fn ($s) => is_array($s))->keyBy(fn ($s) => strtolower((string) ($s['type'] ?? '')));
            $incomingSections = collect($validated['sections'])->keyBy(fn ($s) => strtolower((string) $s['type']));
            $orderedTypes = ['hero', 'about', 'services', 'why_choose_us', 'contact', 'footer'];
            $newSections = [];

            foreach ($orderedTypes as $type) {
                $existing = $existingSections->get($type, ['type' => $type]);
                $incoming = $incomingSections->get($type);
                if (! $incoming) {
                    $existing['visible'] = (bool) ($existing['visible'] ?? true);
                    $newSections[] = $this->sanitizeSection($type, $existing, $generatedWebsite);
                    continue;
                }
                $merged = array_merge(is_array($existing) ? $existing : [], $incoming);
                $merged['type'] = $type;
                $newSections[] = $this->sanitizeSection($type, $merged, $generatedWebsite);
            }

            $content['sections'] = $newSections;

            $generatedWebsite->update([
                'site_title' => $content['site_title'],
                'tagline' => $content['tagline'] ?: null,
                'generated_content' => $content,
            ]);

            return response()->json([
                'ok' => true,
                'message' => 'Website changes saved successfully.',
                'content' => $content,
            ]);
        } catch (Throwable $e) {
            Log::error('Website editor save failed', [
                'generated_website_id' => $generatedWebsite->id,
                'exception' => get_class($e),
                'message' => \Illuminate\Support\Str::limit($e->getMessage(), 300, ''),
            ]);

            return response()->json(['ok' => false, 'message' => 'Unable to save changes. Please try again.'], 500);
        }
    }

    public function regenerateSection(Request $request, GeneratedWebsite $generatedWebsite): JsonResponse
    {
        $generatedWebsite->load('business.sourceContents');

        if (! $generatedWebsite->isCompleted() || ! $generatedWebsite->business) {
            return response()->json(['ok' => false, 'message' => 'This website cannot be edited.'], 422);
        }

        $validated = $request->validate([
            'section_type' => ['required', 'string', 'in:hero,about,services,why_choose_us,contact,footer'],
        ]);

        $type = strtolower($validated['section_type']);

        try {
            $content = $this->normalizeEditorContent($generatedWebsite);
            $result = $this->aiService->regenerateSection($generatedWebsite->business, $content, $type);

            if (! ($result['ok'] ?? false)) {
                return response()->json([
                    'ok' => false,
                    'message' => $result['error'] ?? 'Unable to regenerate section.',
                ], 422);
            }

            $section = $this->sanitizeSection($type, $result['section'], $generatedWebsite);
            // Preserve visibility choice and hero button customizations
            $existingSections = collect($content['sections'] ?? [])->filter(fn ($s) => is_array($s));
            $existing = $existingSections->first(fn ($s) => strtolower((string) ($s['type'] ?? '')) === $type);
            if (is_array($existing) && array_key_exists('visible', $existing)) {
                $section['visible'] = (bool) $existing['visible'];
            }
            if ($type === 'hero' && is_array($existing)) {
                foreach (['primary_button_text', 'primary_button_url', 'secondary_button_text', 'secondary_button_url'] as $btnKey) {
                    if (array_key_exists($btnKey, $existing) && ! array_key_exists($btnKey, $result['section'])) {
                        $section[$btnKey] = $existing[$btnKey];
                    }
                }
                $section = $this->sanitizeSection('hero', $section, $generatedWebsite);
            }

            $newSections = [];
            foreach (['hero', 'about', 'services', 'why_choose_us', 'contact', 'footer'] as $orderedType) {
                if ($orderedType === $type) {
                    $newSections[] = $section;
                    continue;
                }
                $found = $existingSections->first(fn ($s) => strtolower((string) ($s['type'] ?? '')) === $orderedType);
                $newSections[] = $this->sanitizeSection($orderedType, is_array($found) ? $found : ['type' => $orderedType], $generatedWebsite);
            }
            $content['sections'] = $newSections;

            $generatedWebsite->update([
                'generated_content' => $content,
            ]);

            return response()->json([
                'ok' => true,
                'message' => ucfirst(str_replace('_', ' ', $type)).' section regenerated.',
                'section' => $section,
                'content' => $content,
            ]);
        } catch (Throwable $e) {
            Log::error('Website section regeneration failed', [
                'generated_website_id' => $generatedWebsite->id,
                'section_type' => $type,
                'exception' => get_class($e),
                'message' => \Illuminate\Support\Str::limit($e->getMessage(), 300, ''),
            ]);

            return response()->json(['ok' => false, 'message' => 'Unable to regenerate section. Please try again.'], 500);
        }
    }

    public function publish(GeneratedWebsite $generatedWebsite): JsonResponse
    {
        $generatedWebsite->load('business');

        if (! $generatedWebsite->isCompleted() || ! is_array($generatedWebsite->generated_content) || ! $generatedWebsite->business) {
            return response()->json(['ok' => false, 'message' => 'Only completed generated websites with valid content can be published.'], 422);
        }

        $content = $this->normalizeEditorContent($generatedWebsite);
        $sections = collect($content['sections'] ?? [])->filter(fn ($s) => is_array($s) && filled($s['type'] ?? null));
        if ($sections->isEmpty()) {
            return response()->json(['ok' => false, 'message' => 'Website content is incomplete and cannot be published.'], 422);
        }

        $slug = $this->ensureUniqueSlug($generatedWebsite);

        $generatedWebsite->forceFill([
            'slug' => $slug,
            'is_published' => true,
            'published_at' => $generatedWebsite->published_at ?? now(),
        ])->save();

        return response()->json([
            'ok' => true,
            'message' => 'Website published successfully.',
            'is_published' => true,
            'slug' => $generatedWebsite->slug,
            'public_url' => route('public-site.show', $generatedWebsite->slug),
            'published_at' => $generatedWebsite->published_at?->toIso8601String(),
        ]);
    }

    public function unpublish(GeneratedWebsite $generatedWebsite): JsonResponse
    {
        $generatedWebsite->load('business');

        if (! $generatedWebsite->business) {
            return response()->json(['ok' => false, 'message' => 'Website not found.'], 404);
        }

        $generatedWebsite->forceFill([
            'is_published' => false,
        ])->save();

        return response()->json([
            'ok' => true,
            'message' => 'Website unpublished. The public URL is no longer accessible.',
            'is_published' => false,
            'slug' => $generatedWebsite->slug,
            'public_url' => $generatedWebsite->slug ? route('public-site.show', $generatedWebsite->slug) : null,
        ]);
    }

    private function ensureUniqueSlug(GeneratedWebsite $generatedWebsite): string
    {
        $existing = trim((string) $generatedWebsite->slug);
        if ($existing !== '' && $this->isSlugAvailable($existing, $generatedWebsite->id)) {
            return $existing;
        }

        $base = \Illuminate\Support\Str::slug((string) ($generatedWebsite->business?->business_slug ?: $generatedWebsite->site_title ?: 'website'));
        if ($base === '') {
            $base = 'website';
        }
        $base = \Illuminate\Support\Str::limit($base, 240, '');

        $candidate = $base;
        $suffix = 2;
        while (! $this->isSlugAvailable($candidate, $generatedWebsite->id)) {
            $suffixStr = '-'.$suffix;
            $candidate = \Illuminate\Support\Str::limit($base, 255 - strlen($suffixStr), '').$suffixStr;
            $suffix++;
            if ($suffix > 1000) {
                $candidate = $base.'-'.\Illuminate\Support\Str::lower(\Illuminate\Support\Str::random(6));
                if ($this->isSlugAvailable($candidate, $generatedWebsite->id)) {
                    break;
                }
            }
        }

        return $candidate;
    }

    private function isSlugAvailable(string $slug, int $ignoreId): bool
    {
        if (! preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slug)) {
            return false;
        }

        return ! GeneratedWebsite::query()
            ->where('slug', $slug)
            ->where('id', '!=', $ignoreId)
            ->exists();
    }

    /**
     * @return array<string, mixed>
     */
    private function normalizeEditorContent(GeneratedWebsite $generatedWebsite): array
    {
        $content = is_array($generatedWebsite->generated_content) ? $generatedWebsite->generated_content : [];
        $content['site_title'] = $this->clean($content['site_title'] ?? $generatedWebsite->site_title ?? '', 255);
        $content['tagline'] = $this->clean($content['tagline'] ?? $generatedWebsite->tagline ?? '', 500);

        $theme = is_array($content['theme'] ?? null) ? $content['theme'] : [];
        $content['theme'] = [
            'primary_color' => $this->cleanColor($theme['primary_color'] ?? null, '#4F46E5'),
            'secondary_color' => $this->cleanColor($theme['secondary_color'] ?? null, '#0EA5E9'),
            'background_color' => $this->cleanColor($theme['background_color'] ?? null, '#FFFFFF'),
            'text_color' => $this->cleanColor($theme['text_color'] ?? null, '#1F2937'),
        ];

        $existingSections = collect(is_array($content['sections'] ?? null) ? $content['sections'] : [])
            ->filter(fn ($s) => is_array($s))
            ->keyBy(fn ($s) => strtolower((string) ($s['type'] ?? '')));

        $orderedTypes = ['hero', 'about', 'services', 'why_choose_us', 'contact', 'footer'];
        $sections = [];
        foreach ($orderedTypes as $type) {
            $sections[] = $this->sanitizeSection($type, is_array($existingSections->get($type)) ? $existingSections->get($type) : ['type' => $type], $generatedWebsite);
        }
        $content['sections'] = $sections;
        $content['missing_information'] = is_array($content['missing_information'] ?? null) ? $content['missing_information'] : [];
        $content['conflicting_information'] = is_array($content['conflicting_information'] ?? null) ? $content['conflicting_information'] : [];

        return $content;
    }

    /**
     * @param array<string, mixed> $section
     * @return array<string, mixed>
     */
    private function sanitizeSection(string $type, array $section, GeneratedWebsite $generatedWebsite): array
    {
        $business = $generatedWebsite->business;
        $visible = array_key_exists('visible', $section) ? (bool) $section['visible'] : true;
        $title = $this->clean($section['title'] ?? '', 160);
        $content = $this->cleanMultiline($section['content'] ?? '', 5000);

        return match ($type) {
            'hero' => [
                'type' => 'hero',
                'title' => $title !== '' ? $title : ($generatedWebsite->site_title ?: 'Welcome'),
                'content' => $content !== '' ? $content : ($generatedWebsite->tagline ?: 'Welcome to our website.'),
                'primary_button_text' => $this->clean($section['primary_button_text'] ?? ($business?->phone ? 'Call Now' : ''), 60),
                'primary_button_url' => $this->cleanButtonUrl($section['primary_button_url'] ?? ($business?->phone ? 'tel:'.preg_replace('/[^\d+]/', '', $business->phone) : '')),
                'secondary_button_text' => $this->clean($section['secondary_button_text'] ?? ($business?->email ? 'Email Us' : ''), 60),
                'secondary_button_url' => $this->cleanButtonUrl($section['secondary_button_url'] ?? ($business?->email ? 'mailto:'.$business->email : '')),
                'visible' => $visible,
            ],
            'about' => [
                'type' => 'about',
                'title' => $title !== '' ? $title : 'About Us',
                'content' => $content !== '' ? $content : 'Contact the business for more information.',
                'visible' => $visible,
            ],
            'services' => [
                'type' => 'services',
                'title' => $title !== '' ? $title : 'Products & Services',
                'items' => $this->sanitizeServiceItems($section['items'] ?? []),
                'visible' => $visible,
            ],
            'why_choose_us' => [
                'type' => 'why_choose_us',
                'title' => $title !== '' ? $title : 'Why Choose Us',
                'items' => $this->sanitizeStringItems($section['items'] ?? []),
                'visible' => $visible,
            ],
            'contact' => $this->sanitizeContactSection($section, $business, $visible),
            'footer' => [
                'type' => 'footer',
                'content' => $content !== '' ? $this->clean($section['content'] ?? '', 300) : ('© '.date('Y').' '.($business?->business_name ?? 'Business').'. All rights reserved.'),
                'visible' => $visible,
            ],
            default => [
                'type' => $type,
                'title' => $title,
                'content' => $content,
                'visible' => $visible,
            ],
        };
    }

    /**
     * @param array<string, mixed> $section
     * @return array<string, mixed>
     */
    private function sanitizeContactSection(array $section, $business, bool $visible): array
    {
        $allowedSocialKeys = ['instagram', 'facebook', 'linkedin', 'google_business'];
        $social = [];
        $inputSocial = is_array($section['social_links'] ?? null) ? $section['social_links'] : [];
        foreach ($allowedSocialKeys as $key) {
            $value = $inputSocial[$key] ?? ($business?->{$key.'_url'} ?? null);
            $value = is_string($value) ? trim($value) : null;
            if ($value && filter_var($value, FILTER_VALIDATE_URL)) {
                $social[$key] = $value;
            }
        }

        $website = $section['website_url'] ?? $business?->website_url;
        $website = is_string($website) && filter_var(trim($website), FILTER_VALIDATE_URL) ? trim($website) : null;
        $email = $section['email'] ?? $business?->email;
        $email = is_string($email) && filter_var(trim($email), FILTER_VALIDATE_EMAIL) ? trim($email) : null;

        return [
            'type' => 'contact',
            'title' => $this->clean($section['title'] ?? 'Contact Us', 120) ?: 'Contact Us',
            'content' => $this->cleanMultiline($section['content'] ?? '', 1000) ?: 'Get in touch using the contact details below.',
            'address' => $this->clean($section['address'] ?? ($business?->address ?? ''), 500) ?: null,
            'phone' => $this->clean($section['phone'] ?? ($business?->phone ?? ''), 50) ?: null,
            'email' => $email,
            'website_url' => $website,
            'social_links' => $social,
            'visible' => $visible,
        ];
    }

    /**
     * @return array<int, array{name:string,description:string}>
     */
    private function sanitizeServiceItems(mixed $items): array
    {
        if (! is_array($items)) return [];
        $result = [];
        foreach ($items as $item) {
            if (is_string($item)) {
                $name = $this->clean($item, 120);
                if ($name !== '') $result[] = ['name' => $name, 'description' => 'Contact the business for more information.'];
            } elseif (is_array($item)) {
                $name = $this->clean($item['name'] ?? $item['title'] ?? '', 120);
                $desc = $this->cleanMultiline($item['description'] ?? $item['content'] ?? '', 500);
                if ($name !== '') $result[] = ['name' => $name, 'description' => $desc !== '' ? $desc : 'Contact the business for more information.'];
            }
            if (count($result) >= 12) break;
        }
        return $result;
    }

    /**
     * @return array<int, string>
     */
    private function sanitizeStringItems(mixed $items): array
    {
        if (! is_array($items)) return [];
        $result = [];
        foreach ($items as $item) {
            $text = is_array($item) ? $this->clean($item['text'] ?? $item['title'] ?? $item['description'] ?? '', 200) : $this->clean($item, 200);
            if ($text !== '') $result[] = $text;
            if (count($result) >= 10) break;
        }
        return array_values(array_unique($result));
    }

    private function clean(mixed $value, int $max): string
    {
        if (! is_string($value)) $value = is_numeric($value) ? (string) $value : '';
        $value = trim(strip_tags($value));
        $value = preg_replace('/\s+/u', ' ', $value) ?? $value;
        return \Illuminate\Support\Str::limit($value, $max, '');
    }

    private function cleanMultiline(mixed $value, int $max): string
    {
        if (! is_string($value)) $value = is_numeric($value) ? (string) $value : '';
        $value = strip_tags($value);
        $value = preg_replace("/\r\n?/", "\n", $value) ?? $value;
        $value = preg_replace("/[ \t]+/", ' ', $value) ?? $value;
        $value = preg_replace("/\n{3,}/", "\n\n", $value) ?? $value;
        $value = trim($value);
        return \Illuminate\Support\Str::limit($value, $max, '');
    }

    private function cleanColor(mixed $value, string $fallback): string
    {
        if (is_string($value) && preg_match('/^#[0-9A-Fa-f]{6}$/', trim($value))) {
            return strtoupper(trim($value));
        }
        return strtoupper($fallback);
    }

    private function cleanButtonUrl(mixed $value): ?string
    {
        if (! is_string($value)) return null;
        $value = trim($value);
        if ($value === '') return null;
        if (preg_match('/^(https?:\/\/|mailto:|tel:)/i', $value)) {
            return \Illuminate\Support\Str::limit($value, 500, '');
        }
        return null;
    }
}
