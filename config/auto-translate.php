<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Preserve Words Pattern -> '/\(\w+\)|%s|%d|\\\\/'
    |--------------------------------------------------------------------------
    |
    | Cette regex permet de détecter et protéger les placeholders
    | avant d'envoyer un texte en traduction.
    | 
    | ⚠️ Important : NE JAMAIS modifier ces placeholders.
    | Ils doivent rester EXACTEMENT identiques après traduction.
    |
    | Ce pattern couvre :
    | - (WEBSITE_NAME), (AUTHOR_EMAIL), (TEAG), etc.   →  (\w+)
    | - %s, %d                                         →  printf placeholders
    | - \\                                             →  un backslash littéral
    | - (PDF)                                          →  PDF entre parenthèses
    | - ({{filesize}}MiB)                              →  taille de fichier
    | - {{maxFilesize}}MiB                             →  taille max
    | - {{statusCode}}                                 →  code de statut
    |
    */
    'preserve_words_pattern' => '/\(\w+\)|%s|%d|\\\\|\(PDF\)|\(\{\{[a-zA-Z0-9_]+\}\}MiB\)|\{\{[a-zA-Z0-9_]+\}\}MiB|\{\{[a-zA-Z0-9_]+\}\}/',


    /*
    |--------------------------------------------------------------------------
    | Preserve Words
    |--------------------------------------------------------------------------
    |
    | Liste de chaînes exactes qui doivent être protégées et
    | ne jamais être traduites. Elles seront remplacées par des
    | marqueurs temporaires avant traduction, puis restaurées
    | après le retour de l'API.
    |
    | ⚠️ IMPORTANT :
    | - Respecter l'ordre et le nombre des placeholders.
    | - Ne jamais modifier leur contenu.
    |
    */
    'preserve_words' => [
        '(WEBSITE_NAME)' ,
        '(CREATED_ANNO)' ,
        '(WEBSITE_URL)' ,
        '(WEBSITE_EMAIL)' ,
        '(WEBSITE_ADDRESS)' ,
        '(WEBSITE_PHONE)' ,
        '(WEBMASTER_EMAIL)' ,
        '(WEBMASTER_NAME)' ,
        '(AUTHOR_NAME)' ,
        '(AUTHOR_EMAIL)' ,
        '(TEAG)',
        '%s',
        '\\',

        # For DropZone
        '(PDF)',
        '({{filesize}}MiB)',
        '{{maxFilesize}}MiB',
        '{{statusCode}}',
    ],


    /*
    |--------------------------------------------------------------------------
    | To Replace
    |--------------------------------------------------------------------------
    |
    | Certains services de traduction transforment les placeholders
    | en fonction de la langue (par exemple : "(WEBSITE_NAME)" devient
    | "(NOMBRE_SITIO WEB)" en espagnol).
    |
    | Ici on définit un dictionnaire qui permet de "corriger"
    | ces traductions indésirables en rétablissant les placeholders
    | originaux.
    |
    */
    'to_replace' => [
        'es' => [
            '(NOMBRE_SITIO WEB)' => '(WEBSITE_NAME)',
            '(SITIO WEB_TELÉFONO)' => '(WEBSITE_PHONE)',
            '(NOMBRE DEL SITIO WEB)' => '(WEBSITE_NAME)',
        ],
        'el' => [
            '(ΤΕΑΓ)' => '(TEAG)',
        ],
        'ru' => [
            '(ИМЯ_ВЕБ-САЙТА)' => '(WEBSITE_NAME)',
        ],
    ],

    
    /*
    |--------------------------------------------------------------------------
    | Locales supportées
    |--------------------------------------------------------------------------
    |
    | Liste des codes de langue ISO que ton package va gérer.
    | 
    | 🔤 Chaque code correspond à une locale :
    | - en : Anglais
    | - de : Allemand
    | - bg : Bulgare
    | - da : Danois
    | - es : Espagnol
    | - it : Italien
    | - lb : Luxembourgeois
    | - lt : Lituanien
    | - lv : Letton
    | - ro : Roumain
    | - sv : Suédois
    | - et : Estonien
    | - pt : Portugais
    | - no : Norvégien
    | - fi : Finnois
    | - ru : Russe
    | - nl : Néerlandais
    | - sl : Slovène
    | - mn : Mongol
    | - hu : Hongrois
    | - el : Grec
    | - pl : Polonais
    | - uz : Ouzbek
    | - hr : Croate
    | - ky : Kirghiz
    | - hy : Arménien
    | - kk : Kazakh
    | - tg : Tadjik
    | - tr : Turc
    | - sk : Slovaque
    | - sq : Albanais
    |
    | 👉 Tu peux en ajouter d’autres selon tes besoins.
    |
    */
    'locales' => [
        'en',
        'de',
        'bg',
        'da',
        'es',
        'it',
        'lb',
        'lt',
        'lv',
        'ro',
        'sv',
        'et',
        'pt',
        'no',
        'fi',
        'ru',
        'nl',
        'sl',
        'mn',
        'hu',
        'el',
        'pl',
        'uz',
        'hr',
        'ky',
        'hy',
        'kk',
        'tg',
        'tr',
        'sk',
        'sq',
    ],


    /*
    |--------------------------------------------------------------------------
    | Fichiers à traduire
    |--------------------------------------------------------------------------
    |
    | Ici, tu peux lister les fichiers PHP contenant du contenu textuel
    | (conditions générales, politique de confidentialité, mentions légales, etc.)
    | que ton package doit prendre en charge.
    |
    | ✅ Tous ces fichiers seront analysés et leurs chaînes envoyées
    | vers le pipeline de traduction automatique.
    | ✅ Les placeholders définis dans `preserve_words` / `preserve_words_pattern`
    | seront protégés.
    |
    | Exemple :
    | - torskint/privacy-policy.php            → Politique de confidentialité
    | - torskint/terms-and-conditions-of-use.php → Conditions générales d’utilisation
    | - torskint/cookie-policy.php             → Politique de cookies
    |
    */
    'files' => [
        'torskint/email-service-terms-and-conditions.php',
        'torskint/liability-in-case-of-fraud-or-unauthorized-use.php',
        'torskint/privacy-policy.php',
        'torskint/refund-and-return-policy.php',
        'torskint/terms-and-conditions-of-use.php',
        'torskint/translation.php',
        'torskint/account-closing-terms.php',
        'torskint/account-terms.php',
        'torskint/anti-fraud-policy.php',

        'torskint/cookie-policy.php',
        'torskint/legal-notice.php',
    ],

    
    /*
    |--------------------------------------------------------------------------
    | Langue de base (référence)
    |--------------------------------------------------------------------------
    |
    | C’est la langue source par défaut utilisée comme référence
    | pour toutes les traductions automatiques.
    |
    | Exemple :
    | - Si `base_locale = 'fr'`, toutes les autres langues définies
    |   dans `locales` seront générées à partir du contenu français.
    | - Si `base_locale = 'en'`, alors l’anglais sera la référence.
    |
    | ⚠️ Important :
    | - Assure-toi que tous les fichiers listés dans `files`
    |   existent bien dans cette langue.
    | - Si un texte n’existe pas dans la base, il ne pourra
    |   pas être traduit correctement.
    |
    */
    'base_locale' => 'fr'

];