<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Tests\Fixtures\ORM;

use DateTime;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Contract\Entity\SluggableInterface;
use Knp\DoctrineBehaviors\Model\Sluggable\Sluggable;

/**
 * @ORM\Entity
 */
class SluggableMultiEntity implements SluggableInterface
{
    use Sluggable;

    /**
     * @ORM\Column(type="string")
     * @var string
     */
    protected $name;

    /**
     * @ORM\Column(type="datetime")
     * @var DateTimeInterface
     */
    protected $date;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @var int
     */
    private $id;

    public function __construct()
    {
        $this->date = (new DateTime())->modify('-1 year');
    }

    /**
     * Returns object id.
     */
    public function getId(): int
    {
        return $this->id;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    public function getDate()
    {
        return $this->date;
    }

    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * @return string[]
     */
    public function getSluggableFields(): array
    {
        return ['name', 'title'];
    }

    public function getTitle()
    {
        return 'title';
    }

    /**
     * @return mixed|string
     */
    public function generateSlugValue(array $values)
    {
        $sluggableText = implode(' ', $values);

        return strtolower(str_replace(' ', '+', $sluggableText));
    }
}
