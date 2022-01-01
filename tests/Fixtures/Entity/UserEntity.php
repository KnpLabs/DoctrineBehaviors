<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Tests\Fixtures\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;

#[Entity]
class UserEntity
{
    public function __construct(
        #[Id]
        #[Column(type: 'integer')]
        #[GeneratedValue(strategy: 'AUTO')]
        private int $id,
        #[Column(type: 'string')]
        private string $username,
    ) {
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getUsername(): string
    {
        return $this->username;
    }
}
