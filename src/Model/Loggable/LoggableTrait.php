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
            $itemCount = count($changeSet);

            for ($i = 0, $s = $itemCount; $i < $s; ++$i) {
                $item = $changeSet[$i];

                if ($item instanceof DateTime) {
                    $changeSet[$i] = $item->format('Y-m-d H:i:s.u');
                }
            }

            if ($changeSet[0] === $changeSet[1]) {
                continue;
            }

            $message[] = $this->createChangeSetMessage($property, $changeSet);
        }

        return implode("\n", $message);
    }

    public function getCreateLogMessage(): string
    {
        return sprintf('%s #%s created', self::class, $this->getId());
    }

    public function getRemoveLogMessage(): string
    {
        return sprintf('%s #%s removed', self::class, $this->getId());
    }

    private function createChangeSetMessage(string $property, array $changeSet): string
    {
        return sprintf(
            '%s #%s : property "%s" changed from "%s" to "%s"',
            self::class,
            $this->getId(),
            $property,
            is_array($changeSet[0]) ? 'an array' : (string) $changeSet[0],
            is_array($changeSet[1]) ? 'an array' : (string) $changeSet[1]
        );
    }
}
