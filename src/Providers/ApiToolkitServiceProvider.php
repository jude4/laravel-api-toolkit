<?php

namespace JudeUfuoma\ApiToolkit\Providers;

use Illuminate\Support\ServiceProvider;
use JudeUfuoma\ApiToolkit\Console\Commands\GenerateApiCollection;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class ApiToolkitServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('api-toolkit')
            ->hasConfigFile() // This registers the config file
            ->hasCommand(GenerateApiCollection::class);


    }
}
