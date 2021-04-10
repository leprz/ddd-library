<?php

declare(strict_types=1);

namespace Library\SharedKernel\Infrastructure\Money;

use Money\Currency;

trait CurrencyTrait
{
    final protected function __construct(protected Currency $currency)
    {
    }

    public static function fromCode(string $code): static
    {
        return new static(new Currency($code));
    }

    public static function default(): static
    {
        return self::fromCode('USD');
    }

    public function getCode(): string
    {
        return $this->currency->getCode();
    }
}
