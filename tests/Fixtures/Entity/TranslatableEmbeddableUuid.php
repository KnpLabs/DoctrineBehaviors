<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Tests\Fixtures\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Embeddable;
use Doctrine\ORM\Mapping\Id;
use Ramsey\Uuid\Uuid;

#[Embeddable]
class TranslatableEmbeddableUuid
{
    #[Id]
    #[Column(name: 'uuid', unique: true)]
    protected string $value;

    public function __construct(string $value)
    {
        $this->value = $value;
    }

    public static function random(): self
    {
        return new self(Uuid::uuid4()->toString());
    }
}