<?php

namespace Torskint\AutoTranslate;

class AutoTranslateHelper
{

    private static $faker = [
        '(CREATED_ANNO)'    => 1,
        '(WEBSITE_PHONE)'   => 2,
        '(WEBSITE_URL)'     => 2,
        '(WEBSITE_EMAIL)'   => 5,
        '(TEAG)'            => 5,
        '(WEBSITE_NAME)'    => 32,
        '%s'                => 6,
    ];

    public static function generatedRules(array $langage)
    {
        $generatedRules = [];

        $langageTextArray = array_values($langage);
        foreach (array_keys(self::$faker) as $word) {
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