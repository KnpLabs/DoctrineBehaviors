<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Contract\Entity;

interface BlameableInterface
{
    public function setCreatedBy($user): void;

    public function setUpdatedBy($user): void;

    public function setDeletedBy($user): void;

    public function getCreatedBy();

    public function getUpdatedBy();

    public function getDeletedBy();
}
