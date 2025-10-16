<?php

namespace Torskint\AutoTranslate\Services\Translation;

use OpenAI;
use Torskint\AutoTranslate\Services\Utils\OpenAIPromptor;
use Torskint\AutoTranslate\Services\AbstractTranslateService;

class OpenAITranslate extends AbstractTranslateService
{

    public function __construct(string $targetLang)
    {
    	parent::__construct($targetLang);
        
    	$this->client = OpenAI::client( env('OPENAI_API_KEY', null) );
    }

    /**
     * Traduit un texte de $sourceLang vers $targetLang
     */
    public function handle(string $text): string
    {
        try {
            $translator = new OpenAIPromptor($this->sourceLang, $this->targetLang);
            $prompt     = $translator->generate($text);

            $response   = $this->client->chat()->create([
                'model' => env('OPENAI_API_MODEL', null),
                'messages' => [
                    ['role' => 'user', 'content' => $prompt]
                ],
                // 'max_tokens' => 500  // augmente le nombre de tokens si nécessaire
            ]);
            $text       = $response->choices[0]->message->content;

            return $text;
        } catch (Exception $e) {
            return '';
        }
    }

    public function getPriority(): int {
        return 1;
    }
}