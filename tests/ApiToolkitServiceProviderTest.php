<?php

namespace JudeUfuoma\ApiToolkit\Tests;

use JudeUfuoma\ApiToolkit\Providers\ApiToolkitServiceProvider;
use JudeUfuoma\ApiToolkit\Console\Commands\GenerateApiCollection;
use Illuminate\Support\Facades\Artisan;
use Orchestra\Testbench\TestCase;

class ApiToolkitServiceProviderTest extends TestCase
{
    /**
     * Set up the environment for the tests.
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Load the service provider
        $this->app->register(ApiToolkitServiceProvider::class);
    }

    /**
     * Test if the GenerateApiCollection command is registered correctly.
     *
     * @return void
     */
    public function test_generate_api_collection_command_is_registered()
    {
        // Check if the command exists
        $exitCode = Artisan::call('api-toolkit:generate');

        $this->assertEquals(0, $exitCode); // Exit code 0 means success
    }

    /**
     * Test if the service provider has been correctly loaded.
     *
     * @return void
     */
    public function test_service_provider_is_loaded()
    {
        $provider = $this->app->getProvider(ApiToolkitServiceProvider::class);

        $this->assertInstanceOf(
            ApiToolkitServiceProvider::class,
            $provider,
            'ApiToolkitServiceProvider is not loaded correctly.'
        );
    }
}
