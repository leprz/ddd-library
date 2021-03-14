<?php

declare(strict_types=1);

namespace Library\SharedKernel\Generator\HexagonalBuilder;

use Leprz\Boilerplate\PathNode\Folder;
use Leprz\Genius\Common\Application\ValueObject\PascalCaseName;

class HexagonalStructureBuilder
{
    public function __construct(private Folder $baseFolder)
    {
    }

    public static function useCase(PascalCaseName $name): self
    {
        return new self(
            (new Folder('UseCase'))->addFolder(
                new Folder((string)$name)
            )
        );
    }

    public static function common(): self
    {
        return new self(new Folder('Common'));
    }

    public function application(): Folder
    {
        return $this->baseFolder->addFolder(new Folder('Application'));
    }

    public function infrastructure(): Folder
    {
        return $this->baseFolder->addFolder(new Folder('Infrastructure'));
    }

    public function domain(): Folder
    {
        return $this->baseFolder->addFolder(new Folder('Domain'));
    }
}
