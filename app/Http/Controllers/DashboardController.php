<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the main dashboard.
     */
    public function index(): View
    {
        return view('dashboard.index', [
            'pageTitle' => 'Dashboard',
        ]);
    }

    /**
     * Display the create website page.
     */
    public function create(): View
    {
        return view('pages.create-website', [
            'pageTitle' => 'Create Website',
        ]);
    }

    /**
     * Display the my websites page.
     */
    public function websites(): View
    {
        return view('pages.my-websites', [
            'pageTitle' => 'My Websites',
        ]);
    }

    /**
     * Display the business information page.
     */
    public function businessInfo(): View
    {
        $businesses = \App\Models\Business::query()->latest()->get();

        return view('pages.business-information', [
            'pageTitle' => 'Business Information',
            'businesses' => $businesses,
            'businessCategories' => $this->businessCategories(),
        ]);
    }

    /**
     * Display the uploaded files page.
     */
    public function uploadedFiles(): View
    {
        return view('pages.uploaded-files', [
            'pageTitle' => 'Uploaded Files',
        ]);
    }

    /**
     * Display the AI generator page.
     */
    public function aiGenerator(): View
    {
        return view('pages.ai-generator', [
            'pageTitle' => 'AI Generator',
        ]);
    }

    /**
     * Display the website editor selection page.
     */
    public function websiteEditor(): View
    {
        $generatedWebsites = \App\Models\GeneratedWebsite::query()
            ->with('business')
            ->where('generation_status', 'completed')
            ->latest()
            ->get();

        return view('pages.website-editor-index', [
            'pageTitle' => 'Website Editor',
            'generatedWebsites' => $generatedWebsites,
        ]);
    }

    /**
     * Display the settings page.
     */
    public function settings(): View
    {
        $settings = \App\Models\AppSetting::current();

        return view('pages.settings', [
            'pageTitle' => 'Settings',
            'settings' => $settings,
            'aiConfigured' => filled(config('services.ai_website_generator.key')),
            'aiModel' => (string) config('services.ai_website_generator.model', ''),
            'aiBaseUrl' => (string) config('services.ai_website_generator.base_url', ''),
            'aiTimeout' => (int) config('services.ai_website_generator.timeout', 60),
        ]);
    }

    /**
     * @return list<string>
     */
    private function businessCategories(): array
    {
        return [
            'Restaurant / Cafe',
            'Retail / Shop',
            'Professional Services',
            'Healthcare / Clinic',
            'Education / Coaching',
            'Real Estate',
            'Beauty / Salon',
            'Fitness / Gym',
            'Technology / IT',
            'Construction / Home Services',
            'Travel / Hospitality',
            'Portfolio / Personal Brand',
            'Other',
        ];
    }
}
