<?php

declare(strict_types=1);

namespace Library\SharedKernel\Generator\DomainUseCaseGenerate;

use Leprz\Genius\Common\Application\ValueObject\PascalCaseName;

/**
 * @package Library\SharedKernel\Generator\DomainUseCaseGenerate
 */
class DomainUseCaseGeneratorNameBuilder
{
    public function __construct(private PascalCaseName $modelName, private PascalCaseName $action)
    {
    }

    public function getUseCaseName(): PascalCaseName
    {
        return $this->modelName->append((string)$this->action);
    }

    public function getHandlerName(): PascalCaseName
    {
        return $this->getUseCaseName()->append('Handler');
    }

    public function getCommandName(): PascalCaseName
    {
        return $this->getUseCaseName()->append('Command');
    }

    public function getActionInterfaceName(): PascalCaseName
    {
        return $this->getUseCaseName()->append('ActionInterface');
    }

    public function getActionImplementationName(): PascalCaseName
    {
        return $this->getUseCaseName()->append('Action');
    }

    public function getDataInterfaceName()
    {
        return $this->getUseCaseName()->append('DataInterface');
    }
}
