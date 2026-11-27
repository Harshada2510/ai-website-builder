<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\GeneratedWebsite;
use App\Services\AIWebsiteGenerationService;
use App\Services\BusinessExtractionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Throwable;

class WebsiteGenerationController extends Controller
{
    public function __construct(
        private readonly AIWebsiteGenerationService $aiService,
        private readonly BusinessExtractionService $extractionService,
    ) {
    }

    public function index(): View
    {
        $businesses = Business::query()
            ->withCount('generatedWebsites')
            ->with(['generatedWebsites' => function ($query) {
                $query->latest()->limit(1);
            }])
            ->latest()
            ->get();

        return view('pages.ai-generator', [
            'pageTitle' => 'AI Generator',
            'businesses' => $businesses,
            'aiConfigured' => $this->aiService->isConfigured(),
        ]);
    }

    public function create(Business $business): View
    {
        $business->load([
            'sourceContents' => function ($query) {
                $query->latest();
            },
            'generatedWebsites' => function ($query) {
                $query->latest();
            },
        ]);

        $analysis = $this->extractionService->analyzeConflicts($business);
        $usableSources = $this->aiService->usableSources($business);

        return view('pages.generate-website', [
            'pageTitle' => 'Generate Website - '.$business->business_name,
            'business' => $business,
            'analysis' => $analysis,
            'usableSources' => $usableSources,
            'aiConfigured' => $this->aiService->isConfigured(),
        ]);
    }

    public function store(Request $request, Business $business): RedirectResponse
    {
        if (function_exists('set_time_limit')) {
            @set_time_limit(180);
        }

        try {
            $business->load(['sourceContents' => function ($query) {
                $query->latest();
            }]);

            $result = $this->aiService->generate($business);

            if (! $result['ok']) {
                GeneratedWebsite::create([
                    'business_id' => $business->id,
                    'site_title' => $business->business_name,
                    'tagline' => null,
                    'generated_content' => null,
                    'generation_status' => 'failed',
                    'ai_model' => $result['model'] ?? config('services.ai_website_generator.model'),
                    'error_message' => $result['error'] ?? 'Website generation failed.',
                ]);

                return redirect()
                    ->route('businesses.generate', $business->business_slug)
                    ->with('error', $result['error'] ?? 'Website generation failed. Please try again.');
            }

            $data = $result['data'];

            $generatedWebsite = GeneratedWebsite::create([
                'business_id' => $business->id,
                'site_title' => $data['site_title'] ?? $business->business_name,
                'tagline' => $data['tagline'] ?? null,
                'generated_content' => $data,
                'generation_status' => 'completed',
                'ai_model' => $result['model'] ?? config('services.ai_website_generator.model'),
                'error_message' => null,
            ]);

            return redirect()
                ->route('generated-websites.preview', $generatedWebsite)
                ->with('success', 'Website generated successfully. Review the preview below.');
        } catch (Throwable $e) {
            Log::error('AI website generation controller failure', [
                'business_id' => $business->id,
                'business_slug' => $business->business_slug,
                'exception' => get_class($e),
                'code' => $e->getCode(),
                'message' => \Illuminate\Support\Str::limit($e->getMessage(), 500, ''),
            ]);

            GeneratedWebsite::create([
                'business_id' => $business->id,
                'site_title' => $business->business_name,
                'tagline' => null,
                'generated_content' => null,
                'generation_status' => 'failed',
                'ai_model' => config('services.ai_website_generator.model'),
                'error_message' => 'An unexpected error occurred while generating the website. Please try again.',
            ]);

            return redirect()
                ->route('businesses.generate', $business->business_slug)
                ->with('error', 'An unexpected error occurred while generating the website. Please try again.');
        }
    }

    public function preview(GeneratedWebsite $generatedWebsite): View
    {
        $generatedWebsite->load('business');

        return view('pages.generated-website-preview', [
            'pageTitle' => 'Website Preview - '.($generatedWebsite->site_title ?: 'Generated Website'),
            'generatedWebsite' => $generatedWebsite,
            'business' => $generatedWebsite->business,
            'content' => is_array($generatedWebsite->generated_content) ? $generatedWebsite->generated_content : [],
        ]);
    }
}