<?php

declare(strict_types=1);

namespace Library\SharedKernel\Infrastructure\Persistence;

use Doctrine\ORM\EntityManagerInterface;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use RuntimeException;

trait EntityMapperTrait
{
    /** @noinspection PhpMixedReturnTypeCanBeReducedInspection */
    private function createNewInstanceWithoutConstructor(string $class): mixed
    {
        try {
            return (new ReflectionClass($class))->newInstanceWithoutConstructor();
        } catch (ReflectionException $e) {
            throw new RuntimeException($e->getMessage());
        }
    }

    private function mapProperties($model, $entity, ?EntityManagerInterface $entityManager = null): mixed
    {
        try {
            $reflectionClass = new ReflectionClass($model);
        } catch (ReflectionException $e) {
            throw new RuntimeException($e->getMessage());
        }

        $modelProperties = $this->extractModelProperties($reflectionClass);

        foreach ($modelProperties as $property) {
            $propertyName = $property->getName();

            $property->setAccessible(true);

            $setterMethodName = self::propNameToSetter($propertyName);

            self::assertSetterExistsOnEntity($entity, $setterMethodName, $propertyName, (string) $property->getType());

            if ($entityManager && $this->isEntityMangerRequiredInSetter($entity, $setterMethodName)) {
                $entity->{$setterMethodName}($property->getValue($model), $entityManager);
            } else {
                $entity->{$setterMethodName}($property->getValue($model));
            }
        }

        return $entity;
    }

    /**
     * @param \ReflectionClass $reflectionClass
     * @return \ReflectionProperty[]
     */
    private function extractModelProperties(ReflectionClass $reflectionClass): array
    {
        $parent = $reflectionClass->getParentClass();

        if ($parent !== false && self::isProxyClass($reflectionClass)) {
            return  $parent->getProperties();
        }

        return $reflectionClass->getProperties();
    }

    /**
     * @param \ReflectionClass $reflectionClass
     * @return bool
     */
    private static function isProxyClass(ReflectionClass $reflectionClass): bool
    {
        return str_contains($reflectionClass->getName(), 'Proxy');
    }

    private static function propNameToSetter(string $propName): string
    {
        return 'set' . ucfirst($propName);
    }

    private static function assertSetterExistsOnEntity(
        object $entity,
        string $setterMethodName,
        string $propertyName,
        string $propertyType
    ): void {
        if (!method_exists($entity, $setterMethodName)) {
            throw new RuntimeException(
                sprintf(
                    "Entity [%s] is missing method [%s]",
                    $entity::class,
                    static::generateMethodBodyStub(
                        $propertyName,
                        $propertyType
                    ),
                )
            );
        }
    }

    private static function generateMethodBodyStub(string $propName, string $propClassName): string
    {
        return sprintf(
            'public function %s(\%s $%s): void {}',
            self::propNameToSetter($propName),
            $propClassName,
            $propName,
        );
    }

    private function isEntityMangerRequiredInSetter(object $entity, string $methodName): bool
    {
        try {
            $reflectionMethod = new ReflectionMethod($entity, $methodName);
        } catch (ReflectionException $e) {
            throw new RuntimeException($e->getMessage());
        }

        return count($reflectionMethod->getParameters()) > 1;
    }
}
