<?php

namespace Knp\DoctrineBehaviors\Model\Taggable;

interface TagInterface
{
    public function getId();
    public function setId($id);
    public function getName();
    public function setName($name);
    public function getNameCanonical();
    public function setNameCanonical($nameCanonical);
}
