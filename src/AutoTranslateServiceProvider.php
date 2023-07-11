<?php

namespace Torskint\AutoTranslate;

use Torskint\AutoTranslate\Commands\AutoTranslate;
use Torskint\AutoTranslate\Commands\AutoTranslateMissing;
use Torskint\AutoTranslate\Commands\AutoTranslateReset;
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
                AutoTranslate::class, // auto-translate:translate
                AutoTranslateMissing::class, // auto-translate:missing
                AutoTranslateReset::class, // auto-translate:reset
            ]);
    }

}