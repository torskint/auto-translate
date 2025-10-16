<?php

namespace Torskint\AutoTranslate\Services\Translation;

use Stichoza\GoogleTranslate\GoogleTranslate as G2o;
use Torskint\AutoTranslate\Services\AbstractTranslateService;

class GoogleTranslate extends AbstractTranslateService
{

    public function __construct(string $targetLang)
    {
        parent::__construct($targetLang);

    	$this->client = new G2o($targetLang);
    }

    /**
     * Définit la langue source.
     *
     * @param string $sourceLang
     */
    public function setSource(string $sourceLang): void
    {
        $this->client->setSource($sourceLang);
    }


    /**
     * Traduit un texte de $sourceLang vers $targetLang
     */
    public function handle(string $text): string
    {
        try {
            $translated = $this->client->translate($text);
            return $translated;
        } catch (AwsException $e) {
        	return '';
        }
    }

    public function getPriority(): int {
        return 3;
    }
}