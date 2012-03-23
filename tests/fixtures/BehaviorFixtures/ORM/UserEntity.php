<?php

namespace BehaviorFixtures\ORM;

use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\ORM as OrmBehaviors;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity
 */
class UserEntity implements UserInterface
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="string")
     */
    private $username;

    public function getRoles()
    {
        return [];
    }

    public function getPassword()
    {
        return '';
    }

    public function getSalt()
    {
        return '';
    }

    public function getUsername()
    {
        return $this->username;
    }

    public function setUsername($username)
    {
        $this->username = $username;
    }

    public function eraseCredentials()
    {
    }

    public function equals(UserInterface $user)
    {
        return $this === $user;
    }
}
