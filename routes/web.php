<?php

use App\Http\Controllers\BusinessController;
use App\Http\Controllers\BusinessExtractionController;
use App\Http\Controllers\BusinessFileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PublicWebsiteController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\WebsiteEditorController;
use App\Http\Controllers\WebsiteGenerationController;
use Illuminate\Support\Facades\Route;

Route::get('/health', \App\Http\Controllers\HealthController::class)->name('health');

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
Route::redirect('/dashboard', '/', 301);

Route::get('/create-website', [BusinessController::class, 'create'])->name('create-website');
Route::post('/businesses', [BusinessController::class, 'store'])->name('businesses.store');
Route::get('/my-websites', [BusinessController::class, 'index'])->name('my-websites');
Route::get('/businesses/{business:business_slug}', [BusinessController::class, 'show'])->name('businesses.show');
Route::put('/businesses/{business:business_slug}', [BusinessController::class, 'update'])->name('businesses.update');

Route::get('/business-information', [DashboardController::class, 'businessInfo'])->name('business-information');
Route::get('/uploaded-files', [BusinessFileController::class, 'index'])->name('uploaded-files');
Route::get('/files', [BusinessFileController::class, 'index'])->name('files.index');
Route::post('/files', [BusinessFileController::class, 'store'])->name('files.store');
Route::get('/files/{file}/download', [BusinessFileController::class, 'download'])->name('files.download');
Route::delete('/files/{file}', [BusinessFileController::class, 'destroy'])->name('files.destroy');

Route::post('/files/{file}/extract', [BusinessExtractionController::class, 'extractPdf'])->name('files.extract');
Route::post('/businesses/{business:business_slug}/extract-website', [BusinessExtractionController::class, 'extractWebsite'])->name('businesses.extract-website');
Route::post('/businesses/{business:business_slug}/extract-social/{type}', [BusinessExtractionController::class, 'extractSocial'])->name('businesses.extract-social');
Route::get('/source-contents/{sourceContent}', [BusinessExtractionController::class, 'show'])->name('source-contents.show');

Route::get('/ai-generator', [WebsiteGenerationController::class, 'index'])->name('ai-generator');
Route::get('/businesses/{business:business_slug}/generate-website', [WebsiteGenerationController::class, 'create'])->name('businesses.generate');
Route::post('/businesses/{business:business_slug}/generate-website', [WebsiteGenerationController::class, 'store'])->name('businesses.generate.store');
Route::get('/generated-websites/{generatedWebsite}/preview', [WebsiteGenerationController::class, 'preview'])->name('generated-websites.preview');
Route::get('/generated-websites/{generatedWebsite}/edit', [WebsiteEditorController::class, 'edit'])->name('generated-websites.edit');
Route::put('/generated-websites/{generatedWebsite}', [WebsiteEditorController::class, 'update'])->name('generated-websites.update');
Route::post('/generated-websites/{generatedWebsite}/regenerate-section', [WebsiteEditorController::class, 'regenerateSection'])->name('generated-websites.regenerate-section');
Route::post('/generated-websites/{generatedWebsite}/publish', [WebsiteEditorController::class, 'publish'])->name('generated-websites.publish');
Route::post('/generated-websites/{generatedWebsite}/unpublish', [WebsiteEditorController::class, 'unpublish'])->name('generated-websites.unpublish');

Route::get('/website-editor', [DashboardController::class, 'websiteEditor'])->name('website-editor');
Route::get('/settings', [DashboardController::class, 'settings'])->name('settings');
Route::put('/settings', [SettingsController::class, 'update'])->name('settings.update');

Route::get('/site/{slug}', [PublicWebsiteController::class, 'show'])->name('public-site.show');
