<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\BusinessFile;
use App\Models\BusinessSourceContent;
use App\Services\BusinessExtractionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class BusinessExtractionController extends Controller
{
    public function __construct(private readonly BusinessExtractionService $extractionService)
    {
    }

    public function extractPdf(BusinessFile $file): RedirectResponse
    {
        $this->extractionService->extractPdf($file, true);

        return redirect()
            ->route('businesses.show', $file->business->business_slug)
            ->with('success', 'PDF extraction completed. Review the extracted information below.');
    }

    public function extractWebsite(Business $business): RedirectResponse
    {
        $this->extractionService->extractWebsite($business, true);

        return redirect()
            ->route('businesses.show', $business->business_slug)
            ->with('success', 'Website extraction completed. Review the extracted information below.');
    }

    public function extractSocial(Business $business, string $type): RedirectResponse
    {
        $allowed = ['instagram', 'facebook', 'linkedin', 'google_business'];
        if (! in_array($type, $allowed, true)) {
            abort(404);
        }

        $url = match ($type) {
            'instagram' => $business->instagram_url,
            'facebook' => $business->facebook_url,
            'linkedin' => $business->linkedin_url,
            'google_business' => $business->google_business_url,
            default => null,
        };

        $this->extractionService->extractSocialSource($business, $type, $url, true);

        return redirect()
            ->route('businesses.show', $business->business_slug)
            ->with('success', 'Social source was processed. Please review its status below.');
    }

    public function show(BusinessSourceContent $sourceContent): View
    {
        $sourceContent->load('business', 'businessFile');

        return view('pages.extraction-details', [
            'pageTitle' => 'Extracted Source',
            'sourceContent' => $sourceContent,
            'business' => $sourceContent->business,
        ]);
    }
}
