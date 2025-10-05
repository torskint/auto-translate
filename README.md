# Laravel Auto Translate

Ce package fournit un moyen simple d’**automatiser la génération de fichiers de traduction PHP** pour vos projets Laravel.  
Les traductions sont générées automatiquement à partir d’une **langue de référence** (`base_locale`) vers toutes les locales configurées, en utilisant une API de traduction (Google Translate ou OpenAI).

Il inclut :  
✅ La protection stricte des **placeholders** (`(WEBSITE_NAME)`, `%s`, `{{statusCode}}`, etc.)  
✅ Un système de **corrections post-traduction** (`to_replace`) pour éviter les mauvaises substitutions faites par les moteurs de traduction  
✅ La gestion de fichiers spécifiques (ex. conditions générales, politique de confidentialité, mentions légales…)  
✅ Des commandes Artisan pour générer, vérifier et réinitialiser vos traductions  

---

## ⚙️ Installation

Installer le package via Composer :

```bash
composer require torskint/auto-translate:^5.2.0
````

Ajouter le provider dans votre fichier `config/app.php` :

```php
'providers' => [
    // ...
    \Torskint\AutoTranslate\AutoTranslateServiceProvider::class,
],
```

Publier le fichier de configuration :

```bash
php artisan vendor:publish --tag=auto-translate-config
```

---

## 📂 Configuration

Le fichier `config/auto-translate.php` permet de personnaliser le comportement du package.

### Langue de base

```php
'base_locale' => 'fr',
```

La langue de référence à partir de laquelle toutes les traductions seront générées.

---

### Locales gérées

```php
'locales' => [
    'en', 'de', 'es', 'it', 'ru', 'el', 'tr', 'pl', 'pt',
    'sv', 'fi', 'nl', 'hu', 'ro', 'sk', 'sq', 'sl', 'bg',
    'da', 'no', 'lv', 'lt', 'et', 'hr', 'mn', 'uz', 'ky',
    'hy', 'kk', 'tg', 'lb',
],
```

La liste complète des locales qui seront générées automatiquement.

---

### Fichiers à traduire

```php
'files' => [
    'torskint/privacy-policy.php',
    'torskint/terms-and-conditions-of-use.php',
    'torskint/cookie-policy.php',
    'torskint/legal-notice.php',
],
```

Liste des fichiers contenant du contenu statique (souvent juridique) à traduire.

---

### Protection des placeholders

```php
'preserve_words' => [
    '(WEBSITE_NAME)', '(WEBSITE_EMAIL)', '(WEBSITE_PHONE)',
    '(AUTHOR_NAME)', '(AUTHOR_EMAIL)', '(TEAG)',
    '(PDF)', '({{filesize}}MiB)', '{{maxFilesize}}MiB', '{{statusCode}}',
    '\\', '%s', '%d',
],
```

Regex utilisée pour détecter les placeholders dynamiques :

```php
'preserve_words_pattern' => '/\(\w+\)|%s|%d|\\\\|\(PDF\)|\(\{\{[a-zA-Z0-9_]+\}\}MiB\)|\{\{[a-zA-Z0-9_]+\}\}MiB|\{\{[a-zA-Z0-9_]+\}\}/',
```

---

### Corrections post-traduction (`to_replace`)

```php
'to_replace' => [
    'es' => [
        '(NOMBRE_SITIO WEB)'    => '(WEBSITE_NAME)',
        '(SITIO WEB_TELÉFONO)'  => '(WEBSITE_PHONE)',
    ],
    'el' => [
        '(ΤΕΑΓ)' => '(TEAG)',
    ],
    'ru' => [
        '(ИМЯ_ВЕБ-САЙТА)' => '(WEBSITE_NAME)',
    ],
],
```

Permet de corriger automatiquement certains placeholders que Google Translate ou OpenAI peuvent traduire par erreur.

---

## 🚀 Utilisation

### Générer toutes les traductions

```bash
php artisan ts-translate:all
```

👉 Génère toutes les traductions pour toutes les locales définies.

---

### Réinitialiser toutes les traductions

```bash
php artisan ts-translate:reset
```

👉 Supprime toutes les traductions générées et conserve uniquement les fichiers de base.

---

### Vérifier les placeholders

```bash
php artisan ts-translate:count
```

👉 Vérifie que le nombre de placeholders est identique avant et après traduction.
Exemple : `(WEBSITE_NAME)` doit rester **exactement** `(WEBSITE_NAME)`.

---

## 🔒 Sécurité

* Les placeholders sont protégés avant l’envoi aux services de traduction et restaurés après.
* Une vérification stricte s’assure qu’aucun placeholder n’est supprimé, ajouté ou modifié.
* Des corrections (`to_replace`) sont appliquées automatiquement pour éviter les faux positifs.

---

## 📜 Licence

Le MIT License (MIT). Voir le fichier [LICENSE.md](LICENSE.md) pour plus d’informations.
