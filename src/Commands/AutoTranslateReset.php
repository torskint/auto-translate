<?php

namespace Torskint\AutoTranslate\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

use Torskint\AutoTranslate\Helpers\AutoTranslateHelper;

class AutoTranslateReset extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ts-translate:reset';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command will delete all translated files.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $locales = config('auto-translate.locales');

        foreach ($locales as $locale) {
            try {
                if ( ! is_dir( $currentLangPath = lang_path($locale) ) ) {
                    continue;
                }

                foreach (AutoTranslateHelper::get_bases_files() as $file) {
                    $newFilePath = lang_path($locale . DIRECTORY_SEPARATOR . $file);

                    if ( !File::exists($newFilePath) ) {
                        continue;
                    }
                    File::delete($newFilePath);

                    $this->info('Reset ' . $locale . ', please wait...');
                }

            } catch (\Exception $e) {
                $this->error('Error: ' . $e->getMessage());
            }
        }
        return Command::SUCCESS;
    }
}
