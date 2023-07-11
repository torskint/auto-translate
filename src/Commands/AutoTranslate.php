<?php

namespace Torskint\AutoTranslate\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Stichoza\GoogleTranslate\GoogleTranslate;

use Torskint\AutoTranslate\AutoTranslateHelper;

class AutoTranslate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ts-translate:translate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command will search everywhere in your code for translations to automatically generate JSON files for you.';

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

                if ( ! is_dir( $currentLangPath = lang_path($locale) ) ) {
                    mkdir($currentLangPath, 0777, true);
                }

                foreach (AutoTranslateHelper::get_bases_files() as $file) {
                    $filePath = lang_path($base_locale . DIRECTORY_SEPARATOR . $file);
                    $newFilePath = lang_path($locale . DIRECTORY_SEPARATOR . $file);

                    if ( ! File::exists($filePath) ) {
                        continue;
                    }

                    # EPURATION - SUPPRESSION DES LIGNES VIDES
                    $basedFileData = require $filePath;
                    $basedFileContentArray = [];
                    foreach ($basedFileData as $key => $value) {
                        if ( empty($value) ) {
                            continue;
                        }
                        $basedFileContentArray[$key] = $value;
                    }

                    $this->info('Translating ' . $locale . ', please wait...');
                    $this->info('- File ' . $file . ', please wait...');
                    
                    $translator = new GoogleTranslate($locale);
                    $translator->setSource($base_locale);

                    $results = [];
                    foreach ($basedFileContentArray as $key => $value) {
                        if ( ! empty($value) ) {
                            $translatedText = $translator->translate($value);

                            $results[$key] = AutoTranslateHelper::rplc($translatedText, $locale);
                        }
                    }

                    # Vérifier que ce fichier et le fichier de base ont le même nombre de lignes
                    $same_nbLines_result = [];
                    if ( count($basedFileContentArray) <> count($results) ) {
                        $same_nbLines_result[$newFilePath] = array(
                            "$newFilePath Nb Lignes"    => count($basedFileContentArray),
                            "BASED Nb Lignes"           => count($results),
                        );
                        file_put_contents(lang_path('same_nbLines_result.txt'), print_r($same_nbLines_result, true) . "\n", FILE_APPEND);
                        continue;
                    }

                    # Démarrer le traitement
                    $langageKeyArray = array_keys($basedFileContentArray);
                    foreach (AutoTranslateHelper::count_faker_words_in_based_file($basedFileContentArray) as $__word => $occurence_array) {

                        foreach (array_values($results) as $file_line_position => $file_line) {
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
                                file_put_contents(lang_path('occurence_result_check.txt'), print_r($occurence_result, true) . "\n", FILE_APPEND);
                            }
                        }
                    }

                    # GENERER LE FICHIER PHP
                    $content_php  = "<?php\n\n";
                    $content_php .= 'return array('."\n";
                    foreach ($results as $base_key => $text) {
                        $text = trim($text);
                        $text = str_ireplace('"https://"', "(https://)", $text);
                        $text = str_ireplace('(WEB_PS)', "%s", $text);

                        // ​​
                        $text = str_ireplace('​​', "", $text);

                        # SI ON TROUVE DES GUILLEMETS
                        if (preg_match("/\"/", $text)) {
                            $text = addslashes($text);
                        }

                        $content_php .= "\t" . "\"{$base_key}\" => \"{$text}\"," . "\n";
                    }
                    $content_php = trim($content_php);
                    $content_php = trim($content_php, ',') . "\n";
                    $content_php .= ");";

                    File::put($newFilePath, $content_php);

                    $this->info('- File ' . $file . ', FINISHED');
                }

            } catch (\Exception $e) {
                $this->error('Error: ' . $e->getMessage());
            }
        }
        return Command::SUCCESS;
    }
}
