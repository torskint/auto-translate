<?php

namespace Torskint\AutoTranslate\Services\Utils;

class OpenAIPromptor
{
    private string $sourceLanguage;
    private string $targetLanguage;

    public function __construct(string $sourceLanguage, string $targetLanguage)
    {
        $this->sourceLanguage = $sourceLanguage;
        $this->targetLanguage = $targetLanguage;
    }

    public function generate(string $textToTranslate): string
    {
        $prompt = <<<EOT
You are a professional translator specializing in software localization, UI content, and technical texts.  

Your task is to translate the following text from {$this->sourceLanguage} to {$this->targetLanguage}.  
Translate fully into {$this->targetLanguage}. Do not leave any part in {$this->sourceLanguage}, except the placeholders. Keep placeholders exactly as they are.

---

The translated text must adhere to the following guidelines:  

1. Placeholders & variables
   - Identify all placeholders in the source text. Examples: {user_name}, {{variable}}, :count, %s, etc.
   - Preserve all placeholders exactly as they appear.
   - Do not add, remove or rename any placeholders.
   - Verify that the number of placeholders in the translated text exactly matches the source. If any mismatch is detected, correct it.

2. Formatting
   - Preserve punctuation, numbers, HTML tags, Markdown, or any code snippets exactly.
   - Do not translate any text inside code blocks, HTML tags, or variable names.

3. Context & readability
   - Maintain the original meaning and context.
   - Adjust sentence structure naturally for the target language, while keeping placeholders intact.
   - Ensure consistent terminology: the same word appearing multiple times must always be translated the same way.

---

Return only the translated text. Do not include explanations, comments, or extra text. Ensure no placeholders are altered or missing.  

Source text:
"{$textToTranslate}"
EOT;

        return $prompt;
    }
}