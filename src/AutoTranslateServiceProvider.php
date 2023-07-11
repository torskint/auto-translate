<?php

namespace Torskint\AutoTranslate;

use Torskint\AutoTranslate\Commands\AutoTranslate;
use Torskint\AutoTranslate\Commands\AutoTranslateMissing;
use Torskint\AutoTranslate\Commands\AutoTranslateReset;
use Torskint\AutoTranslate\Commands\AutoTranslateTestCount;

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
                AutoTranslate::class, // ts-translate:translate
                AutoTranslateMissing::class, // ts-translate:missing
                AutoTranslateReset::class, // ts-translate:reset
                AutoTranslateTestCount::class, // ts-translate:count
            ]);
    }

}