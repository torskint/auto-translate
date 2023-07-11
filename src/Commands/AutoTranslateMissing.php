<?php

namespace Torskint\AutoTranslate\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Stichoza\GoogleTranslate\GoogleTranslate;

use Torskint\AutoTranslate\AutoTranslateHelper;

class AutoTranslateMissing extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ts-translate:missing';

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

        $same_nbLines_result = [];
        $ok_translated_result = [];

        foreach ($locales as $locale) {
            try {

                if ( !is_dir( $currentLangPath = lang_path($locale) ) ) {
                    continue;
                }

                foreach (AutoTranslateHelper::get_bases_files() as $file) {

                    $filePath = lang_path($base_locale . DIRECTORY_SEPARATOR . $file);
                    $newFilePath = lang_path($locale . DIRECTORY_SEPARATOR . $file);

                    if ( !File::exists($filePath) || !File::exists($newFilePath) ) {
                        continue;
                    }

                    # EPURATION - SUPPRESSION DES LIGNES VIDES
                    $basedFileData  = require $filePath;
                    $newFileData    = require $newFilePath;

                    $basedFileContentArray = [];
                    $missings = [];
                    foreach ($basedFileData as $key => $value) {
                        if ( empty($value) ) {
                            continue;
                        }
                        $basedFileContentArray[$key] = $value;

                        if ( !isset($newFileData[$key]) ) {
                            $missings[$key] = $value;
                        }
                    }

                    $this->info('Missing ' . $locale . ', please wait...');
                    $this->info('- File ' . $file . ', please wait... - ' . count($missings) . ' LINES.');
                    
                    $translator = new GoogleTranslate($locale);
                    $translator->setSource($base_locale);

                    $results = [];
                    foreach ($missings as $key => $value) {
                        $translatedText = $translator->translate($value);

                        $results[$key] = AutoTranslateHelper::rplc($translatedText, $locale);
                    }

                    # Vérifier que ce fichier et le fichier de base ont le même nombre de lignes
                    $composedData = array_merge($newFileData, $results);

                    if ( count($basedFileContentArray) <> ( $ct = count($composedData) )) {
                        $same_nbLines_result[$newFilePath] = array(
                            "$newFilePath Nb Lignes"    => $ct,
                            "BASED Nb Lignes"           => count($basedFileContentArray),
                        );
                        file_put_contents(lang_path('same_nbLines_result.txt'), print_r($same_nbLines_result, true) . "\n", FILE_APPEND);
                        continue;
                    }

                    # Démarrer le traitement
                    $langageKeyArray = array_keys($missings);
                    foreach (AutoTranslateHelper::count_faker_words_in_based_file($missings) as $__word => $occurence_array) {

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
                                file_put_contents(lang_path('occurence_result.txt'), print_r($occurence_result, true) . "\n", FILE_APPEND);
                            } else {
                                $ok_translated_result[] = $newFilePath;
                            }
                        }
                    }

                    # GENERER LE FICHIER PHP
                    $content_php  = "<?php\n\n";
                    $content_php .= 'return array('."\n";
                    foreach ($composedData as $base_key => $text) {
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
                }

            } catch (\Exception $e) {
                $this->error('Error: ' . $e->getMessage());
            }
        }
        return Command::SUCCESS;
    }

}
