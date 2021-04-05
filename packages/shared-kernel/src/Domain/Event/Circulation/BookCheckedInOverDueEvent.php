<?php

declare(strict_types=1);

namespace Library\SharedKernel\Domain\Event\Circulation;

class BookCheckedInOverDueEvent
{
    public function __construct(
        private string $borrowerId,
        private string $libraryMaterialId,
        private string $overDueTimePeriod,
    ) {
    }

    /**
     * @return string
     */
    public function getBorrowerId(): string
    {
        return $this->borrowerId;
    }

    /**
     * @return string
     */
    public function getLibraryMaterialId(): string
    {
        return $this->libraryMaterialId;
    }

    /**
     * @return string
     */
    public function getOverDueTimePeriod(): string
    {
        return $this->overDueTimePeriod;
    }
}
