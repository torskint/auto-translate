<?php

namespace Torskint\AutoTranslate\Services\Translation;

use Aws\Translate\TranslateClient;
use Aws\Exception\AwsException;
use Torskint\AutoTranslate\Services\AbstractTranslateService;

class AmazonTranslate extends AbstractTranslateService
{
    protected TranslateClient $client;

    /*
    |--------------------------------------------------------------------------
    | Supported Languages
    |--------------------------------------------------------------------------
    |
    | Liste des codes de langues supportées par Amazon Translate.
    | Ces codes respectent le format ISO 639-1 (ou RFC 5646 pour les variantes de pays).
    |
    | Source officielle : 
    | https://docs.aws.amazon.com/translate/latest/dg/what-is-languages.html#what-is-languages-supported
    |
    | ⚠️ IMPORTANT :
    | - Vérifier que la langue cible fait partie de cette liste
    |   avant d'appeler l'API pour éviter les erreurs UnsupportedLanguagePair.
    | - Cette liste doit être mise à jour si AWS ajoute de nouvelles langues.
    |
    */
    protected array $supportedLanguages = [
        'af','sq','am','ar','hy','az','bn','bs','bg','ca','zh','zh-TW','hr','cs','da','fa-AF','nl','en',
        'et','fa','tl','fi','fr','fr-CA','ka','de','el','gu','ht','ha','he','hi','hu','is','id','ga',
        'it','ja','kn','kk','ko','lv','lt','mk','ms','ml','mt','mr','mn','no','ps','pl','pt','pt-PT',
        'pa','ro','ru','sr','si','sk','sl','so','es','es-MX','sw','sv','ta','te','th','tr','uk','ur',
        'uz','vi','cy',
    ];

    public function __construct(string $targetLang)
    {
        parent::__construct($targetLang);

    	$this->client 		= new TranslateClient([
    	    'version' => 'latest',
    	    'region' => env('AWS_DEFAULT_REGION', 'eu-central-1'),
    	    'credentials' => [
    	        'key' => env('AWS_ACCESS_KEY_ID'),
    	        'secret' => env('AWS_SECRET_ACCESS_KEY'),
    	    ],
    	]);
    }

    /**
     * Traduit un texte de $sourceLang vers $targetLang
     */
    public function handle(string $text): string
    {
        try {
            $result = $this->client->translateText([
                'Text' => $text,
                'SourceLanguageCode' => $this->sourceLang,
                'TargetLanguageCode' => $this->targetLang,
            ]);
            $translated = $result->get('TranslatedText');

            return $translated;
        } catch (AwsException $e) {
        	return '';
        }
    }

    public function getPriority(): int {
        return 2;
    }
}