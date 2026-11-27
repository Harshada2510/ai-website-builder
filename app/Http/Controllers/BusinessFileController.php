<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\BusinessFile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BusinessFileController extends Controller
{
    /**
     * Display uploaded files and upload form.
     */
    public function index(Request $request): View
    {
        $businesses = Business::query()->orderBy('business_name')->get(['id', 'business_name', 'city']);

        $filesQuery = BusinessFile::query()
            ->with('business:id,business_name')
            ->latest();

        $selectedBusinessId = $request->integer('business_id');
        if ($selectedBusinessId > 0) {
            $filesQuery->where('business_id', $selectedBusinessId);
        }

        $files = $filesQuery->paginate(10)->withQueryString();

        return view('pages.uploaded-files', [
            'pageTitle' => 'Uploaded Files',
            'businesses' => $businesses,
            'files' => $files,
            'selectedBusinessId' => $selectedBusinessId > 0 ? $selectedBusinessId : null,
        ]);
    }

    /**
     * Store uploaded files for a selected business.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'business_id' => ['required', 'integer', 'exists:businesses,id'],
            'files' => ['required', 'array', 'min:1', 'max:10'],
            'files.*' => ['required', 'file', 'max:10240', 'mimetypes:application/pdf,image/jpeg,image/png,image/webp'],
        ], [
            'business_id.required' => 'Please select a business.',
            'business_id.exists' => 'The selected business could not be found.',
            'files.required' => 'Please choose at least one file to upload.',
            'files.max' => 'You can upload a maximum of 10 files at once.',
            'files.*.max' => 'Each file must not exceed 10 MB.',
            'files.*.mimetypes' => 'Only PDF, JPG, JPEG, PNG, and WEBP files are allowed.',
            'files.*.file' => 'Each upload must be a valid file.',
        ]);

        $business = Business::query()->findOrFail($validated['business_id']);
        $uploadedFiles = $request->file('files', []);
        $savedCount = 0;
        $emptyFiles = 0;

        foreach ($uploadedFiles as $uploadedFile) {
            if (! $uploadedFile || ! $uploadedFile->isValid()) {
                continue;
            }

            if ((int) $uploadedFile->getSize() <= 0) {
                $emptyFiles++;
                continue;
            }

            $mimeType = (string) $uploadedFile->getMimeType();
            $fileType = $this->resolveFileType($mimeType);

            if ($fileType === null) {
                continue;
            }

            $extension = $this->extensionForMime($mimeType);
            $storedName = Str::uuid()->toString().'.'.$extension;
            $directory = 'business-files/'.$business->id;
            $filePath = $uploadedFile->storeAs($directory, $storedName, 'local');

            if ($filePath === false) {
                continue;
            }

            $originalName = $this->sanitizeOriginalName($uploadedFile->getClientOriginalName(), $extension);

            BusinessFile::create([
                'business_id' => $business->id,
                'original_name' => $originalName,
                'stored_name' => $storedName,
                'file_path' => $filePath,
                'mime_type' => $mimeType,
                'file_size' => (int) $uploadedFile->getSize(),
                'file_type' => $fileType,
            ]);

            $savedCount++;
        }

        if ($savedCount === 0) {
            $errorMessage = $emptyFiles > 0
                ? 'Empty files are not allowed. Please upload valid files.'
                : 'No valid files were uploaded. Please try again.';

            return back()
                ->withInput($request->only('business_id'))
                ->withErrors(['files' => $errorMessage]);
        }

        $message = $savedCount === 1
            ? '1 file uploaded successfully.'
            : $savedCount.' files uploaded successfully.';

        if ($emptyFiles > 0) {
            $message .= ' '.$emptyFiles.' empty file(s) were skipped.';
        }

        return redirect()
            ->route('files.index', ['business_id' => $business->id])
            ->with('success', $message);
    }

    /**
     * Download a stored file through the application.
     */
    public function download(BusinessFile $file): StreamedResponse
    {
        if (! Storage::disk('local')->exists($file->file_path)) {
            abort(404, 'The requested file could not be found.');
        }

        $downloadName = $this->sanitizeOriginalName($file->original_name, $this->extensionForMime($file->mime_type));

        return Storage::disk('local')->download($file->file_path, $downloadName);
    }

    /**
     * Delete a stored file and its database record.
     */
    public function destroy(BusinessFile $file): RedirectResponse
    {
        $businessId = $file->business_id;

        if (Storage::disk('local')->exists($file->file_path)) {
            Storage::disk('local')->delete($file->file_path);
        }

        $file->delete();

        return redirect()
            ->route('files.index', ['business_id' => $businessId])
            ->with('success', 'File deleted successfully.');
    }

    private function resolveFileType(string $mimeType): ?string
    {
        return match ($mimeType) {
            'application/pdf' => 'document',
            'image/jpeg', 'image/png', 'image/webp' => 'image',
            default => null,
        };
    }

    private function extensionForMime(string $mimeType): string
    {
        return match ($mimeType) {
            'application/pdf' => 'pdf',
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            default => 'bin',
        };
    }

    private function sanitizeOriginalName(string $originalName, string $fallbackExtension): string
    {
        $name = pathinfo($originalName, PATHINFO_FILENAME);
        $name = preg_replace('/[^A-Za-z0-9_\- ]+/', '_', $name) ?? 'file';
        $name = trim($name, '_ -');

        if ($name === '') {
            $name = 'file';
        }

        return Str::limit($name, 180, '').'.'.$fallbackExtension;
    }
}
