<?php

namespace Torskint\AutoTranslate\Helpers;

use Illuminate\Support\Facades\File;

class AutoTranslateHelper
{

    public static function get_bases_files(): array
    {
        $base_locale    = config('auto-translate.base_locale');
        $baseFilePath   = lang_path($base_locale);
        $files          = config('auto-translate.files', []);

        $allBaseFiles = [];
        if ( is_dir($baseFilePath) ) {
            foreach ($files as $file) {

                $file       = trim($file, '/');
                $fileUri    = lang_path($base_locale . '/' . $file);

                if ( !is_file($fileUri) ) {
                    continue;
                }
                $allBaseFiles[] = $file;
            }
        }

        return $allBaseFiles;
    }

    public static function insert(array $composedData, string $newFilePath)
    {
        $arrayData = [];
        foreach ($composedData as $base_key => $text) {
            $text = trim($text);
            // $text = str_ireplace('"https://"', "(https://)", $text);
            // $text = str_ireplace('(WEB_PS)', "%s", $text);

            // // ​​
            // $text = preg_replace("/[\x00-\x1F\x7F\xA0]/u", '', $text);

            // # SI ON TROUVE DES GUILLEMETS
            // $text = str_replace('"', '\"', $text);

            $arrayData[$base_key] = $text;
        }
        $content_php = "<?php\n\nreturn " . var_export($arrayData, true) . ";\n";

        File::put($newFilePath, $content_php);
    }

    public static function log(string $name, array $data)
    {
        return file_put_contents(lang_path($name . '.LOG'), print_r($data, true) . "\n", FILE_APPEND);
    }

}