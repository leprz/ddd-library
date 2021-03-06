<?php

declare(strict_types=1);

namespace Library\SharedKernel\Infrastructure\Behat;

use Exception;
use LogicException;
use PHPUnit\Framework\MockObject\Generator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Rule\AnyInvokedCount as AnyInvokedCountMatcher;
use PHPUnit\Framework\MockObject\Rule\InvokedAtIndex as InvokedAtIndexMatcher;
use PHPUnit\Framework\MockObject\Rule\InvokedAtLeastCount as InvokedAtLeastCountMatcher;
use PHPUnit\Framework\MockObject\Rule\InvokedAtLeastOnce as InvokedAtLeastOnceMatcher;
use PHPUnit\Framework\MockObject\Rule\InvokedAtMostCount as InvokedAtMostCountMatcher;
use PHPUnit\Framework\MockObject\Rule\InvokedCount as InvokedCountMatcher;
use PHPUnit\Framework\MockObject\Stub\ReturnCallback as ReturnCallbackStub;
use Symfony\Component\DependencyInjection\ContainerInterface;

trait BehaviourTestCaseTrait
{
    public static ContainerInterface $container;

    private static ?Generator $mockGenerator = null;

    public function __construct(
        ContainerInterface $container,
    ) {
        static::requirePhpUnit();
        /** @noinspection PhpFieldAssignmentTypeMismatchInspection */
        self::$container = $container->has('test.service_container') ? $container->get(
            'test.service_container'
        ) : $container;

        $this->setUp();
    }

    abstract protected static function requirePhpUnit(): void;

    protected function resolve(string $interface): object
    {
        return $this->getFromContainer($interface);
    }

    /**
     * @template T
     * @psalm-param class-string<T> $interface The interface to resolve
     * @return T
     */
    private function getFromContainer(string $interface): object
    {
        try {
            return self::$container->get($interface);
        } catch (Exception $e) {
            throw new LogicException($e->getMessage());
        }
    }

    protected function bindMock(string $interface): MockObject
    {
        $stub = $this->createMock($interface);
        $this->bindInstance($interface, $stub);
        return $stub;
    }

    /**
     * @template T
     * @param string $interface The interface to resolve
     * @return T The resolved instance
     */
    protected function createMock(string $interface): MockObject
    {
        if (self::$mockGenerator === null) {
            self::$mockGenerator = new Generator();
        }

        return self::$mockGenerator->getMock($interface);
    }

    /**
     * @template T
     * @param class-string<T> $id The interface to bind
     * @param object $instance
     * @return T The resolved instance
     */
    protected function bindInstance(string $id, object $instance): void
    {
        self::$container->set($id, $instance);
    }

    protected function setUp(): void
    {
    }


    /**
     * Returns a matcher that matches when the method is executed
     * zero or more times.
     */
    public static function any(): AnyInvokedCountMatcher
    {
        return new AnyInvokedCountMatcher;
    }

    /**
     * Returns a matcher that matches when the method is never executed.
     */
    public static function never(): InvokedCountMatcher
    {
        return new InvokedCountMatcher(0);
    }

    /**
     * Returns a matcher that matches when the method is executed
     * at least N times.
     */
    public static function atLeast(int $requiredInvocations): InvokedAtLeastCountMatcher
    {
        return new InvokedAtLeastCountMatcher(
            $requiredInvocations
        );
    }

    /**
     * Returns a matcher that matches when the method is executed at least once.
     */
    public static function atLeastOnce(): InvokedAtLeastOnceMatcher
    {
        return new InvokedAtLeastOnceMatcher;
    }

    /**
     * Returns a matcher that matches when the method is executed exactly once.
     */
    public static function once(): InvokedCountMatcher
    {
        return new InvokedCountMatcher(1);
    }

    /**
     * Returns a matcher that matches when the method is executed
     * exactly $count times.
     */
    public static function exactly(int $count): InvokedCountMatcher
    {
        return new InvokedCountMatcher($count);
    }

    /**
     * Returns a matcher that matches when the method is executed
     * at most N times.
     */
    public static function atMost(int $allowedInvocations): InvokedAtMostCountMatcher
    {
        return new InvokedAtMostCountMatcher($allowedInvocations);
    }

    /**
     * Returns a matcher that matches when the method is executed
     * at the given index.
     */
    public static function at(int $index): InvokedAtIndexMatcher
    {
        return new InvokedAtIndexMatcher($index);
    }

    public static function returnCallback($callback): ReturnCallbackStub
    {
        return new ReturnCallbackStub($callback);
    }
}
