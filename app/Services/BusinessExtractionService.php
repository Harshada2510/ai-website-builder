<?php

namespace App\Services;

use App\Models\Business;
use App\Models\BusinessFile;
use App\Models\BusinessSourceContent;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Smalot\PdfParser\Parser;
use Throwable;

class BusinessExtractionService
{
    private const MAX_PDF_TEXT_LENGTH = 120000;
    private const MAX_WEB_TEXT_LENGTH = 80000;
    private const MAX_WEB_RESPONSE_BYTES = 1048576; // 1MB
    private const WEB_TIMEOUT_SECONDS = 8;

    public function extractPdf(BusinessFile $file, bool $force = false): BusinessSourceContent
    {
        $existing = BusinessSourceContent::query()
            ->where('business_id', $file->business_id)
            ->where('business_file_id', $file->id)
            ->where('source_type', 'pdf')
            ->latest()
            ->first();

        if ($existing && ! $force && in_array($existing->extraction_status, ['completed', 'needs_review'], true)) {
            return $existing;
        }

        $record = $existing ?: new BusinessSourceContent([
            'business_id' => $file->business_id,
            'business_file_id' => $file->id,
            'source_type' => 'pdf',
        ]);

        $record->source_title = $file->original_name;
        $record->source_url = null;
        $record->extraction_status = 'pending';
        $record->error_message = null;
        $record->extracted_text = null;
        $record->extracted_at = null;

        try {
            if ($file->mime_type !== 'application/pdf') {
                return $this->fail($record, 'Only PDF files can be extracted in this phase.');
            }

            if (! Storage::disk('local')->exists($file->file_path)) {
                return $this->fail($record, 'The uploaded PDF file could not be found in storage.');
            }

            $absolutePath = Storage::disk('local')->path($file->file_path);
            $fileSize = (int) Storage::disk('local')->size($file->file_path);
            if ($fileSize > 10 * 1024 * 1024) {
                return $this->fail($record, 'The PDF file is too large to process.');
            }

            $parser = new Parser();
            $pdf = $parser->parseFile($absolutePath);
            $pages = $pdf->getPages();
            $pageTexts = [];

            foreach ($pages as $index => $page) {
                $text = $this->normalizeText((string) $page->getText());
                if ($text !== '') {
                    $pageTexts[] = "----- Page ".($index + 1)." -----\n".$text;
                }
            }

            $text = trim(implode("\n\n", $pageTexts));
            $text = Str::limit($text, self::MAX_PDF_TEXT_LENGTH, "\n\n[Text truncated due to size limit]");

            if ($text === '') {
                $record->extraction_status = 'needs_review';
                $record->error_message = 'No readable text was found. This may be a scanned or image-only PDF. OCR is not implemented in this phase.';
                $record->extracted_text = null;
                $record->extracted_at = now();
                $record->save();
                return $record;
            }

            $record->extraction_status = 'completed';
            $record->extracted_text = $text;
            $record->error_message = null;
            $record->extracted_at = now();
            $record->save();
            return $record;
        } catch (Throwable $e) {
            return $this->fail($record, 'Unable to extract text from this PDF. The file may be corrupted or unreadable.');
        }
    }

