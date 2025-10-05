<?php

namespace Torskint\AutoTranslate;

use Torskint\AutoTranslate\Commands\AutoTranslateStarter;
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
                AutoTranslateStarter::class, // ts-translate:all
                AutoTranslateReset::class, // ts-translate:reset
                AutoTranslateTestCount::class, // ts-translate:count
            ]);
    }

}