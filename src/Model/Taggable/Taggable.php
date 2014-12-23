<?php

namespace Knp\DoctrineBehaviors\Model\Taggable;

use BehaviorFixtures\ORM\TaggableEntityTag;
use Doctrine\Common\Collections\ArrayCollection;
use Knp\DoctrineBehaviors\Model\Taggable\Tag;

trait Taggable
{

    protected $tagModel = 'BehaviorFixtures\ORM\TaggableEntityTag';

    /**
     * @var null|\Doctrine\Common\Collections\ArrayCollection|\Knp\DoctrineBehaviors\Model\Taggable\TagInterface[]
     */
    protected $tags;

    /**
     * @param \Knp\DoctrineBehaviors\Model\Taggable\TagInterface $tag
     */
    public function addTag(TagInterface $tag)
    {
        $this->getTags()->add($tag);
    }

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection|\Knp\DoctrineBehaviors\Model\Taggable\TagInterface[];
     */
    public function getTags()
    {
        if(!isset($this->tags)) {
            $this->tags = new ArrayCollection;
        }

        return $this->tags;
    }

    /**
     * @return \Knp\DoctrineBehaviors\Model\Taggable\TagInterface
     */
    protected function createTagFromModel()
    {
        return new $this->tagModel;
    }

    public function addTagFromString($string)
    {
        $tag = $this->createTagFromModel();
        $tag->setName($string);
        $this->addTag($tag);
    }

    public function updateCanonicalFields()
    {
        foreach($this->getTags() as $tag) {
            $tag->setNameCanonical(preg_replace('/[\W]/', '', $tag->getName()));
        }
    }
}
