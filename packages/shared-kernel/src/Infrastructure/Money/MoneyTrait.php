<?php

declare(strict_types=1);

namespace Library\SharedKernel\Infrastructure\Money;

use Money\Currency;
use Money\Money;

trait MoneyTrait
{
    protected function __construct(protected Money $money)
    {
    }

    protected static function _create(int $amount, string $code): static
    {
        return new self(new Money($amount, new Currency($code)));
    }

    private function _subtract($subtrahend): static
    {
        return new static($this->money->subtract($subtrahend->money));
    }

    private function _add($money): static
    {
        return new static(
            $this->money->add(
                $money->getAdapter()
            )
        );
    }

    private function _multiply(float $multiplier): static
    {
        return new static($this->money->multiply($multiplier));
    }

    public function negative(): static
    {
        return new static($this->money->negative());
    }

    protected function _getCurrencyCode(): string
    {
        return $this->money->getCurrency()->getCode();
    }

    private function getAdapter(): Money
    {
        return $this->money;
    }

    private function _equals($money): bool
    {
        return $this->money->equals($money->getAdapter());
    }

    public function getAmountInMainUnit(): float
    {
        return (int)$this->money->getAmount() / 10;
    }

    private function _greaterThan($money): bool
    {
        return $this->money->greaterThan($money->getAdapter());
    }

    public function getAmount(): int
    {
        return (int) $this->money->getAmount() * 10;
    }
}
