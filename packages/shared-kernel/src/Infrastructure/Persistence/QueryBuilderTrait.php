<?php

declare(strict_types=1);

namespace Library\SharedKernel\Infrastructure\Persistence;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\TransactionRequiredException;
use Library\Circulation\Common\Application\Exception\EntityNotFoundException;
use Library\Circulation\Core\Book\Domain\Book;
use LogicException;

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

    /**
     * @throws \Library\Circulation\Common\Application\Exception\EntityNotFoundException
     */
    private function getSingleResult(string $identifier, QueryBuilder $qb, ?int $lockMode = null): mixed
    {
        try {
            $query = $qb->getQuery();
            if ($lockMode) {
                $query->setLockMode($lockMode);
            }
            return $query->getSingleResult();
        } catch (NoResultException) {
            throw EntityNotFoundException::identifiedBy(static::entityClass(), $identifier);
        } catch (NonUniqueResultException | TransactionRequiredException $e) {
            throw new LogicException($e->getMessage());
        }
    }
}
