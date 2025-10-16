<?php

namespace Torskint\AutoTranslate\Helpers;

class AutoTranslatePlaceholderHelper extends AbstractTranslateHelper
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
    public static function protect(string $text): array
    {
        $placeholders = config('auto-translate.preserve_words', []);
        $mapping = [];

        foreach ($placeholders as $i => $placeholder) {
            $token = self::placeholder($i);

            // Compter les occurrences dans le texte
            $count = substr_count(strtolower($text), strtolower($placeholder));

            if ($count === 0) {
                continue;
            }
            $text = str_ireplace($placeholder, $token, $text);

            // Enregistrer le mapping
            $mapping[$token] = [
                'placeholder' => $placeholder,
                'count' => $count,
            ];
        }

        return [
            'protected_str' => $text,
            'mapping'  => $mapping,
        ];
    }


    /**
     * Restaure les placeholders dans le texte traduit
     * et détecte les erreurs éventuelles.
     *
     * @return array {
     *     @var string 'text'  → texte restauré
     *     @var array  'errors' → erreurs de restauration détectées
     * }
     */
    public static function restore(string $translatedText, array $mapping): array
    {
        $errors = [];
        $restored = $translatedText;

        // Extraire la liste attendue
        $expectedTokens = array_keys($mapping);

        // 1️⃣ Trouver tous les tokens présents dans le texte via détection dynamique
        //    (On cherche seulement ceux du mapping)
        $foundTokens = [];
        foreach ($expectedTokens as $token) {
            if (stripos($translatedText, $token) !== false) {
                $foundTokens[] = $token;
            }
        }

        // 2️⃣ Détecter les tokens inattendus
        //    -> tout texte ressemblant à un token mais non dans le mapping
        //    Pour cela, on cherche une "empreinte" commune.
        // $tokenFragments = self::detectCommonTokenFragments($expectedTokens);
        // if ($tokenFragments) {
        //     // Ex : "__PH_" ou "@@PRESERVE_" ou autre
        //     [$prefix, $suffix] = $tokenFragments;
        //     $pattern = '/' . preg_quote($prefix, '/') . '.*?' . preg_quote($suffix, '/') . '/';
        //     preg_match_all($pattern, $translatedText, $matches);
        //     $tokensInText = $matches[0] ?? [];

        //     // Comparer avec les tokens attendus
        //     $unexpectedTokens = array_diff($tokensInText, $expectedTokens);
        //     foreach ($unexpectedTokens as $token) {
        //         $errors[] = [
        //             'type' => 'unexpected_token',
        //             'token' => $token,
        //             'message' => "Le token {$token} n’existe pas dans le mapping original.",
        //         ];
        //     }
        // }

        // 3️⃣ Tokens manquants
        $missingTokens = array_diff($expectedTokens, $foundTokens);
        foreach ($missingTokens as $token) {
            $errors[] = [
                'type' => 'missing_token',
                'token' => $token,
                'expected_placeholder' => $mapping[$token]['placeholder'] ?? null,
                'message' => "Le token {$token} est absent du texte traduit.",
            ];
        }

        // 4️⃣ Vérifier le nombre d’occurrences attendu
        foreach ($mapping as $token => $info) {
            $expectedCount = $info['count'];
            $actualCount   = substr_count($translatedText, $token);

            if ($actualCount !== $expectedCount) {
                $errors[] = [
                    'type' => 'count_mismatch',
                    'token' => $token,
                    'expected' => $expectedCount,
                    'found' => $actualCount,
                    'message' => "Le token {$token} apparaît {$actualCount} fois au lieu de {$expectedCount}.",
                ];
            }
        }

        // 5️⃣ Stop si erreurs critiques
        if (!empty($errors)) {
            return [
                'restored' => $translatedText,
                'valid' => false,
                'errors' => $errors,
            ];
        }

        // 6️⃣ Restauration stricte
        foreach ($mapping as $token => $info) {
            $placeholder = $info['placeholder'];
            $restored = str_ireplace($token, $placeholder, $restored);
        }

        // 7️⃣ Vérifier qu’il ne reste aucun token connu dans le texte final
        foreach ($expectedTokens as $token) {
            if (stripos($restored, $token) !== false) {
                $errors[] = [
                    'type' => 'unrestored_token',
                    'token' => $token,
                    'message' => "Le token {$token} n’a pas été restauré correctement.",
                ];
            }
        }

        return [
            'restored_str'  => $restored,
            'valid'         => empty($errors),
            'errors'        => $errors,
        ];
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
