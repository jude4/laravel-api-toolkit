<?php

namespace JudeUfuoma\ApiToolkit\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use JudeUfuoma\ApiToolkit\Console\Commands\GenerateApiCollection;
use Orchestra\Testbench\TestCase;

class GenerateApiCollectionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Clear any existing generated file
        if (File::exists(base_path('postman_collection.json'))) {
            File::delete(base_path('postman_collection.json'));
        }
    }

    /** @test */
    public function it_generates_postman_collection_file()
    {
        // Register a test API route
        Route::post('api/test-route', function () {
            return response()->json(['success' => true]);
        })->middleware('auth:api');

        Artisan::call('api-toolkit:generate');

        $this->assertFileExists(base_path('postman_collection.json'));
    }

    /** @test */
    public function it_includes_form_request_rules_in_postman_collection()
    {
        // Create a test FormRequest
        $formRequest = <<<'EOT'
        <?php
        namespace App\Http\Requests;
        use Illuminate\Foundation\Http\FormRequest;
        class TestFormRequest extends FormRequest {
            public function rules() {
                return [
                    'email' => 'required|email',
                    'age' => 'numeric|min:18',
                    'subscribe' => 'boolean'
                ];
            }
        }
        EOT;

        file_put_contents(app_path('Http/Requests/TestFormRequest.php'), $formRequest);

        // Register route using the FormRequest
        Route::post('api/form-request-route', function (App\Http\Requests\TestFormRequest $request) {
            return response()->json(['success' => true]);
        });

        Artisan::call('api-toolkit:generate');

        $collection = json_decode(File::get(base_path('postman_collection.json')), true);
        $requestBody = $collection['item'][0]['request']['body']['raw'];

        $this->assertJsonStringEqualsJsonString(
            json_encode([
                'email' => 'example@test.com',
                'age' => 123,
                'subscribe' => true
            ]),
            $requestBody
        );
    }

    /** @test */
    public function it_falls_back_to_docblock_when_no_form_request_exists()
    {
        // Register route with docblock params
        Route::post(
            'api/docblock-route',
            /**
             * @bodyParam name string required Example: John Doe
             * @bodyParam age integer Example: 30
             */
            function () {
                return response()->json(['success' => true]);
            }
        );

        Artisan::call('api-toolkit:generate');

        $collection = json_decode(File::get(base_path('postman_collection.json')), true);
        $requestBody = $collection['item'][0]['request']['body']['raw'];

        $this->assertJsonStringEqualsJsonString(
            json_encode([
                'name' => 'John Doe',
                'age' => 30
            ]),
            $requestBody
        );
    }

    /** @test */
    public function it_includes_authentication_headers_for_protected_routes()
    {
        Route::post('api/protected-route', function () {
            return response()->json(['success' => true]);
        })->middleware('auth:api');

        Artisan::call('api-toolkit:generate');

        $collection = json_decode(File::get(base_path('postman_collection.json')), true);
        $headers = $collection['item'][0]['request']['header'];

        $this->assertContains([
            'key' => 'Authorization',
            'value' => 'Bearer {{token}}',
            'type' => 'text'
        ], $headers);
    }

    /** @test */
    public function it_generates_query_params_for_get_requests()
    {
        Route::get(
            'api/search',
            /**
             * @queryParam q string required Search term. Example: test
             * @queryParam page integer Page number. Example: 1
             */
            function () {
                return response()->json(['results' => []]);
            }
        );

        Artisan::call('api-toolkit:generate');

        $collection = json_decode(File::get(base_path('postman_collection.json')), true);
        $queryParams = $collection['item'][0]['request']['url']['query'];

        $this->assertEquals([
            ['key' => 'q', 'value' => 'test'],
            ['key' => 'page', 'value' => '1']
        ], $queryParams);
    }

    /** @test */
    public function it_scans_routes_from_config()
    {
        config(['api-toolkit.route_files' => ['api-admin.php']]);

        File::put(base_path('routes/api-admin.php'), <<<'EOT'
    <?php
    Route::post('api/admin/test', function () {
        return response()->json(['admin' => true]);
    });
    EOT);
        Artisan::call('api-toolkit:generate');
        $collection = json_decode(File::get(base_path('postman_collection.json')), true);
        $this->assertEquals('api/admin/test', $collection['item'][0]['name']);
    }
}
