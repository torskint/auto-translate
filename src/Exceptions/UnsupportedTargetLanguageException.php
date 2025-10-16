<?php

declare(strict_types=1);

namespace Torskint\AutoTranslate\Exceptions;

use RuntimeException;

class UnsupportedTargetLanguageException extends RuntimeException
{
    public function __construct(string $source, string $target)
    {
        parent::__construct(
            "La paire de langues n'est pas supportée : {$source} → {$target}"
        );
    }
}
