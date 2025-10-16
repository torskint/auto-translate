<?php

declare(strict_types=1);

namespace Torskint\AutoTranslate\Services;

/**
 * Interface ServiceInterface
 *
 * Définit le contrat de base pour tous les services de traduction.
 * Chaque service doit définir sa priorité, la langue cible,
 * et fournir une méthode de traduction cohérente.
 */
interface ServiceInterface
{
    /**
     * Retourne la priorité du service.
     * Une valeur plus faible signifie une exécution plus tôt.
     */
    public function getPriority(): int;

    /**
     * Constructeur du service de traduction.
     *
     * @param string $targetLang Langue cible (ex. 'fr', 'en', 'es').
     */
    public function __construct(string $targetLang);

    /**
     * Définit la langue source.
     *
     * @param string $sourceLang Langue d’origine (ex. 'en', 'fr').
     * @return void
     */
    public function setSource(string $sourceLang): void;

    /**
     * Vérifie si la langue $targetLang est supportée.
     *
     * @return bool
     */
    public function isSupportedLanguage(): bool;

    /**
     * Traduit le texte fourni vers la langue cible $targetLang.
     *
     * @param string $text Texte à traduire.
     * @return string Résultat traduit.
     */
    public function handle(string $text): string;
    public function translate(string $text): string;
}
