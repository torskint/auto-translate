<?php

namespace Torskint\AutoTranslate\Helpers;

use Illuminate\Support\Facades\Log;

class AutoTranslatePlaceholderHelper
{
    /**
     * Remplace les placeholders par des marqueurs temporaires avant traduction
     */
    public static function protect(string $text): string
    {
        $placeholders = config('auto-translate.preserve_words', []);
        foreach ($placeholders as $i => $placeholder) {
            $text = str_ireplace($placeholder, "[[PH_{$i}]]", $text);
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

        // Restauration
        foreach ($placeholders as $i => $placeholder) {
            $translated = str_ireplace("[[PH_{$i}]]", $placeholder, $translated);
        }

        // Vérification stricte : même placeholders qu'à l'origine
        preg_match_all($placeholder_pattern, $original, $origMatches);
        preg_match_all($placeholder_pattern, $translated, $transMatches);

        if ($origMatches[0] !== $transMatches[0]) {
            // throw new \RuntimeException(
            //     "Les placeholders ne correspondent pas.\n".
            //     "Original: " . implode(', ', $origMatches[0]) . "\n".
            //     "Traduit: " . implode(', ', $transMatches[0]) . "\n".
            //     "Référence: " . $original_key
            // );

            Log::warning('[TS_TRANSLATE] Incohérence de placeholders détectée', [
                'Original'  => implode(', ', $origMatches[0]),
                'Traduit'   => implode(', ', $transMatches[0]),
                'Référence' => $original_key, // identifiant unique
            ]);
        }

        return $translated;
    }
}
