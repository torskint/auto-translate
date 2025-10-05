<?php

namespace Torskint\AutoTranslate\Helpers;

class AutoTranslatePlaceholderHelper
{

    private static function placeholder($key)
    {
        return "__PLACEHOLDER_{$key}__";
    }

    public static function cleanText(string $text): string
    {
        return preg_replace('/[\x00-\x1F\x7F\xA0]+/u', '', $text) ?? '';
    }

    /**
     * Remplace les placeholders par des marqueurs temporaires avant traduction
     */
    public static function protect(string $text): string
    {
        $placeholders = config('auto-translate.preserve_words', []);
        foreach ($placeholders as $i => $placeholder) {
            $text = str_ireplace($placeholder, self::placeholder($i), $text);
        }
        return $text;
    }

    /**
     * Restaure les placeholders après traduction et vérifie qu'ils correspondent
     */
    public static function restore(string $original, string $translated, string $original_key): string
    {
        $placeholders           = config('auto-translate.preserve_words', []);
        $placeholder_pattern    = config('auto-translate.preserve_words_pattern', null);
        $channel                = config('auto-translate.log_channel', false);

        if ( empty($translated) ) {
            return '';
        }

        # Restauration
        foreach ($placeholders as $i => $placeholder) {
            $translated = str_ireplace(self::placeholder($i), $placeholder, $translated);
        }

        # Vérification stricte : même placeholders qu'à l'origine
        preg_match_all($placeholder_pattern, $original, $origMatches);
        preg_match_all($placeholder_pattern, $translated, $transMatches);

        $origPlaceholders = $origMatches[0] ?? [];
        $transPlaceholders = $transMatches[0] ?? [];

        # Comparaison logique (non sensible à l'ordre)
        $missingInTranslation = array_diff($origPlaceholders, $transPlaceholders);
        $extraInTranslation   = array_diff($transPlaceholders, $origPlaceholders);

        if ( !empty($missingInTranslation) || !empty($extraInTranslation) ) {

            $debug = [
                'Message'           => 'Incohérence de placeholders détectée',
                'Original'          => $original,
                'Traduit'           => $translated,
                'Référence'         => $original_key ?? '(clé inconnue)',
                'Manquants'         => $missingInTranslation,
                'Supplémentaires'   => $extraInTranslation,
            ];

            if ($channel) {
                AutoTranslateHelper::log('PLACEHOLDERS_ISSUE', $debug);
            } else {
                $message = "[TS_TRANSLATE] Incohérence de placeholders détectée :";
                throw new \RuntimeException($message . "\n". implode("\n", $debug));
            }

            return '';
        }

        return $translated;
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

    // COMPTER LE NOMBRE D'OCCURANCE DE $preserve_words QU'IL Y A DANS LE ARRAY
    public static function count_preserve_words_in_based_file(array $langage): array
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
}
