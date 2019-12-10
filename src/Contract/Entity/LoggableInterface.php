<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Contract\Entity;

interface LoggableInterface
{
    public function getUpdateLogMessage(array $changeSets = []): string;

    public function getCreateLogMessage(): string;

    public function getRemoveLogMessage(): string;
}
