<?php

namespace Torskint\AutoTranslate\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Stichoza\GoogleTranslate\GoogleTranslate;

if ( ! defined('DS') ) {
    define('DS', DIRECTORY_SEPARATOR);
}

class AutoTranslate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'auto:translate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command will search everywhere in your code for translations to automatically generate JSON files for you.';


    private $faker = [
        '(CREATED_ANNO)'    => 1,
        '(WEBSITE_PHONE)'   => 2,
        '(WEBSITE_URL)'     => 2,
        '(WEBSITE_EMAIL)'   => 5,
        '(TEAG)'            => 5,
        '(WEBSITE_NAME)'    => 32,
        '%s'                => 6,
    ];

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $locales        = config('auto-translate.locales');
        $base_locale    = config('auto-translate.base_locale');
        $baseFilePath   = lang_path($base_locale);

        $allBaseFiles = [];
        if ( is_dir($baseFilePath) ) {
            foreach (scandir($baseFilePath) as $file) {
                if ( $file == '.' || $file == '..' || $file <> 'translation.php' ) {
                    continue;
                }
                $allBaseFiles[] = $file;
            }
        }
        $same_nbLines_result = [];
        $ok_translated_result = [];

        foreach ($locales as $locale) {
            try {
                $currentLangPath = lang_path($locale);
                if ( ! is_dir($currentLangPath) ) {
                    mkdir($currentLangPath, 0777, true);
                }

                foreach ($allBaseFiles as $file) {
                    $filePath = lang_path($base_locale . DS . $file);
                    $newFilePath = lang_path($locale . DS . $file);

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
                            $results[$key] = $translator->translate($value);
                        }
                    }

                    # Vérifier que ce fichier et le fichier de base ont le même nombre de lignes
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
                    foreach ($this->generatedRules($basedFileContentArray) as $__word => $occurence_array) {

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
                }

            } catch (\Exception $e) {
                $this->error('Error: ' . $e->getMessage());
            }
        }
        return Command::SUCCESS;
    }

    private function generatedRules(array $langage)
    {
        $generatedRules = [];

        $langageTextArray = array_values($langage);
        foreach (array_keys($this->faker) as $word) {
            $generatedRules[$word] = [];
            foreach ($langageTextArray as $lang_key_int => $text) {
                $countThis = substr_count($text, $word);
                if ( $countThis <= 0 ) {
                    continue;
                }
                if (empty($generatedRules[$word][$lang_key_int])) {
                    $generatedRules[$word][$lang_key_int] = 0;
                }
                $generatedRules[$word][$lang_key_int] += $countThis;
            }
        }

        return $generatedRules;
    }
}
