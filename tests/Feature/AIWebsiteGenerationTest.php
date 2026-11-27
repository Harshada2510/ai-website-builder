<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\GeneratedWebsite;
use App\Services\AIWebsiteGenerationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class AIWebsiteGenerationTest extends TestCase
{
    use RefreshDatabase;

    private function makeBusiness(array $overrides = []): Business
    {
        return Business::create(array_merge([
            'business_name' => 'Sunrise Cafe',
            'business_slug' => 'sunrise-cafe',
            'business_category' => 'Cafe',
            'city' => 'Pune',
            'address' => '123 Main Street',
            'description' => 'A cozy cafe.',
            'phone' => '+91 9999999999',
            'email' => 'hello@sunrise.test',
            'status' => 'draft',
        ], $overrides));
    }

    private function validPayload(): array
    {
        return [
            'site_title' => 'Sunrise Cafe',
            'tagline' => 'Fresh coffee in Pune',
            'theme' => ['primary_color' => '#112233', 'secondary_color' => '#445566'],
            'sections' => [
                ['type' => 'hero', 'title' => 'Sunrise Cafe', 'content' => 'Welcome', 'visible' => true],
                ['type' => 'about', 'title' => 'About Us', 'content' => 'A cozy cafe.', 'visible' => true],
                ['type' => 'services', 'title' => 'Services', 'items' => [['name' => 'Coffee', 'description' => 'Fresh coffee.']], 'visible' => true],
                ['type' => 'why_choose_us', 'title' => 'Why Choose Us', 'items' => ['Fresh ingredients'], 'visible' => true],
                ['type' => 'contact', 'title' => 'Contact', 'content' => 'Call us.', 'address' => '123 Main Street', 'phone' => '+91 9999999999', 'email' => 'hello@sunrise.test', 'website_url' => null, 'social_links' => new \stdClass(), 'visible' => true],
                ['type' => 'footer', 'content' => 'Footer', 'visible' => true],
            ],
            'missing_information' => [],
            'conflicting_information' => [],
        ];
    }

    private function configureAi(): void
    {
        Config::set('services.ai_website_generator.key', 'test-secret-key');
        Config::set('services.ai_website_generator.model', 'gemini-2.5-flash');
        Config::set('services.ai_website_generator.base_url', 'https://generativelanguage.googleapis.com/v1beta/openai');
        Config::set('services.ai_website_generator.timeout', 60);
    }

    public function test_successful_gemini_compatible_json_response_is_saved(): void
    {
        $this->configureAi();
        $business = $this->makeBusiness();

        Http::fake([
            'https://generativelanguage.googleapis.com/v1beta/openai/chat/completions' => Http::response([
                'choices' => [['message' => ['content' => json_encode($this->validPayload())]]],
            ], 200),
        ]);

        $response = $this->post(route('businesses.generate.store', $business->business_slug));

        $response->assertRedirect();
        $this->assertDatabaseHas('generated_websites', [
            'business_id' => $business->id,
            'generation_status' => 'completed',
            'ai_model' => 'gemini-2.5-flash',
        ]);
        $generated = GeneratedWebsite::where('business_id', $business->id)->first();
        $this->assertNotNull($generated);
        $this->assertSame('Sunrise Cafe', $generated->site_title);
        $this->assertIsArray($generated->generated_content);
    }

    public function test_invalid_api_key_response_is_handled_and_saved_as_failed(): void
    {
        $this->configureAi();
        $business = $this->makeBusiness();

        Http::fake([
            'https://generativelanguage.googleapis.com/v1beta/openai/*' => Http::response(['error' => ['message' => 'Invalid API key']], 401),
        ]);

        $response = $this->post(route('businesses.generate.store', $business->business_slug));

        $response->assertRedirect(route('businesses.generate', $business->business_slug));
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('generated_websites', [
            'business_id' => $business->id,
            'generation_status' => 'failed',
        ]);
        $failed = GeneratedWebsite::where('business_id', $business->id)->first();
        $this->assertStringContainsString('invalid', strtolower($failed->error_message));
    }

    public function test_invalid_model_response_is_handled(): void
    {
        $this->configureAi();
        $business = $this->makeBusiness();

        Http::fake([
            'https://generativelanguage.googleapis.com/v1beta/openai/*' => Http::response(['error' => ['message' => 'model not found']], 404),
        ]);

        $result = app(AIWebsiteGenerationService::class)->generate($business);

        $this->assertFalse($result['ok']);
        $this->assertStringContainsString('model', strtolower($result['error']));
    }

    public function test_invalid_model_saves_single_failed_record(): void
    {
        $this->configureAi();
        $business = $this->makeBusiness();

        Http::fake([
            'https://generativelanguage.googleapis.com/v1beta/openai/*' => Http::response(['error' => ['message' => 'model not found']], 404),
        ]);

        $response = $this->post(route('businesses.generate.store', $business->business_slug));

        $response->assertRedirect(route('businesses.generate', $business->business_slug));
        $response->assertSessionHas('error');
        $this->assertSame(1, GeneratedWebsite::where('business_id', $business->id)->count());
        $failed = GeneratedWebsite::where('business_id', $business->id)->first();
        $this->assertSame('failed', $failed->generation_status);
        $this->assertStringContainsString('model', strtolower((string) $failed->error_message));
    }

    public function test_quota_exceeded_response_is_handled(): void
    {
        $this->configureAi();
        $business = $this->makeBusiness();

        Http::fake([
            'https://generativelanguage.googleapis.com/v1beta/openai/*' => Http::response(['error' => ['status' => 'RESOURCE_EXHAUSTED']], 429),
        ]);

        $result = app(AIWebsiteGenerationService::class)->generate($business);

        $this->assertFalse($result['ok']);
        $this->assertStringContainsString('quota', strtolower($result['error']));
    }

    public function test_connection_exception_is_handled_and_logged(): void
    {
        $this->configureAi();
        $business = $this->makeBusiness();
        Log::spy();

        Http::fake(function () {
            throw new \Illuminate\Http\Client\ConnectionException('cURL error 6: Could not resolve host');
        });

        $result = app(AIWebsiteGenerationService::class)->generate($business);

        $this->assertFalse($result['ok']);
        $this->assertStringContainsString('could not be reached', strtolower($result['error']));
        Log::shouldHaveReceived('warning')->atLeast()->once();
    }

    public function test_timeout_is_handled(): void
    {
        $this->configureAi();
        $business = $this->makeBusiness();

        Http::fake(function () {
            throw new \Illuminate\Http\Client\ConnectionException('cURL error 28: Operation timed out');
        });

        $result = app(AIWebsiteGenerationService::class)->generate($business);

        $this->assertFalse($result['ok']);
        $this->assertStringContainsString('timed out', strtolower($result['error']));
    }

    public function test_malformed_json_response_is_rejected(): void
    {
        $this->configureAi();
        $business = $this->makeBusiness();

        Http::fake([
            'https://generativelanguage.googleapis.com/v1beta/openai/*' => Http::response([
                'choices' => [['message' => ['content' => 'not-json']]],
            ], 200),
        ]);

        $result = app(AIWebsiteGenerationService::class)->generate($business);

        $this->assertFalse($result['ok']);
        $this->assertStringContainsString('valid json', strtolower($result['error']));
    }

    public function test_empty_ai_response_is_handled(): void
    {
        $this->configureAi();
        $business = $this->makeBusiness();

        Http::fake([
            'https://generativelanguage.googleapis.com/v1beta/openai/*' => Http::response([
                'choices' => [['message' => ['content' => '']]],
            ], 200),
        ]);

        $result = app(AIWebsiteGenerationService::class)->generate($business);

        $this->assertFalse($result['ok']);
        $this->assertStringContainsString('empty response', strtolower($result['error']));
    }

    public function test_api_key_is_never_exposed_in_logs_or_browser_output(): void
    {
        $this->configureAi();
        $business = $this->makeBusiness();
        $records = [];
        Log::listen(function ($event) use (&$records) {
            $records[] = json_encode([$event->level, $event->message, $event->context]);
        });

        Http::fake([
            'https://generativelanguage.googleapis.com/v1beta/openai/*' => Http::response(['error' => ['message' => 'Invalid API key']], 401),
        ]);

        $response = $this->post(route('businesses.generate.store', $business->business_slug));
        $response->assertRedirect(route('businesses.generate', $business->business_slug));
        $this->assertStringNotContainsString('test-secret-key', $response->getContent());
        $this->assertStringNotContainsString('test-secret-key', (string) session('error'));
        $this->assertStringNotContainsString('test-secret-key', implode("\n", $records));
    }

    public function test_failed_generation_is_saved_correctly(): void
    {
        $this->configureAi();
        $business = $this->makeBusiness();

        Http::fake([
            'https://generativelanguage.googleapis.com/v1beta/openai/*' => Http::response(['error' => ['message' => 'Server error']], 500),
        ]);

        $this->post(route('businesses.generate.store', $business->business_slug));

        $this->assertDatabaseHas('generated_websites', [
            'business_id' => $business->id,
            'generation_status' => 'failed',
        ]);
        $failed = GeneratedWebsite::where('business_id', $business->id)->first();
        $this->assertNotNull($failed->error_message);
        $this->assertNull($failed->generated_content);
    }

    public function test_successful_retry_after_503(): void
    {
        $this->configureAi();
        $business = $this->makeBusiness();

        Http::fake([
            'https://generativelanguage.googleapis.com/v1beta/openai/chat/completions' => Http::sequence()
                ->push(['error' => ['message' => 'Service unavailable']], 503)
                ->push([
                    'choices' => [['message' => ['content' => json_encode($this->validPayload())]]],
                ], 200),
        ]);

        $response = $this->post(route('businesses.generate.store', $business->business_slug));

        $response->assertRedirect();
        $this->assertDatabaseHas('generated_websites', [
            'business_id' => $business->id,
            'generation_status' => 'completed',
        ]);
        $this->assertSame(1, GeneratedWebsite::where('business_id', $business->id)->count());
    }

    public function test_failure_after_all_503_retries_saves_single_failed_record(): void
    {
        $this->configureAi();
        $business = $this->makeBusiness();

        Http::fake([
            'https://generativelanguage.googleapis.com/v1beta/openai/chat/completions' => Http::sequence()
                ->push(['error' => ['message' => 'Service unavailable']], 503)
                ->push(['error' => ['message' => 'Service unavailable']], 503)
                ->push(['error' => ['message' => 'Service unavailable']], 503)
                ->push(['error' => ['message' => 'Service unavailable']], 503),
        ]);

        $response = $this->post(route('businesses.generate.store', $business->business_slug));

        $response->assertRedirect(route('businesses.generate', $business->business_slug));
        $response->assertSessionHas('error');
        $this->assertSame(1, GeneratedWebsite::where('business_id', $business->id)->count());
        $failed = GeneratedWebsite::where('business_id', $business->id)->first();
        $this->assertSame('failed', $failed->generation_status);
        $this->assertStringContainsString('temporarily unavailable', strtolower((string) $failed->error_message));
    }

    public function test_request_timeout_saves_single_failed_record(): void
    {
        $this->configureAi();
        $business = $this->makeBusiness();

        Http::fake(function () {
            throw new \Illuminate\Http\Client\ConnectionException('cURL error 28: Operation timed out');
        });

        $response = $this->post(route('businesses.generate.store', $business->business_slug));

        $response->assertRedirect(route('businesses.generate', $business->business_slug));
        $response->assertSessionHas('error');
        $this->assertSame(1, GeneratedWebsite::where('business_id', $business->id)->count());
        $failed = GeneratedWebsite::where('business_id', $business->id)->latest()->first();
        $this->assertSame('failed', $failed->generation_status);
        $this->assertStringContainsString('timed out', strtolower((string) $failed->error_message));
    }

    public function test_one_generated_record_per_generation_request(): void
    {
        $this->configureAi();
        $business = $this->makeBusiness();

        Http::fake([
            'https://generativelanguage.googleapis.com/v1beta/openai/chat/completions' => Http::response([
                'choices' => [['message' => ['content' => json_encode($this->validPayload())]]],
            ], 200),
        ]);

        $this->post(route('businesses.generate.store', $business->business_slug));
        $this->post(route('businesses.generate.store', $business->business_slug));

        $this->assertSame(2, GeneratedWebsite::where('business_id', $business->id)->count());
        $this->assertSame(2, GeneratedWebsite::where('business_id', $business->id)->where('generation_status', 'completed')->count());
    }
}