    public function extractWebsite(Business $business, bool $force = false): BusinessSourceContent
    {
        $url = trim((string) $business->website_url);
        $existing = BusinessSourceContent::query()
            ->where('business_id', $business->id)
            ->where('source_type', 'website')
            ->latest()
            ->first();

        if ($url === '') {
            $record = $existing ?: new BusinessSourceContent([
                'business_id' => $business->id,
                'source_type' => 'website',
            ]);
            return $this->fail($record, 'No business website URL is saved for this business.', 'Business Website');
        }

        if ($existing && ! $force && $existing->source_url === $url && in_array($existing->extraction_status, ['completed', 'needs_review'], true)) {
            return $existing;
        }

        $record = $existing ?: new BusinessSourceContent([
            'business_id' => $business->id,
            'source_type' => 'website',
        ]);
        $record->source_url = $url;
        $record->source_title = 'Business Website';
        $record->extraction_status = 'pending';
        $record->error_message = null;
        $record->extracted_text = null;
        $record->extracted_at = null;

        $safeUrl = $this->validatePublicUrl($url);
        if ($safeUrl === null) {
            return $this->fail($record, 'The website URL is invalid or not allowed. Only public http/https URLs are permitted.', 'Business Website');
        }

        try {
            $response = Http::timeout(self::WEB_TIMEOUT_SECONDS)
                ->withOptions([
                    'allow_redirects' => [
                        'max' => 3,
                        'strict' => true,
                        'protocols' => ['http', 'https'],
                    ],
                ])
                ->withHeaders([
                    'User-Agent' => 'AIWebsiteBuilderBot/1.0 (+business-info-extraction)',
                    'Accept' => 'text/html,application/xhtml+xml,text/plain;q=0.9,*/*;q=0.8',
                ])
                ->get($safeUrl);

            if (! $response->successful()) {
                return $this->fail($record, 'The website could not be fetched (HTTP '.$response->status().'). It may block automated access or be unavailable.', 'Business Website');
            }

            $body = (string) $response->body();
            if (strlen($body) > self::MAX_WEB_RESPONSE_BYTES) {
                $body = substr($body, 0, self::MAX_WEB_RESPONSE_BYTES);
            }

            $contentType = strtolower((string) $response->header('Content-Type'));
            if ($contentType !== '' && ! str_contains($contentType, 'text/html') && ! str_contains($contentType, 'text/plain') && ! str_contains($contentType, 'application/xhtml+xml')) {
                return $this->fail($record, 'The website did not return readable HTML/text content.', 'Business Website');
            }

            $text = $this->extractTextFromHtml($body);
            $text = Str::limit($text, self::MAX_WEB_TEXT_LENGTH, "\n\n[Text truncated due to size limit]");

            if ($text === '') {
                $record->extraction_status = 'needs_review';
                $record->error_message = 'No readable text was found on the website.';
                $record->extracted_text = null;
                $record->extracted_at = now();
                $record->save();
                return $record;
            }

            $record->source_url = $safeUrl;
            $record->source_title = $this->extractHtmlTitle($body) ?: 'Business Website';
            $record->extraction_status = 'completed';
            $record->extracted_text = $text;
            $record->error_message = null;
            $record->extracted_at = now();
            $record->save();
            return $record;
        } catch (Throwable $e) {
            return $this->fail($record, 'Unable to extract information from the website. The site may be unreachable, slow, or blocking automated access.', 'Business Website');
        }
    }

    public function extractSocialSource(Business $business, string $type, ?string $url, bool $force = false): BusinessSourceContent
    {
        $type = strtolower($type);
        $allowed = ['instagram', 'facebook', 'linkedin', 'google_business'];
        if (! in_array($type, $allowed, true)) {
            throw new \InvalidArgumentException('Unsupported social source type.');
        }

        $url = trim((string) $url);
        $existing = BusinessSourceContent::query()
            ->where('business_id', $business->id)
            ->where('source_type', $type)
            ->latest()
            ->first();

        $record = $existing ?: new BusinessSourceContent([
            'business_id' => $business->id,
            'source_type' => $type,
        ]);
        $record->source_title = Str::of($type)->replace('_', ' ')->title()->toString().' URL';
        $record->source_url = $url !== '' ? $url : null;

        if ($url === '') {
            $record->extraction_status = 'needs_review';
            $record->error_message = 'No URL saved for this social source.';
            $record->extracted_text = null;
            $record->extracted_at = now();
            $record->save();
            return $record;
        }

        if ($existing && ! $force && $existing->source_url === $url && $existing->extraction_status === 'needs_review') {
            return $existing;
        }

        // Do not bypass login/CAPTCHA/platform restrictions. Mark for manual review.
        $record->extraction_status = 'needs_review';
        $record->error_message = 'Automated extraction is not performed for this platform. Please review manually or provide a PDF/screenshot/manual details.';
        $record->extracted_text = 'Source URL saved for manual review: '.$url;
        $record->extracted_at = now();
        $record->save();
        return $record;
    }

