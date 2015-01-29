<?php

namespace Knp\DoctrineBehaviors\Model\Taggable;

interface TagInterface
{
    /**
     * @return mixed
     */
    public function getId();

    /**
     * @param $id
     * @return void
     */
    public function setId($id);

    /**
     * @return string
     */
    public function getName();

    /**
     * @param string $name
     * @return void
     */
    public function setName($name);

    /**
     * @return string
     */
    public function getNameCanonical();

    /**
     * @param string $nameCanonical
     * @return void
     */
    public function setNameCanonical($nameCanonical);

    /**
     * @return \Knp\DoctrineBehaviors\Model\Taggable\TaggableInterface
     */
    public function getTaggable();

    /**
     * @param \Knp\DoctrineBehaviors\Model\Taggable\TaggableInterface $taggable
     */
    public function setTaggable(TaggableInterface $taggable);
}
