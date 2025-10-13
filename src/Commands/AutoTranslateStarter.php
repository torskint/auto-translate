<?php

namespace Torskint\AutoTranslate\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

use Stichoza\GoogleTranslate\GoogleTranslate;
use Torskint\AutoTranslate\Services\AmazonTranslate;

use Torskint\AutoTranslate\Helpers\AutoTranslateHelper;
use Torskint\AutoTranslate\Helpers\AutoTranslatePlaceholderHelper as PH;

class AutoTranslateStarter extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ts-translate:all';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Missings translations to automatically generate PHP files for you.';


    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $locales        = config('auto-translate.locales');
        $base_locale    = config('auto-translate.base_locale');

        foreach ($locales as $locale) {
            try {

                if ( $base_locale == $locale ) {
                    continue;
                }

                if ( !is_dir( $currentLangPath = lang_path($locale) ) ) {
                    mkdir($currentLangPath, 0755, true);
                }

                /*
                |--------------------------------------------------------------------------
                | Translation Service Initialization
                |--------------------------------------------------------------------------
                |
                | Configuration du service de traduction à utiliser dans l'application.
                | Selon la langue cible ou la disponibilité :
                | - Amazon Translate : recommandé pour la confidentialité et la gestion des placeholders
                | - Google Translate : fallback pour les langues non supportées par AWS
                |
                | ⚠️ IMPORTANT :
                | - Vérifier que la langue est supportée avant d'appeler l'API.
                | - Les placeholders (WEBSITE_NAME), (WEBSITE_URL),... doivent toujours être préservés.
                |
                */
                $translator = new AmazonTranslate($locale);
                if ( ! $translator->isSupportedLanguage() ) {
                    $translator = new GoogleTranslate($locale);
                }
                $translator->setSource($base_locale); 

                foreach (AutoTranslateHelper::get_bases_files() as $file) {

                    $baseFilePath    = lang_path($base_locale . DIRECTORY_SEPARATOR . $file);
                    $targetFilePath  = lang_path($locale . DIRECTORY_SEPARATOR . $file);

                    # TEMPORAIRES
                    if ( !is_dir($TD  = dirname($targetFilePath)) ) {
                        mkdir($TD, 0777, true);
                    }

                    # SI LE FICHIER DE LANG DE BASE EST INTROUVABLE
                    # ON QUITTE L'EXECUTION DU SCRIPT.
                    if ( !File::exists($baseFilePath) ) {
                        continue;
                    }

                    # EPURATION - SUPPRESSION DES LIGNES VIDES
                    $basedFileData  = require $baseFilePath;

                    $targetFileData    = [];
                    if ( is_file($targetFilePath) ) {
                        $targetFileData    = require $targetFilePath;
                    }

                    # ON VEUT DES ARRAYS
                    $basedFileData    = is_array($basedFileData) ? $basedFileData : [];
                    $targetFileData   = is_array($targetFileData) ? $targetFileData : [];

                    $missings = [];
                    $basedFileContentArray = [];
                    foreach ($basedFileData as $key => $value) {
                        if ( empty($value) ) {
                            continue;
                        }
                        $basedFileContentArray[$key] = $value;

                        if ( !isset($targetFileData[$key]) || empty($targetFileData[$key]) ) {
                            $missings[$key] = $value;
                        }
                    }

                    if ( empty($missings) ) {
                        // $this->info('-> ' . $locale . ' -> OK.');
                        continue;
                    }
                    $this->info('Missing -> ' . $locale . ', please wait...');
                    $this->info('- File ' . $file . ', please wait... - ' . count($missings) . ' LINES.');

                    $results = [];
                    $wrongly_translated_data = [];
                    foreach ($missings as $original_key => $original_str) {

                        # VEROUILLAGE DES PLACEHOLDERS
                        $protected_str = PH::protect($original_str);

                        # DEMARRAGE DE LA TRADUCTION DU TEXTE ACTUEL
                        $translated_str = $translator->translate($protected_str);

                        # NETTOYAGE DES CARACTÈRES INVISIBLES ET NON IMPRIMABLES
                        $translated_str = PH::cleanText($translated_str);

                        # RESTORATION DES PLACEHOLDERS
                        # REMPLACEMENT DE CERTAINS MOTS LOCAUX CASSES PAR LEURS EQUIVALENTS
                        if ( !empty($translated_str) ) {
                            $restored_str               = PH::restore($original_str, $translated_str, $original_key);
                            
                            # ---------------------------------------------------------
                            # RETOURNE UN TEXTE VIDE, SI LA TRADUCTION EST ERRONNEE.
                            # ---------------------------------------------------------
                            if ( empty($restored_str) ) {
                                $wrongly_translated_data[$original_key] = [
                                    'original'      => $original_str,
                                    'translated'    => $translated_str,
                                ];
                                continue;
                            }

                            $results[$original_key]     = PH::rplc($restored_str, $locale);
                        }
                    }

                    # Vérifier que ce fichier et le fichier de base ont le même nombre de lignes
                    $composedData_1 = array_merge($targetFileData, $results);
                    $composedData   = array_merge($composedData_1, $wrongly_translated_data);

                    $basedFileSize = count($basedFileContentArray);
                    if ( $basedFileSize <> ( $ct = count($composedData) )) {
                        AutoTranslateHelper::log('SAME_LINES_ISSUE', [
                            $targetFilePath => [
                                "$targetFilePath Nb Lignes"    => $ct,
                                "BASED Nb Lignes"           => $basedFileSize,
                            ]
                        ]);
                        continue;
                    }

                    # Démarrer le traitement
                    $langageKeyArray = array_keys($missings);
                    foreach (PH::count_preserve_words_in_based_file($missings) as $__word => $occurence_array) {

                        foreach (array_values($results) as $file_line_position => $file_line) {
                            if (empty($occurence_array[$file_line_position])) {
                                continue;
                            }
                            $getBasedFileWordCountByLine    = $occurence_array[$file_line_position];
                            $langageKeyName                 = $langageKeyArray[$file_line_position];

                            $counter = substr_count($file_line, $__word);
                            if ( $counter <> $getBasedFileWordCountByLine ) {
                                AutoTranslateHelper::log('PLACEHOLDERS_ISSUE', [
                                    $targetFilePath => [
                                        $__word => [
                                            'ligne'     => $langageKeyName,
                                            'normal'    => $getBasedFileWordCountByLine,
                                            'trouvé'    => $counter,
                                            'la ligne'  => $file_line,
                                        ]
                                    ]
                                ]);
                            }
                        }
                    }

                    # GENERER LE FICHIER PHP
                    AutoTranslateHelper::insert($composedData, $targetFilePath);

                    $this->info('- File ' . $file . ', FINISHED');
                }

            } catch (\Exception $e) {
                $this->error('Error: ' . $e->getMessage());
            }
        }
        return Command::SUCCESS;
    }

}