    public function analyzeConflicts(Business $business): array
    {
        $business->loadMissing('sourceContents');
        $completed = $business->sourceContents->where('extraction_status', 'completed');

        $missing = [];
        $checks = [
            'Phone number' => fn () => filled($business->phone) || $this->textContainsAny($completed, ['phone', 'tel', '+', 'call']),
            'Email' => fn () => filled($business->email) || $this->textContainsAny($completed, ['email', '@']),
            'Address' => fn () => filled($business->address) || $this->textContainsAny($completed, ['address', 'location', 'street', 'road']),
            'Business description' => fn () => filled($business->description) || $this->textContainsAny($completed, ['about', 'we are', 'our company', 'services']),
            'Opening hours' => fn () => $this->textContainsAny($completed, ['open', 'hours', 'mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun']),
            'Products/services' => fn () => $this->textContainsAny($completed, ['service', 'services', 'product', 'products', 'offer', 'menu']),
            'Price information' => fn () => $this->textContainsAny($completed, ['price', 'pricing', 'cost', '$', '₹', 'rs', 'inr']),
        ];
        foreach ($checks as $label => $check) {
            if (! $check()) {
                $missing[] = $label;
            }
        }

        $conflicts = [];
        $this->detectEmailConflicts($business, $completed, $conflicts);
        $this->detectPhoneConflicts($business, $completed, $conflicts);

        return [
            'missing' => $missing,
            'conflicts' => $conflicts,
        ];
    }

    private function detectEmailConflicts(Business $business, $completed, array &$conflicts): void
    {
        if (blank($business->email)) {
            return;
        }
        $formEmail = strtolower(trim($business->email));
        foreach ($completed as $source) {
            preg_match_all('/[A-Z0-9._%+\-]+@[A-Z0-9.\-]+\.[A-Z]{2,}/i', (string) $source->extracted_text, $matches);
            foreach (array_unique($matches[0] ?? []) as $found) {
                $foundNorm = strtolower(trim($found));
                if ($foundNorm !== '' && $foundNorm !== $formEmail) {
                    $conflicts[] = [
                        'field' => 'Email',
                        'business_value' => $business->email,
                        'extracted_value' => $found,
                        'source' => $source->source_title ?: ucfirst($source->source_type),
                        'source_type' => $source->source_type,
                    ];
                }
            }
        }
    }

    private function detectPhoneConflicts(Business $business, $completed, array &$conflicts): void
    {
        if (blank($business->phone)) {
            return;
        }
        $formDigits = preg_replace('/\D+/', '', (string) $business->phone) ?: '';
        if (strlen($formDigits) < 7) {
            return;
        }
        foreach ($completed as $source) {
            preg_match_all('/(?:(?:\+?\d{1,3}[\s\-\.]?)?(?:\(?\d{2,4}\)?[\s\-\.]?){2,4}\d{2,4})/', (string) $source->extracted_text, $matches);
            foreach (array_unique($matches[0] ?? []) as $found) {
                $digits = preg_replace('/\D+/', '', $found) ?: '';
                if (strlen($digits) < 7) {
                    continue;
                }
                if ($digits !== $formDigits && ! str_ends_with($digits, $formDigits) && ! str_ends_with($formDigits, $digits)) {
                    $conflicts[] = [
                        'field' => 'Phone number',
                        'business_value' => $business->phone,
                        'extracted_value' => trim($found),
                        'source' => $source->source_title ?: ucfirst($source->source_type),
                        'source_type' => $source->source_type,
                    ];
                }
            }
        }
    }

