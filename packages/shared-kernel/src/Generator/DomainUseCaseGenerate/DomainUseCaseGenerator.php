<?php

declare(strict_types=1);

namespace Library\SharedKernel\Generator\DomainUseCaseGenerate;

use Leprz\Boilerplate\PathNode\Php\PhpClass;
use Leprz\Boilerplate\PathNode\Php\PhpInterface;
use Leprz\Boilerplate\PathNode\Php\PhpMethod;
use Leprz\Boilerplate\PathNode\Php\PhpParameter;
use Leprz\Boilerplate\PathNode\Php\PhpType;
use Leprz\Genius\Common\Infrastructure\Generator\SourceGenerator;
use Leprz\Genius\Common\Infrastructure\Generator\TestGenerator;
use Library\SharedKernel\Generator\HexagonalBuilder\HexagonalStructureBuilder;

/**
 * @package Library\SharedKernel\Generator\DomainUseCaseGenerate
 */
class DomainUseCaseGenerator
{
    /**
     * @param \Leprz\Genius\Common\Infrastructure\Generator\SourceGenerator
     * @param \Leprz\Genius\Common\Infrastructure\Generator\TestGenerator
     */
    public function __construct(private SourceGenerator $sourceGenerator, private TestGenerator $testGenerator)
    {
    }

    /**
     * @param \Library\SharedKernel\Generator\DomainUseCaseGenerate\DomainUseCaseGeneratorNameBuilder
     * @return void
     */
    public function generate(DomainUseCaseGeneratorNameBuilder $nameBuilder): void
    {
        $this->sourceGenerator->generate($this->createCommand($nameBuilder));
        $this->sourceGenerator->generate($this->createHandler($nameBuilder));
        $this->sourceGenerator->generate($this->createActionInterface($nameBuilder));
        $this->sourceGenerator->generate($this->createActionImplementation($nameBuilder));
        $this->sourceGenerator->generate($this->createDataInterface($nameBuilder));
    }

    private function createCommand(DomainUseCaseGeneratorNameBuilder $nameBuilder): PhpClass
    {
        return HexagonalStructureBuilder::useCase($nameBuilder->getUseCaseName())
            ->application()
            ->addPhpClass(new PhpClass((string)$nameBuilder->getCommandName()))
            ->implements($this->createDataInterface($nameBuilder));
    }

    private function createHandler(DomainUseCaseGeneratorNameBuilder $nameBuilder): PhpClass
    {
        return HexagonalStructureBuilder::useCase($nameBuilder->getUseCaseName())
            ->application()
            ->addPhpClass(new PhpClass((string)$nameBuilder->getHandlerName()))
            ->addMethod(
                new PhpMethod(
                    '__invoke',
                    returnType: PhpType::void(),
                    params: [
                        new PhpParameter(
                            'command',
                            PhpType::object($this->createCommand($nameBuilder))
                        ),
                    ]
                )
            );
    }

    private function createActionInterface(DomainUseCaseGeneratorNameBuilder $nameBuilder): PhpInterface
    {
        return HexagonalStructureBuilder::useCase($nameBuilder->getUseCaseName())
            ->domain()
            ->addPhpInterface(
                new PhpInterface((string)$nameBuilder->getActionInterfaceName())
            );
    }

    private function createActionImplementation(DomainUseCaseGeneratorNameBuilder $nameBuilder): PhpClass
    {
        return HexagonalStructureBuilder::useCase($nameBuilder->getUseCaseName())
            ->application()
            ->addPhpClass(
                new PhpClass((string)$nameBuilder->getActionImplementationName())
            )->implements(
                $this->createActionInterface($nameBuilder)
            );
    }

    private function createDataInterface(DomainUseCaseGeneratorNameBuilder $nameBuilder): PhpInterface
    {
        return HexagonalStructureBuilder::useCase($nameBuilder->getUseCaseName())
            ->domain()
            ->addPhpInterface(
                new PhpInterface((string)$nameBuilder->getDataInterfaceName())
            );
    }
}
