<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Services\BusinessExtractionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\View\View;

class BusinessController extends Controller
{
    /**
     * Show the create website/business form.
     */
    public function create(): View
    {
        return view('pages.create-website', [
            'pageTitle' => 'Create Website',
            'businessCategories' => $this->businessCategories(),
        ]);
    }

    /**
     * Store a new business in the database.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'business_name' => ['required', 'string', 'max:255'],
            'business_category' => ['nullable', 'string', 'max:100'],
            'city' => ['required', 'string', 'max:150'],
            'address' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:3000'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'website_url' => ['nullable', 'url', 'max:255'],
            'instagram_url' => ['nullable', 'url', 'max:255'],
            'facebook_url' => ['nullable', 'url', 'max:255'],
            'linkedin_url' => ['nullable', 'url', 'max:255'],
            'google_business_url' => ['nullable', 'url', 'max:255'],
            'confirm_duplicate' => ['nullable', 'boolean'],
        ], [
            'business_name.required' => 'Business name is required.',
            'city.required' => 'City is required.',
            'email.email' => 'Please enter a valid email address.',
            '*.url' => 'Please enter a valid URL (including http:// or https://).',
        ]);

        $confirmDuplicate = $request->boolean('confirm_duplicate');

        $possibleDuplicates = Business::query()
            ->whereRaw('LOWER(business_name) = ?', [Str::lower($validated['business_name'])])
            ->get(['id', 'business_name', 'city']);

        if ($possibleDuplicates->isNotEmpty() && ! $confirmDuplicate) {
            $duplicateList = $possibleDuplicates
                ->map(fn (Business $business) => $business->business_name.' ('.$business->city.')')
                ->implode(', ');

            return back()
                ->withInput($request->except('confirm_duplicate'))
                ->with('duplicate_warning', 'A business with a similar name already exists: '.$duplicateList.'. If this is a different business, please confirm below to save it as a new record.');
        }

        $validated['business_slug'] = $this->generateUniqueSlug($validated['business_name']);
        $validated['status'] = 'draft';
        unset($validated['confirm_duplicate']);

        $business = Business::create($validated);

        return redirect()
            ->route('businesses.show', $business->business_slug)
            ->with('success', 'Business information saved successfully.');
    }

    /**
     * Display saved businesses.
     */
    public function index(): View
    {
        $businesses = Business::query()
            ->latest()
            ->get();

        return view('pages.my-websites', [
            'pageTitle' => 'My Websites',
            'businesses' => $businesses,
        ]);
    }

    /**
     * Display a single business details page.
     */
    public function show(Business $business): View
    {
        $business->load([
            'files' => function ($query) {
                $query->latest();
            },
            'sourceContents' => function ($query) {
                $query->latest();
            },
            'generatedWebsites' => function ($query) {
                $query->latest();
            },
        ]);

        $extractionAnalysis = app(BusinessExtractionService::class)->analyzeConflicts($business);

        return view('pages.business-details', [
            'pageTitle' => $business->business_name,
            'business' => $business,
            'extractionAnalysis' => $extractionAnalysis,
        ]);
    }

    /**
     * Update an existing business.
     */
    public function update(Request $request, Business $business): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'business_name' => ['required', 'string', 'max:255'],
            'business_category' => ['nullable', 'string', 'max:100'],
            'city' => ['required', 'string', 'max:150'],
            'address' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:3000'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'website_url' => ['nullable', 'url', 'max:255'],
            'instagram_url' => ['nullable', 'url', 'max:255'],
            'facebook_url' => ['nullable', 'url', 'max:255'],
            'linkedin_url' => ['nullable', 'url', 'max:255'],
            'google_business_url' => ['nullable', 'url', 'max:255'],
        ], [
            'business_name.required' => 'Business name is required.',
            'city.required' => 'City is required.',
            'email.email' => 'Please enter a valid email address.',
            '*.url' => 'Please enter a valid URL (including http:// or https://).',
        ]);

        if ($validator->fails()) {
            return redirect()
                ->route('business-information')
                ->withErrors($validator, 'business_'.$business->id)
                ->withInput()
                ->with('edit_business_id', $business->id);
        }

        $validated = $validator->validated();
        $business->fill($validated);
        $business->save();

        return redirect()
            ->route('business-information')
            ->with('success', 'Business information updated successfully.')
            ->with('edit_business_id', $business->id);
    }

    /**
     * Generate a unique slug for the business.
     */
    private function generateUniqueSlug(string $name): string
    {
        $baseSlug = Str::slug($name);
        if ($baseSlug === '') {
            $baseSlug = 'business';
        }

        $slug = $baseSlug;
        $counter = 2;

        while (Business::query()->where('business_slug', $slug)->exists()) {
            $slug = $baseSlug.'-'.$counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Common business categories for the form.
     *
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
