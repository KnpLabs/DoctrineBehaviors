<?php

namespace BehaviorFixtures\ORM;

use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Model;

/**
 * @ORM\Entity
 */
class RenamedDeletableEntity
{
    use Model\SoftDeletable\SoftDeletable
    {
        Model\SoftDeletable\SoftDeletable::delete           as deleteTrait;
        Model\SoftDeletable\SoftDeletable::restore          as restoreTrait;
        Model\SoftDeletable\SoftDeletable::isDeleted        as isTraitDeleted;
        Model\SoftDeletable\SoftDeletable::willBeDeleted    as willTraitBeDeleted;
        Model\SoftDeletable\SoftDeletable::getDeletedAt     as getTraitDeletedAt;
        Model\SoftDeletable\SoftDeletable::setDeletedAt     as setTraitDeletedAt;
    }

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * Returns object id.
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    public function delete()
    {
        throw new BadMethodCallException($this, 'delete');
    }

    public function restore()
    {
        throw new BadMethodCallException($this, 'restore');
    }

    public function isDeleted()
    {
        throw new BadMethodCallException($this, 'isDeleted');
    }

    public function willBeDeleted()
    {
        throw new BadMethodCallException($this, 'willBeDeleted');
    }

    public function getDeletedAt()
    {
        throw new BadMethodCallException($this, 'getDeletedAt');
    }

    public function setDeletedAt()
    {
        throw new BadMethodCallException($this, 'setDeletedAt');
    }
}
