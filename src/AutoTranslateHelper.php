<?php

namespace Torskint\AutoTranslate;

class AutoTranslateHelper
{

    private static $faker = [
        '(CREATED_ANNO)' ,
        '(WEBSITE_PHONE)',
        '(WEBSITE_URL)'  ,
        '(WEBSITE_EMAIL)',
        '(TEAG)'         ,
        '(WEBSITE_NAME)' ,
        '%s'             ,
    ];

    public static function count_faker_words_in_based_file(array $langage)
    {
        $faker_counter = [];

        $langageTextArray = array_values($langage);
        foreach (self::$faker as $word) {
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

    public static function get_bases_files()
    {
        $base_locale    = config('auto-translate.base_locale');
        $baseFilePath   = lang_path($base_locale);
        $files          = config('auto-translate.files');

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

    public static function rplc($text, $langIso)
    {
        $data = array(
            'es' => [
                '(NOMBRE_SITIO WEB)' => '(WEBSITE_NAME)',
            ],
            'el' => [
                '(ΤΕΑΓ)' => '(TEAG)',
            ],
            'ru' => [
                '(ИМЯ_ВЕБ-САЙТА)' => '(WEBSITE_NAME)',
            ],
            
        );

        if ( isset($data[$langIso]) ) {
            foreach ($data[$langIso] as $key => $value) {
                $text = str_ireplace($key, $value, $text);
            }
        }

        return $text;
    }

}