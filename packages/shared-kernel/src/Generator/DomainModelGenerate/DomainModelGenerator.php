<?php

declare(strict_types=1);

namespace Library\SharedKernel\Generator\DomainModelGenerate;

use Doctrine\ORM\EntityManagerInterface;
use Leprz\Boilerplate\PathNode\Php\PhpClass;
use Leprz\Boilerplate\PathNode\Php\PhpInterface;
use Leprz\Boilerplate\PathNode\Php\PhpMethod;
use Leprz\Boilerplate\PathNode\Php\PhpParameter;
use Leprz\Boilerplate\PathNode\Php\PhpTrait;
use Leprz\Boilerplate\PathNode\Php\PhpType;
use Leprz\Genius\Common\Infrastructure\Generator\SourceGenerator;
use Leprz\Genius\Common\Infrastructure\Generator\TestGenerator;
use Library\Circulation\Common\Infrastructure\Persistence\EntityMapperTrait;
use Library\SharedKernel\Generator\HexagonalBuilder\HexagonalStructureBuilder;
use Library\SharedKernel\Infrastructure\Persistence\QueryBuilderTrait;

/**
 * @package Library\SharedKernel\Generator\DomainModelGenerate
 */
class DomainModelGenerator
{
    /**
     * @param \Leprz\Genius\Common\Infrastructure\Generator\SourceGenerator
     * @param \Leprz\Genius\Common\Infrastructure\Generator\TestGenerator
     */
    public function __construct(private SourceGenerator $sourceGenerator, private TestGenerator $testGenerator)
    {
    }

    /**
     * @param \Library\SharedKernel\Generator\DomainModelGenerate\DomainModelGeneratorNameBuilder
     * @return void
     */
    public function generate(DomainModelGeneratorNameBuilder $nameBuilder): void
    {
        $this->sourceGenerator->generate($this->domainModelUuidClass($nameBuilder));
        $this->sourceGenerator->generate($this->domainModelClass($nameBuilder));
        $this->sourceGenerator->generate($this->domainModelConstructorParameterInterface($nameBuilder));
        $this->sourceGenerator->generate($this->domainModelConstructorParameterClass($nameBuilder));
        $this->sourceGenerator->generate($this->applicationPersistencePersistenceInterface($nameBuilder));
        $this->sourceGenerator->generate($this->applicationPersistenceRepositoryInterface($nameBuilder));
        $this->sourceGenerator->generate($this->infrastructureEntityClass($nameBuilder));
        $this->sourceGenerator->generate($this->infrastructurePersistenceEntityMapperClass($nameBuilder));
        $this->sourceGenerator->generate($this->infrastructurePersistenceEntityRepositoryClass($nameBuilder));
        $this->sourceGenerator->generate($this->infrastructurePersistenceEntityPersistenceClass($nameBuilder));
        $this->sourceGenerator->generate($this->infrastructurePersistenceEntityProxyClass($nameBuilder));
    }

    private function core(DomainModelGeneratorNameBuilder $nameBuilder): HexagonalStructureBuilder
    {
        return HexagonalStructureBuilder::core($nameBuilder->getModelName());
    }

    private function domainModelClass(DomainModelGeneratorNameBuilder $nameBuilder): PhpClass
    {
        return $this->core($nameBuilder)->domain()->addPhpClass(
            new PhpClass((string)$nameBuilder->getModelName())
        )
            ->addMethod(
                new PhpMethod(
                    '__construct',
                    params: [
                        new PhpParameter(
                            'data',
                            PhpType::object(
                                $this->domainModelConstructorParameterInterface($nameBuilder)
                            )
                        ),
                    ]
                )
            );
    }

    private function domainModelConstructorParameterInterface(
        DomainModelGeneratorNameBuilder $nameBuilder
    ): PhpInterface {
        return $this->core($nameBuilder)->domain()->addPhpInterface(
            new PhpInterface((string)$nameBuilder->getModelConstructorParameterInterfaceName())
        );
    }

    private function domainModelConstructorParameterClass(
        DomainModelGeneratorNameBuilder $nameBuilder
    ): PhpClass {
        return $this->core($nameBuilder)->domain()->addPhpClass(
            new PhpClass((string)$nameBuilder->getModelConstructorParameterClassName())
        )->implements($this->domainModelConstructorParameterInterface($nameBuilder));
    }

    private function infrastructureEntityClass(DomainModelGeneratorNameBuilder $nameBuilder): PhpClass
    {
        return $this->core($nameBuilder)->infrastructure()
            ->addPhpClass(
                new PhpClass(
                    (string)$nameBuilder->getEntityName()
                )
            )->implements(
                $this->domainModelConstructorParameterInterface($nameBuilder)
            );
    }

    private function infrastructurePersistenceEntityMapperClass(
        DomainModelGeneratorNameBuilder $nameBuilder
    ): PhpClass {
        return $this->core($nameBuilder)->infrastructure()->addPhpClass(
            new PhpClass((string)$nameBuilder->getEntityMapperClassName())
        )->useTraits(
            PhpTrait::fromFQCN(EntityMapperTrait::class)
        )->addMethod(
            new PhpMethod(
                '__construct',
                params: [
                    new PhpParameter(
                        'entityManager',
                        PhpType::object(PhpInterface::fromFQCN(EntityManagerInterface::class))
                    ),
                ]
            )
        )->addMethod(
            new PhpMethod(
                'mapToExistingEntity',
                returnType: PhpType::object($this->infrastructureEntityClass($nameBuilder)),
                params: [
                    new PhpParameter('entity', PhpType::object($this->infrastructureEntityClass($nameBuilder))),
                    new PhpParameter('model', PhpType::object($this->domainModelClass($nameBuilder))),
                ]
            )
        )->addMethod(
            new PhpMethod(
                'mapToNewEntity',
                returnType: PhpType::object($this->infrastructureEntityClass($nameBuilder)),
                params: [
                    new PhpParameter('model', PhpType::object($this->domainModelClass($nameBuilder))),
                ]
            )
        );
    }

