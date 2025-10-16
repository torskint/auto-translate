<?php

declare(strict_types=1);

namespace Torskint\AutoTranslate\Services;

use Torskint\AutoTranslate\Exceptions\UnsupportedTargetLanguageException;

/**
 * Classe abstraite de base pour tous les services de traduction.
 * Fournit les propriétés communes et une logique partielle.
 */
abstract class AbstractTranslateService implements ServiceInterface
{

    /**
     * Le client peut être de n'importe quel type de service de traduction
     * (AWS Translate, OpenAI, DeepL, etc.)
     */
    protected mixed $client;

    /**
     * @var string Langue cible
     */
    protected string $targetLang;

    /**
     * @var string Langue source
     */
    protected string $sourceLang;

    /**
     * @var array Liste des langues supportées
     */
    protected array $supportedLanguages = [];

    /**
     * Constructeur du service.
     *
     * @param string $targetLang
     */
    public function __construct(string $targetLang)
    {
        $this->targetLang = $targetLang;
    }

    /**
     * Définit la langue source.
     *
     * @param string $sourceLang
     */
    public function setSource(string $sourceLang): void
    {
        $this->sourceLang = $sourceLang;
    }

    /**
     * Vérifie si la langue $this->targetLang est supportée.
     */
    public function isSupportedLanguage(): bool
    {
        if ( !empty($this->supportedLanguages) ) {
            return in_array($this->targetLang, $this->supportedLanguages, true);
        }
        return true;
    }


    /**
     * Méthode publique finale qui gère la vérification avant traduction
     */
    final public function translate(string $text): string
    {
        if (!$this->isSupportedLanguage()) {
            // throw new UnsupportedTargetLanguageException($this->sourceLang, $this->targetLang);
            return '';
        }

        // Appelle la logique spécifique de la classe enfant
        return $this->handle($text);
    }

    /**
     * Les classes concrètes doivent implémenter la traduction.
     * Chaque classe enfant doit implémenter cette méthode
     */
    abstract public function handle(string $text): string;

    /**
     * Par défaut, priorité = 0 (peut être redéfini par le service concret)
     */
    public function getPriority(): int
    {
        return 0;
    }
}
