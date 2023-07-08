<?php

namespace Torskint\AutoTranslate;

use Torskint\AutoTranslate\Commands\AutoTranslate;
use Torskint\AutoTranslate\Commands\AutoTranslateMissing;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class AutoTranslateServiceProvider extends PackageServiceProvider
{

    public function configurePackage(Package $package): void
    {
        $package
            ->name('auto-translate')
            ->hasConfigFile()
            ->hasCommands([
                AutoTranslate::class,
                AutoTranslateMissing::class,
            ]);
    }

}