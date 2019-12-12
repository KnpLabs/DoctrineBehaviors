<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Model\Loggable;

use DateTime;

trait LoggableTrait
{
    public function getUpdateLogMessage(array $changeSets = []): string
    {
        $message = [];
        foreach ($changeSets as $property => $changeSet) {
            for ($i = 0, $s = sizeof($changeSet); $i < $s; $i++) {
                if ($changeSet[$i] instanceof DateTime) {
                    $changeSet[$i] = $changeSet[$i]->format('Y-m-d H:i:s.u');
                }
            }

            if ($changeSet[0] !== $changeSet[1]) {
                $message[] = sprintf(
                    '%s #%d : property "%s" changed from "%s" to "%s"',
                    self::class,
                    $this->getId(),
                    $property,
                    ! is_array($changeSet[0]) ? $changeSet[0] : 'an array',
                    ! is_array($changeSet[1]) ? $changeSet[1] : 'an array'
                );
            }
        }

        return implode("\n", $message);
    }

    public function getCreateLogMessage(): string
    {
        return sprintf('%s #%d created', self::class, $this->getId());
    }

    public function getRemoveLogMessage(): string
    {
        return sprintf('%s #%d removed', self::class, $this->getId());
    }
}
