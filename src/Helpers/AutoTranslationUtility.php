<?php

namespace Torskint\AutoTranslate\Helpers;

use Illuminate\Support\Facades\File;
use Torskint\AutoTranslate\Helpers\AutoTranslateHelper;
use Torskint\AutoTranslate\Helpers\AutoTranslatePlaceholderHelper;

class AutoTranslationUtility
{

	protected array $sourceFileData = [];
	protected array $targetFileData = [];

	protected ?string $sourceLang = null;
	protected ?string $targetLang = null;
	protected array $translators = [];

	protected array $sourceFileContentArray = [];
	protected array $missings = [];
	
	function __construct(array $translators, string $sourceLang, string $targetLang)
	{
		$this->sourceLang = $sourceLang;
		$this->targetLang = $targetLang;

		$this->translators = $translators;
	}

	public function run()
	{
		foreach (AutoTranslateHelper::get_bases_files() as $file) {

		    $sourceFilePath  = lang_path($this->sourceLang . DIRECTORY_SEPARATOR . $file);
		    $targetFilePath  = lang_path($this->targetLang . DIRECTORY_SEPARATOR . $file);

		    # SI LE FICHIER DE LANG DE BASE EST INTROUVABLE
		    # ON PASSE AU FICHIER SUIVANT à TRADUIRE.
		    if ( !File::exists($sourceFilePath) ) {
		        continue;
		    }
		    $_sourceFileData  = require $sourceFilePath;

		    # TEMPORAIRES
		    if ( !is_dir($TD  = dirname($targetFilePath)) ) {
		        mkdir($TD, 0777, true);
		    }
		    $_targetFileData  = (is_file($targetFilePath)) ? require $targetFilePath : [];

		    # ON VEUT DES ARRAYS
		    $this->sourceFileData  = is_array($_sourceFileData) ? $_sourceFileData : [];
		    $this->targetFileData  = is_array($_targetFileData) ? $_targetFileData : [];

		    $this->detectMissingLines();
		    if ( empty($this->missings) ) {
		        continue;
		    }
		    $this->info('Missing -> ' . $this->targetLang . ', please wait...');
		    $this->info('- File ' . $file . ', please wait... - ' . count($this->missings) . ' LINES.');

		    $results = [];
		    $this->wrongly_translated_data = [];
		    foreach ($this->missings as $original_key => $original_str) {

		    	$translatedText = $this->translateText($original_str);
		        if ( $translatedText === false ) {
		        	$this->wrongly_translated_data[$original_key] = [
		        	    'original'      => $original_str,
		        	    // 'translated'    => $translatedText,
		        	];

		        	AutoTranslateHelper::log('PLACEHOLDERS_ISSUE', [
		        	    $targetFilePath => [
		        	        "$targetFilePath Nb Lignes"    => $ct,
		        	        "SOURCE NB LINE"           => $sourceFileSize,
		        	    ]
		        	]);

		        	continue;
		        }
		        $results[$original_key] = $translatedText;
		    }

		    # Vérifier que ce fichier et le fichier de base ont le même nombre de lignes
		    $composedData_1 = array_merge($this->targetFileData, $results);
		    $composedData   = array_merge($composedData_1, $this->wrongly_translated_data);

		    $sourceFileSize = count($this->sourceFileContentArray);
		    if ( $sourceFileSize <> ( $ct = count($composedData) )) {
		        AutoTranslateHelper::log('SAME_LINES_ISSUE', [
		            $targetFilePath => [
		                "$targetFilePath Nb Lignes" => $ct,
		                "SOURCE NB LINE" => $sourceFileSize,
		            ]
		        ]);
		        continue;
		    }

		    # GENERER LE FICHIER PHP
		    AutoTranslateHelper::insert($composedData, $targetFilePath);

		    $this->info('- File ' . $file . ', FINISHED');
		}
	}


	private function translateText(string $original_str): bool|string
	{
		# VEROUILLAGE DES PLACEHOLDERS
		[$protected_str, $mapping] = AutoTranslatePlaceholderHelper::protect($original_str);

		foreach ($this->translators as $translator) {
			# DEMARRAGE DE LA TRADUCTION DU TEXTE ACTUEL
			$translated_str = $translator->translate($protected_str);

			# NETTOYAGE DES CARACTÈRES INVISIBLES ET NON IMPRIMABLES
			$translated_str = AutoTranslatePlaceholderHelper::cleanText($translated_str);

			# RESTORATION DES PLACEHOLDERS
			# REMPLACEMENT DE CERTAINS MOTS LOCAUX CASSES PAR LEURS EQUIVALENTS
			if ( !empty($translated_str) ) {
			    [$restored_str, $valid, $errors] = AutoTranslatePlaceholderHelper::restore($translated_str, $mapping);
			    
			    # ---------------------------------------------------------
			    # RETOURNE UN TEXTE VIDE, SI LA TRADUCTION EST ERRONEE.
			    # ---------------------------------------------------------
			    if ( $valid ) {
			        return $restored_str;
			    }
			}
		}

		return false;
	}


	private function detectMissingLines(): void
	{
		$this->missings = [];
		$this->sourceFileContentArray = [];

		foreach ($this->sourceFileData as $key => $value) {
		    if ( empty($value) ) {
		        continue;
		    }
		    $this->sourceFileContentArray[$key] = $value;

		    if ( !isset($this->targetFileData[$key]) || empty($this->targetFileData[$key]) ) {
		        $this->missings[$key] = $value;
		    }
		}
	}
}