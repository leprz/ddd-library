<?php

declare(strict_types=1);

namespace Library\SharedKernel\Infrastructure\Persistence;

use Ramsey\Uuid\Uuid;

trait UuidV4GeneratorTrait
{
    public function uuidV4(): string
    {
        return Uuid::uuid4()->toString();
    }
}
