<?php

namespace App\Http\Controllers;

use App\Models\GeneratedWebsite;
use Illuminate\View\View;

class PublicWebsiteController extends Controller
{
    public function show(string $slug): View
    {
        $slug = trim($slug);
        if ($slug === '' || ! preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slug)) {
            abort(404);
        }

        $generatedWebsite = GeneratedWebsite::query()
            ->with('business')
            ->where('slug', $slug)
            ->where('is_published', true)
            ->where('generation_status', 'completed')
            ->firstOrFail();

        $content = is_array($generatedWebsite->generated_content) ? $generatedWebsite->generated_content : [];
        $sections = collect($content['sections'] ?? [])->filter(fn ($s) => is_array($s));
        if ($sections->isEmpty()) {
            abort(404);
        }

        return view('pages.public-website', [
            'generatedWebsite' => $generatedWebsite,
            'business' => $generatedWebsite->business,
            'content' => $content,
        ]);
    }
}
