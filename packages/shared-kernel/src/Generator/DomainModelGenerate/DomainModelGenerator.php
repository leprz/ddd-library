<?php

declare(strict_types=1);

namespace Library\SharedKernel\Generator\DomainModelGenerate;

use Doctrine\ORM\EntityManagerInterface;
use Leprz\Boilerplate\PathNode\Folder;
use Leprz\Boilerplate\PathNode\Php\PhpClass;
use Leprz\Boilerplate\PathNode\Php\PhpInterface;
use Leprz\Boilerplate\PathNode\Php\PhpMethod;
use Leprz\Boilerplate\PathNode\Php\PhpParameter;
use Leprz\Boilerplate\PathNode\Php\PhpTrait;
use Leprz\Boilerplate\PathNode\Php\PhpType;
use Leprz\Genius\Common\Infrastructure\Generator\SourceGenerator;
use Leprz\Genius\Common\Infrastructure\Generator\TestGenerator;
use Library\Circulation\Common\Infrastructure\Persistence\EntityMapperTrait;
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
        $this->sourceGenerator->generate($this->commonDomainUuidClass($nameBuilder));
        $this->sourceGenerator->generate($this->commonDomainModelClass($nameBuilder));
        $this->sourceGenerator->generate($this->commonDomainModelConstructorParameterInterface($nameBuilder));
        $this->sourceGenerator->generate($this->commonInfrastructureEntityClass($nameBuilder));
        $this->sourceGenerator->generate($this->commonInfrastructurePersistenceEntityMapperClass($nameBuilder));
        $this->sourceGenerator->generate($this->commonInfrastructurePersistenceEntityRepositoryClass($nameBuilder));
        $this->sourceGenerator->generate($this->commonInfrastructurePersistenceEntityPersistenceClass($nameBuilder));
        $this->sourceGenerator->generate($this->commonInfrastructurePersistenceEntityProxyClass($nameBuilder));
        $this->sourceGenerator->generate($this->commonApplicationPersistencePersistenceInterface($nameBuilder));
        $this->sourceGenerator->generate($this->commonApplicationPersistenceRepositoryInterface($nameBuilder));
    }

    private function commonDomainModelClass(DomainModelGeneratorNameBuilder $nameBuilder): PhpClass
    {
        return $this->commonDomainModelFolder($nameBuilder)
            ->addPhpClass(
                new PhpClass((string)$nameBuilder->getModelName())
            )->addMethod(
                new PhpMethod(
                    '__construct',
                    params: [
                        new PhpParameter(
                            'data',
                            PhpType::object(
                                $this->commonDomainModelConstructorParameterInterface($nameBuilder)
                            )
                        ),
                    ]
                )
            );
    }

    private function commonDomainModelFolder(DomainModelGeneratorNameBuilder $nameBuilder): Folder
    {
        return $this->commonDomain()->addFolder(new Folder((string)$nameBuilder->getModelName()));
    }

    private function commonDomain(): Folder
    {
        return $this->common()->addFolder(new Folder('Domain'));
    }

    private function common(): Folder
    {
        return new Folder('Common');
    }

    private function commonDomainModelConstructorParameterInterface(DomainModelGeneratorNameBuilder $nameBuilder
    ): PhpClass {
        return $this->commonDomainModelFolder($nameBuilder)->addPhpInterface(
            new PhpInterface((string)$nameBuilder->getModelConstructorParameterInterfaceName())
        );
    }

    private function commonInfrastructureEntityClass(DomainModelGeneratorNameBuilder $nameBuilder): PhpClass
    {
        return $this->commonInfrastructure()->addFolder(new Folder('Entity'))->addPhpClass(
            new PhpClass(
                (string)$nameBuilder->getEntityName()
            )
        );
    }

    private function commonInfrastructure(): Folder
    {
        return $this->common()->addFolder(new Folder('Infrastructure'));
    }

    private function commonInfrastructurePersistenceEntityMapperClass(
        DomainModelGeneratorNameBuilder $nameBuilder
    ): PhpClass {
        return $this->commonInfrastructurePersistenceEntityFolder($nameBuilder)->addPhpClass(
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
                returnType: PhpType::object($this->commonInfrastructureEntityClass($nameBuilder)),
                params: [
                    new PhpParameter('entity', PhpType::object($this->commonInfrastructureEntityClass($nameBuilder))),
                    new PhpParameter('model', PhpType::object($this->commonDomainModelClass($nameBuilder))),
                ]
            )
        )->addMethod(
            new PhpMethod(
                'mapToNewEntity',
                returnType: PhpType::object($this->commonInfrastructureEntityClass($nameBuilder)),
                params: [
                    new PhpParameter('cart', PhpType::object($this->commonDomainModelClass($nameBuilder))),
                ]
            )
        );
    }

    private function commonInfrastructurePersistenceEntityFolder(DomainModelGeneratorNameBuilder $nameBuilder): Folder
    {
        return $this->commonInfrastructure()->addFolder(new Folder('Persistence'))->addFolder(
            new Folder((string)$nameBuilder->getModelName())
        );
    }

    private function commonInfrastructurePersistenceEntityRepositoryClass(
        DomainModelGeneratorNameBuilder $nameBuilder
    ): PhpClass {
        return $this->commonInfrastructurePersistenceEntityFolder($nameBuilder)->addPhpClass(
            new PhpClass((string)$nameBuilder->getEntityRepositoryClassName())
        )->useTraits(
            PhpTrait::fromFQCN(QueryBuilderTrait::class)
        )->implements(
            $this->commonApplicationPersistenceRepositoryInterface($nameBuilder)
        )->addMethod(
            // TODO add static visibility
            new PhpMethod('entityClass', 'protected', PhpType::string())
        )->addMethod(
            $this->repositoryGetByIdMethod($nameBuilder)
        );
    }

    private function repositoryGetByIdMethod(DomainModelGeneratorNameBuilder $nameBuilder): PhpMethod
    {
        return new PhpMethod('getById', returnType: PhpType::object($this->commonDomainModelClass($nameBuilder)));
    }

    private function commonApplicationPersistenceRepositoryInterface(
        DomainModelGeneratorNameBuilder $nameBuilder
    ): PhpInterface {
        return $this->commonApplicationPersistenceFolder()->addPhpInterface(
            new PhpInterface((string)$nameBuilder->getRepositoryInterfaceName())
        )->addMethod(
            $this->repositoryGetByIdMethod($nameBuilder)
        );
    }

    private function commonApplicationPersistenceFolder(): Folder
    {
        return $this->common()->addFolder(new Folder('Application'))->addFolder(
            new Folder('Persistence')
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
                      new PhpParameter('model', PhpType::object($this->commonDomainModelClass($nameBuilder))),
                  ]
        );
    }

    private function addMethod(DomainModelGeneratorNameBuilder $nameBuilder): PhpMethod
    {
        return new PhpMethod(
            'add', returnType: PhpType::void(), params: [
                     new PhpParameter('model', PhpType::object($this->commonDomainModelClass($nameBuilder))),
                 ]
        );
    }

    private function commonApplicationPersistencePersistenceInterface(DomainModelGeneratorNameBuilder $nameBuilder
    ): PhpInterface {
        return $this->commonApplicationPersistenceFolder()->addPhpInterface(
            new PhpInterface((string)$nameBuilder->getPersistenceInterfaceName())
        )->addMethod(
            $this->flushMethod()
        )->addMethod(
            $this->saveMethod($nameBuilder)
        )->addMethod(
            $this->addMethod($nameBuilder)
        );
    }

    private function commonDomainUuidClass(DomainModelGeneratorNameBuilder $nameBuilder): PhpClass
    {
        return $this->commonDomainModelFolder($nameBuilder)->addPhpClass(
            new PhpClass((string)$nameBuilder->getModelIdName())
        );
    }

    private function commonInfrastructurePersistenceEntityPersistenceClass(
        DomainModelGeneratorNameBuilder $nameBuilder
    ): PhpClass {
        return $this->commonInfrastructurePersistenceEntityFolder($nameBuilder)->addPhpClass(
            new PhpClass((string)$nameBuilder->getEntityPersistenceClassName())
        )->implements(
            $this->commonApplicationPersistencePersistenceInterface($nameBuilder)
        )->addMethod(
            $this->flushMethod()
        )->addMethod(
            $this->saveMethod($nameBuilder)
        )->addMethod(
            $this->addMethod($nameBuilder)
        );
    }

    private function commonInfrastructurePersistenceEntityProxyClass(DomainModelGeneratorNameBuilder $nameBuilder
    ): PhpClass {
        return $this->commonInfrastructurePersistenceEntityFolder($nameBuilder)->addPhpClass(
            new PhpClass((string)$nameBuilder->getEntityProxyName())
        )->extends(
            $this->commonDomainModelClass($nameBuilder)
        )->addMethod(
            new PhpMethod(
                '__construct',
                params: [
                    new PhpParameter(
                        'entity',
                        PhpType::object($this->commonInfrastructureEntityClass($nameBuilder))
                    ),
                ]
            )
        )->addMethod(
            new PhpMethod(
                'getEntity',
                returnType: PhpType::object($this->commonInfrastructureEntityClass($nameBuilder)),
                params: [
                    new PhpParameter(
                        'mapper',
                        PhpType::object($this->commonInfrastructurePersistenceEntityMapperClass($nameBuilder))
                    ),
                ]
            )
        );
    }
}
