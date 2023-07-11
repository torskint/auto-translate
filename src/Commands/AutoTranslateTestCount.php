<?php

namespace Torskint\AutoTranslate\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

use Torskint\AutoTranslate\AutoTranslateHelper;

class AutoTranslateTestCount extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ts-translate:count';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check that the number of words to be replaced (WEBSITE_NAME, %s, WEBSITE_URL, etc) corresponds to the number in each target language file.';


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

                if ( !is_dir( $currentLangPath = lang_path($locale) ) ) {
                    $this->error('Error: Dossier Inexistant : ' . $locale);
                    continue;
                }

                foreach (AutoTranslateHelper::get_bases_files() as $file) {

                    $filePath = lang_path($base_locale . DIRECTORY_SEPARATOR . $file);
                    $newFilePath = lang_path($locale . DIRECTORY_SEPARATOR . $file);

                    if ( !File::exists($filePath) || !File::exists($newFilePath) ) {
                        $this->error("Error: Fichier Inexistant : {$filePath} / {$newFilePath}");
                        continue;
                    }

                    # EPURATION - SUPPRESSION DES LIGNES VIDES
                    $basedFileData  = require $filePath;
                    $newFileData    = require $newFilePath;

                    $basedFileContentArray = [];
                    foreach ($basedFileData as $key => $value) {
                        if ( empty($value) ) {
                            continue;
                        }
                        $basedFileContentArray[$key] = $value;
                    }
                    $this->info('Checking ' . $locale . ', please wait...');
                    
                    $same_nbLines_result = [];
                    if ( ( $cb = count($basedFileContentArray)) <> ( $ct = count($newFileData) )) {
                        $same_nbLines_result[$newFilePath] = array(
                            "$newFilePath Nb Lignes"    => $ct,
                            "BASED Nb Lignes"           => $cb,
                        );
                        $this->info('Size Error :: ' . $locale . " :: BASED == $cb, TARGET == $ct");
                        file_put_contents(lang_path('same_nbLines_result.txt'), print_r($same_nbLines_result, true) . "\n", FILE_APPEND);
                        continue;
                    }

                    # Démarrer le traitement
                    $langageKeyArray = array_keys($basedFileContentArray);
                    foreach (AutoTranslateHelper::count_faker_words_in_based_file($basedFileContentArray) as $__word => $occurence_array) {

                        foreach (array_values($newFileData) as $file_line_position => $file_line) {
                            if (empty($occurence_array[$file_line_position])) {
                                continue;
                            }
                            $getBasedFileWordCountByLine    = $occurence_array[$file_line_position];
                            $langageKeyName                 = $langageKeyArray[$file_line_position];

                            $counter = substr_count($file_line, $__word);
                            if ( $counter <> $getBasedFileWordCountByLine ) {
                                $occurence_result[$newFilePath][ $__word ] = array(
                                    'ligne'     => $langageKeyName,
                                    'normal'    => $getBasedFileWordCountByLine,
                                    'trouvé'    => $counter,
                                    'la ligne'  => $file_line,
                                );
                                $this->info('Occurrence error :: ' . $locale . " :: WORD == $__word, LINE == $langageKeyName, NORMAL == $getBasedFileWordCountByLine, FOUND == $counter");
                                file_put_contents(lang_path('occurence_result.txt'), print_r($occurence_result, true) . "\n", FILE_APPEND);
                            } else {
                                $this->info('Checking ' . $locale . ', OK');
                            }
                        }
                    }
                }

            } catch (\Exception $e) {
                $this->error('Error: ' . $e->getMessage());
            }
        }
        return Command::SUCCESS;
    }

}
