<?php

declare(strict_types=1);

namespace Library\SharedKernel\Generator\DomainModelGenerate;

use Aphiria\Console\Commands\Attributes\Argument;
use Aphiria\Console\Commands\Attributes\Command;
use Aphiria\Console\Input\ArgumentTypes;
use Aphiria\Console\Input\Input;
use Aphiria\Console\Output\IOutput;
use Leprz\Genius\Common\Infrastructure\Console\GeneratorConsoleCommand;
use Leprz\Genius\Common\Infrastructure\Console\NameArgumentGetterTrait;
use Leprz\Genius\Common\Infrastructure\EventBus\ListenerProvider;

#[
    Command('generate:domain-model', 'Generates domain model and doctrine entity mapping'),
    Argument('name', type: ArgumentTypes::REQUIRED, description: 'The name of the generator')
]
class DomainModelGenerateConsoleCommand extends GeneratorConsoleCommand
{
    use NameArgumentGetterTrait;

    /**
     * @param \Leprz\Genius\Common\Infrastructure\EventBus\ListenerProvider
     * @param \Library\SharedKernel\Generator\DomainModelGenerate\DomainModelGenerator
     */
    public function __construct(private ListenerProvider $listenerProvider, private DomainModelGenerator $generator)
    {
    }

    /**
     * @param \Aphiria\Console\Input\Input
     * @param \Aphiria\Console\Output\IOutput
     * @return void
     */
    public function generate(Input $input, IOutput $output): void
    {
        $name = $this->getNameArgument($input, $output);

        $this->generator->generate(new DomainModelGeneratorNameBuilder($name));
    }

    /**
     * @return \Leprz\Genius\Common\Infrastructure\EventBus\ListenerProvider
     */
    public function getListenerProvider(): ListenerProvider
    {
        return $this->listenerProvider;
    }
}
