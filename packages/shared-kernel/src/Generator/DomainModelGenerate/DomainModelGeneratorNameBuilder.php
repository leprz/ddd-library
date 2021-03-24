<?php

declare(strict_types=1);

namespace Library\SharedKernel\Generator\DomainModelGenerate;

use Leprz\Genius\Common\Application\ValueObject\PascalCaseName;

/**
 * @package Library\SharedKernel\Generator\DomainModelGenerate
 */
class DomainModelGeneratorNameBuilder
{
    /**
     * @var \Leprz\Genius\Common\Application\ValueObject\PascalCaseName
     */
    private PascalCaseName $modelName;

    public function __construct(PascalCaseName $name)
    {
        $this->modelName = $name;
    }

    public function getModelName(): PascalCaseName
    {
        return $this->modelName;
    }

    public function getModelIdName(): PascalCaseName
    {
        return $this->modelName->append('Id');
    }

    public function getModelConstructorParameterInterfaceName(): PascalCaseName
    {
        return $this->modelName->append('ConstructorParameterInterface');
    }

    public function getModelConstructorParameterClassName(): PascalCaseName
    {
        return $this->modelName->append('ConstructorParameter');
    }

    public function getEntityName(): PascalCaseName
    {
        return $this->modelName->append('Entity');
    }

    public function getEntityMapperClassName(): PascalCaseName
    {
        return $this->getEntityName()->append('Mapper');
    }

    public function getRepositoryInterfaceName(): PascalCaseName
    {
        return $this->modelName->append('RepositoryInterface');
    }

    public function getEntityRepositoryClassName(): PascalCaseName
    {
        return $this->getEntityName()->append('Repository');
    }

    public function getPersistenceInterfaceName(): PascalCaseName
    {
        return $this->modelName->append('PersistenceInterface');
    }

    public function getEntityPersistenceClassName(): PascalCaseName
    {
        return $this->modelName->append('EntityPersistence');
    }

    public function getEntityProxyName(): PascalCaseName
    {
        return $this->modelName->append('Proxy');
    }
}
