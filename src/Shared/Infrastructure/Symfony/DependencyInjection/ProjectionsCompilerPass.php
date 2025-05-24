<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Symfony\DependencyInjection;

use App\Shared\Infrastructure\Symfony\Command\InitializeAllProjections;
use Ecotone\EventSourcing\Attribute\Projection;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Registers all services with the #[Projection] attribute for the @InitializeAllProjections command.
 */
class ProjectionsCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $projections = [];
        foreach ($container->getDefinitions() as $definition) {
            if ((null === $className = $definition->getClass()) || !class_exists($className, false)) {
                continue;
            }
            try {
                $reflectionClass = new \ReflectionClass($className);
            } catch (\ReflectionException) {
                continue;
            }

            foreach ($reflectionClass->getAttributes() as $attribute) {
                if (Projection::class === $attribute->getName()) {
                    /** @var Projection $projection */
                    $projection = $attribute->newInstance();
                    $projections[] = $projection->getName();
                    break;
                }
            }
        }

        $command = $container->getDefinition(InitializeAllProjections::class);
        $command->setArguments(['$projections' => $projections]);
    }
}
