<?php

declare(strict_types=1);

namespace Library\SharedKernel\Generator\DomainUseCaseGenerate;

use Aphiria\Console\Commands\Attributes\Argument;
use Aphiria\Console\Commands\Attributes\Command;
use Aphiria\Console\Input\ArgumentTypes;
use Aphiria\Console\Input\Input;
use Aphiria\Console\Output\IOutput;
use Leprz\Genius\Common\Application\Exception\InvalidArgumentException;
use Leprz\Genius\Common\Application\ValueObject\PascalCaseName;
use Leprz\Genius\Common\Infrastructure\Console\GeneratorConsoleCommand;
use Leprz\Genius\Common\Infrastructure\EventBus\ListenerProvider;

#[
    Command('generate:domain-use-case', 'Generates domain use case with data and action interface'),
    Argument('modelName', type: ArgumentTypes::REQUIRED, description: 'The name of the model for example Book'),
    Argument('actionName', type: ArgumentTypes::REQUIRED, description: 'The name of action for example CheckOut')
]
class DomainUseCaseGenerateConsoleCommand extends GeneratorConsoleCommand
{
    /**
     * @param \Leprz\Genius\Common\Infrastructure\EventBus\ListenerProvider
     * @param \Library\SharedKernel\Generator\DomainUseCaseGenerate\DomainUseCaseGenerator
     */
    public function __construct(private ListenerProvider $listenerProvider, private DomainUseCaseGenerator $generator)
    {
    }

    /**
     * @param \Aphiria\Console\Input\Input
     * @param \Aphiria\Console\Output\IOutput
     * @return void
     */
    public function generate(Input $input, IOutput $output): void
    {
        $this->generator->generate(
            new DomainUseCaseGeneratorNameBuilder(
                $this->getArgument($input, $output, 'modelName'),
                $this->getArgument($input, $output, 'actionName')
            )
        );
    }

    private function getArgument(Input $input, IOutput $output, string $arg): ?PascalCaseName
    {
        try {
            return new PascalCaseName((string)$input->arguments[$arg]);
        } catch (InvalidArgumentException $e) {
            $output->writeln("<error>{$e->getMessage()}</error>");
            exit;
        }
    }

    /**
     * @return \Leprz\Genius\Common\Infrastructure\EventBus\ListenerProvider
     */
    public function getListenerProvider(): ListenerProvider
    {
        return $this->listenerProvider;
    }
}
