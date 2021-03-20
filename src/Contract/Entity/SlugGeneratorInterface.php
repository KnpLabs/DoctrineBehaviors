<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Contract\Entity;

interface SlugGeneratorInterface
{
    public function generateSlugValue(array $values): string;
}