    private function textContainsAny($sources, array $needles): bool
    {
        foreach ($sources as $source) {
            $text = Str::lower((string) $source->extracted_text);
            foreach ($needles as $needle) {
                if (str_contains($text, Str::lower($needle))) {
                    return true;
                }
            }
        }
        return false;
    }

    private function fail(BusinessSourceContent $record, string $message, ?string $title = null): BusinessSourceContent
    {
        if ($title !== null) {
            $record->source_title = $title;
        }
        $record->extraction_status = 'failed';
        $record->error_message = $message;
        $record->extracted_text = null;
        $record->extracted_at = now();
        $record->save();
        return $record;
    }

    private function normalizeText(string $text): string
    {
        $text = str_replace(["\r\n", "\r"], "\n", $text);
        $text = preg_replace("/[ \t]+/", ' ', $text) ?? $text;
        $text = preg_replace("/\n{3,}/", "\n\n", $text) ?? $text;
        $text = trim($text);
        return $text;
    }

    private function extractTextFromHtml(string $html): string
    {
        $html = preg_replace('/<(script|style|noscript|svg|canvas|iframe|form)[^>]*>.*?<\/\1>/is', ' ', $html) ?? $html;
        $html = preg_replace('/<(nav|footer|header|aside)[^>]*>.*?<\/\1>/is', ' ', $html) ?? $html;
        $html = preg_replace('/<!--.*?-->/s', ' ', $html) ?? $html;
        $text = strip_tags($html);
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        return $this->normalizeText($text);
    }

    private function extractHtmlTitle(string $html): ?string
    {
        if (preg_match('/<title[^>]*>(.*?)<\/title>/is', $html, $matches)) {
            $title = $this->normalizeText(html_entity_decode(strip_tags($matches[1]), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
            return $title !== '' ? Str::limit($title, 120, '') : null;
        }
        return null;
    }

    private function validatePublicUrl(string $url): ?string
    {
        $url = trim($url);
        $parts = parse_url($url);
        if (! is_array($parts)) {
            return null;
        }

        $scheme = strtolower((string) ($parts['scheme'] ?? ''));
        $host = strtolower((string) ($parts['host'] ?? ''));
        if (! in_array($scheme, ['http', 'https'], true) || $host === '') {
            return null;
        }
        if (isset($parts['user']) || isset($parts['pass'])) {
            return null;
        }
        if ($host === 'localhost' || str_ends_with($host, '.localhost') || str_ends_with($host, '.local')) {
            return null;
        }

        // Resolve host and block private/reserved IPs
        $ips = $this->resolveHostIps($host);
        if (empty($ips)) {
            return null;
        }
        foreach ($ips as $ip) {
            if (! $this->isPublicIp($ip)) {
                return null;
            }
        }

        return $url;
    }

    private function resolveHostIps(string $host): array
    {
        $ips = [];
        if (filter_var($host, FILTER_VALIDATE_IP)) {
            return [$host];
        }

        $aRecords = @dns_get_record($host, DNS_A) ?: [];
        foreach ($aRecords as $record) {
            if (! empty($record['ip'])) {
                $ips[] = $record['ip'];
            }
        }
        $aaaaRecords = @dns_get_record($host, DNS_AAAA) ?: [];
        foreach ($aaaaRecords as $record) {
            if (! empty($record['ipv6'])) {
                $ips[] = $record['ipv6'];
            }
        }

        return array_values(array_unique($ips));
    }

    private function isPublicIp(string $ip): bool
    {
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false;
        }

        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            // Block loopback, link-local, unique local, mapped/reserved ranges.
            $lower = strtolower($ip);
            if ($lower === '::1') return false;
            if (str_starts_with($lower, 'fe80:')) return false; // link-local
            if (str_starts_with($lower, 'fc') || str_starts_with($lower, 'fd')) return false; // ULA
            if (str_starts_with($lower, '::ffff:')) {
                $mapped = substr($lower, 7);
                return $this->isPublicIp($mapped);
            }
            return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false;
        }

        return false;
    }
}
