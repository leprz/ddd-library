<?php

declare(strict_types=1);

namespace Library\SharedKernel\Infrastructure\Persistence;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;

trait QueryBuilderTrait
{
    abstract protected static function entityClass(): string;
    abstract protected function getEntityManager(): EntityManagerInterface;

    private function createQueryBuilder(string $alias): QueryBuilder
    {
        return $this->getEntityManager()->createQueryBuilder()
            ->from(static::entityClass(), $alias)
            ->select(
                $alias
            );
    }
}