    private function infrastructurePersistenceEntityRepositoryClass(
        DomainModelGeneratorNameBuilder $nameBuilder
    ): PhpClass {
        return $this->core($nameBuilder)->infrastructure()->addPhpClass(
            new PhpClass((string)$nameBuilder->getEntityRepositoryClassName())
        )->useTraits(
            PhpTrait::fromFQCN(QueryBuilderTrait::class)
        )->implements(
            $this->applicationPersistenceRepositoryInterface($nameBuilder)
        )->addMethod(
            new PhpMethod('entityClass', 'protected static', PhpType::string())
        )->addMethod(
            $this->repositoryGetByIdMethod($nameBuilder)
        )->addMethod(
            new PhpMethod(
                '__construct',
                params: [
                    new PhpParameter(
                        'entityManager',
                        PhpType::object(PhpInterface::fromFQCN(EntityManagerInterface::class))
                    ),
                ]
            )
        )->addMethod(
            new PhpMethod(
                'getEntityManager',
                'protected',
                PhpType::object(PhpInterface::fromFQCN(EntityManagerInterface::class))
            )
        );
    }

    private function repositoryGetByIdMethod(DomainModelGeneratorNameBuilder $nameBuilder): PhpMethod
    {
        return new PhpMethod('getById', returnType: PhpType::object($this->domainModelClass($nameBuilder)));
    }

    private function applicationPersistenceRepositoryInterface(
        DomainModelGeneratorNameBuilder $nameBuilder
    ): PhpInterface {
        return $this->core($nameBuilder)->application()->addPhpInterface(
            new PhpInterface((string)$nameBuilder->getRepositoryInterfaceName())
        )->addMethod(
            $this->repositoryGetByIdMethod($nameBuilder)
        );
    }

    private function flushMethod(): PhpMethod
    {
        return new PhpMethod('flush', returnType: PhpType::void());
    }

    private function saveMethod(DomainModelGeneratorNameBuilder $nameBuilder): PhpMethod
    {
        return new PhpMethod(
            'save', returnType: PhpType::void(), params: [
                      new PhpParameter('model', PhpType::object($this->domainModelClass($nameBuilder))),
                  ]
        );
    }

    private function addMethod(DomainModelGeneratorNameBuilder $nameBuilder): PhpMethod
    {
        return new PhpMethod(
            'add', returnType: PhpType::void(), params: [
                     new PhpParameter('model', PhpType::object($this->domainModelClass($nameBuilder))),
                 ]
        );
    }

    private function applicationPersistencePersistenceInterface(DomainModelGeneratorNameBuilder $nameBuilder
    ): PhpInterface {
        return $this->core($nameBuilder)->application()->addPhpInterface(
            new PhpInterface((string)$nameBuilder->getPersistenceInterfaceName())
        )->addMethod(
            $this->flushMethod()
        )->addMethod(
            $this->saveMethod($nameBuilder)
        )->addMethod(
            $this->addMethod($nameBuilder)
        );
    }

    private function domainModelUuidClass(DomainModelGeneratorNameBuilder $nameBuilder): PhpClass
    {
        return $this->core($nameBuilder)->domain()->addPhpClass(
            new PhpClass((string)$nameBuilder->getModelIdName())
        );
    }

    private function infrastructurePersistenceEntityPersistenceClass(
        DomainModelGeneratorNameBuilder $nameBuilder
    ): PhpClass {
        return $this->core($nameBuilder)->infrastructure()->addPhpClass(
            new PhpClass((string)$nameBuilder->getEntityPersistenceClassName())
        )->implements(
            $this->applicationPersistencePersistenceInterface($nameBuilder)
        )->addMethod(
            $this->flushMethod()
        )->addMethod(
            $this->saveMethod($nameBuilder)
        )->addMethod(
            $this->addMethod($nameBuilder)
        );
    }

    private function infrastructurePersistenceEntityProxyClass(DomainModelGeneratorNameBuilder $nameBuilder
    ): PhpClass {
        return $this->core($nameBuilder)->infrastructure()->addPhpClass(
            new PhpClass((string)$nameBuilder->getEntityProxyName())
        )->extends(
            $this->domainModelClass($nameBuilder)
        )->addMethod(
            new PhpMethod(
                '__construct',
                params: [
                    new PhpParameter(
                        'entity',
                        PhpType::object($this->infrastructureEntityClass($nameBuilder))
                    ),
                ]
            )
        )->addMethod(
            new PhpMethod(
                'getEntity',
                returnType: PhpType::object($this->infrastructureEntityClass($nameBuilder)),
                params: [
                    new PhpParameter(
                        'mapper',
                        PhpType::object($this->infrastructurePersistenceEntityMapperClass($nameBuilder))
                    ),
                ]
            )
        );
    }
}
