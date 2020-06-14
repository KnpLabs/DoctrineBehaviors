<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Model\Hashidable;

trait HashidableMethodsTrait
{
    public function getHashId(): ?string
    {
        return $this->hashId;
    }

    public function setHashId(string $hashId): void
    {
        $this->hashId = $hashId;
    }
}
