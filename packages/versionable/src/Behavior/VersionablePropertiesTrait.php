<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Versionable\Behavior;

use Doctrine\ORM\Mapping as ORM;

trait VersionablePropertiesTrait
{
    /**
     * This property handled internally by Doctrine itself.
     *
     * Version gets incremented on any property change, e.g. if you change "$title" property and the "$version" = 1, the
     * "$version" property gets changed to 2.
     *
     * @ORM\Version
     * @ORM\Column(type="integer", nullable=false)
     * @var int
     */
    public $version;
}
