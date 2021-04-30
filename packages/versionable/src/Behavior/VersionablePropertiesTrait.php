<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Versionable\Behavior;

use Doctrine\ORM\Mapping as ORM;

trait VersionablePropertiesTrait
{
    /**
     * @ORM\Version
     * @ORM\Column(type="integer")
     * @var int
     */
    public $version;
}
