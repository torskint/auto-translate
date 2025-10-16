<?php

namespace Torskint\AutoTranslate\Commands;

use Illuminate\Console\Command;
use Torskint\AutoTranslate\Services\ServiceManager;
use Torskint\AutoTranslate\Helpers\AutoTranslationUtility;

class AutoTranslateStarter extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ts-translate:all';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Missings translations to automatically generate PHP files for you.';


    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $locales        = config('auto-translate.locales');
        $base_locale    = config('auto-translate.base_locale');

        foreach ($locales as $locale) {
            try {

                if ( $base_locale == $locale ) {
                    continue;
                }

                if ( !is_dir( $currentLangPath = lang_path($locale) ) ) {
                    mkdir($currentLangPath, 0755, true);
                }

                /*
                |--------------------------------------------------------------------------
                | Translation Service Initialization
                |--------------------------------------------------------------------------
                |
                | Configuration du service de traduction à utiliser dans l'application.
                | Selon la langue cible ou la disponibilité :
                | - Amazon Translate : recommandé pour la confidentialité et la gestion des placeholders
                | - Google Translate : fallback pour les langues non supportées par AWS
                |
                | ⚠️ IMPORTANT :
                | - Vérifier que la langue est supportée avant d'appeler l'API.
                | - Les placeholders (WEBSITE_NAME), (WEBSITE_URL),... doivent toujours être préservés.
                |
                */
                $translators = (new ServiceManager)->process($base_locale, $locale);

                $utility = new AutoTranslationUtility($translators, $base_locale, $locale);
                $utility->setCommand($this);
                $utility->run();

            } catch (\Exception $e) {
                $this->error('Error: ' . $e->getMessage());
            }
        }
        return Command::SUCCESS;
    }

}
