<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Contract\Entity;

interface HashidableInterface
{
    /**
     * Field used to generate the hash.
     */
    public function getHashidableField(): string;

    public function getHashId(): string;

    public function setHashId(string $hashId): void;
}
