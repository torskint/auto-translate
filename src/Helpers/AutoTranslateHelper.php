<?php

namespace Torskint\AutoTranslate\Helpers;

class AutoTranslateHelper
{

    public static function count_faker_words_in_based_file(array $langage): array
    {
        $preserveWords = config('auto-translate.preserve_words', []);

        $faker_counter = [];

        $langageTextArray = array_values($langage);
        foreach ($preserveWords as $word) {
            $faker_counter[$word] = [];
            foreach ($langageTextArray as $lang_key_int => $text) {
                $countThis = substr_count($text, $word);
                if ( $countThis <= 0 ) {
                    continue;
                }
                if (empty($faker_counter[$word][$lang_key_int])) {
                    $faker_counter[$word][$lang_key_int] = 0;
                }
                $faker_counter[$word][$lang_key_int] += $countThis;
            }
        }

        return $faker_counter;
    }

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

    public static function rplc($text, $langIso): string
    {
        $data = config('auto-translate.to_replace', []);

        if ( isset($data[$langIso]) ) {
            foreach ($data[$langIso] as $key => $value) {
                $text = str_ireplace($key, $value, $text);
            }
        }

        return $text;
    }

}