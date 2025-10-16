<?php

namespace Torskint\AutoTranslate\Services;

use ReflectionClass;

class ServiceManager
{
    public function process(string $sourceLang, string $targetLang): array
    {
        $services = [];

        // Charger dynamiquement toutes les classes
        foreach (glob(__DIR__ . '/Translation/*.php') as $file) {
            $className = pathinfo($file, PATHINFO_FILENAME);
            // if (in_array($className, ['ServiceManager', 'ServiceInterface'])) continue;

            $fqcn = "Torskint\\AutoTranslate\\Services\\Translation\\{$className}";
            if (class_exists($fqcn)) {
                $reflection = new ReflectionClass($fqcn);
                if ($reflection->implementsInterface(ServiceInterface::class)) {
                    $fqcn_obj = new $fqcn( $targetLang );
                    $fqcn_obj->setSource( $sourceLang );

                    $services[] = $fqcn_obj;
                }
            }
        }

        // Trier par priorité croissante
        usort($services, fn($a, $b) => $a->getPriority() <=> $b->getPriority());

        return $services;
    }
}
