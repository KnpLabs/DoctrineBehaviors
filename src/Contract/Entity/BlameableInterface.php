<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Contract\Entity;

interface BlameableInterface
{
    /**
     * @param int|object|string $user
     */
    public function setCreatedBy($user): void;

    /**
     * @param int|object|string $user
     */
    public function setUpdatedBy($user): void;

    /**
     * @param int|object|string $user
     */
    public function setDeletedBy($user): void;

    /**
     * @return int|object|string
     */
    public function getCreatedBy();

    /**
     * @return int|object|string
     */
    public function getUpdatedBy();

    /**
     * @return int|object|string|null
     */
    public function getDeletedBy();
}
