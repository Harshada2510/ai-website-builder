<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $generatedWebsite->site_title ?: ($business?->business_name ?? 'Website') }}</title>
    <meta name="description" content="{{ \Illuminate\Support\Str::limit(strip_tags((string) ($generatedWebsite->tagline ?: ($business?->description ?? ''))), 160, '') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; margin: 0; }
        .generated-preview a { word-break: break-word; }
    </style>
</head>
<body>
    @include('pages.partials.generated-site-sections', ['publicView' => true])
</body>
</html>
