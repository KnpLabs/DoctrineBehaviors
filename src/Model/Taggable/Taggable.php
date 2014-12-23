<?php

namespace Knp\DoctrineBehaviors\Model\Taggable;

use Doctrine\Common\Collections\ArrayCollection;
use RuntimeException;

trait Taggable
{
    /**
     * @var string
     */
    protected $tagModel = 'BehaviorFixtures\\ORM\\TaggableEntityTag';

    /**
     * @var null|\Doctrine\Common\Collections\ArrayCollection|\Knp\DoctrineBehaviors\Model\Taggable\TagInterface[]
     */
    protected $tags;

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection|\Knp\DoctrineBehaviors\Model\Taggable\TagInterface[];
     */
    public function getTags()
    {
        if (!isset($this->tags)) {
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

    public function updateCanonicalFields()
    {
        foreach($this->getTags() as $tag) {
            $tag->setNameCanonical($this->canonizeTagString($tag->getName()));
        }
    }

    /**
     * @param $string
     * @return mixed
     */
    public function canonizeTagString($string)
    {
        return preg_replace('/[\W]/', '', $string);
    }

    /**
     * @param $string
     * @return string
     */
    public function trimTagString($string)
    {
        return trim($string);
    }

    /**
     * @param mixed $mixed
     * @param mixed $default
     * @return array
     * @throws \RuntimeException
     */
    protected function validateTags($mixed, $default = null)
    {
        $result = $default;
        if (is_scalar($mixed)) {
            $mixed = explode(',', $mixed);
        }
        if (is_array($mixed)) {
            foreach ($mixed as $key => $value) {
                $mixed[$key] = $this->trimTagString($value);
            }
            $result = $mixed;
        }
        if (is_object($mixed)) {
            if (!($mixed instanceof TagInterface)) {
                throw new RuntimeException('invalid tag type');
            }
            $result = [$mixed];
        }

        return $result;
    }

    /**
     * @param mixed $tags
     */
    public function addTags($tags)
    {
        /**
         * @var Taggable|\Knp\DoctrineBehaviors\Model\Taggable\TaggableInterface $this
         */
        $tags = $this->validateTags($tags);
        foreach($tags as $key => $tag) {
            if($tag instanceof TagInterface) {
                //do nothing
            } else {
                $tag = $this->createTagFromModel($tag);
            }
            $tag->setName($this->trimTagString($tags[$key]));
            $tag->setTaggable($this);
            $this->getTags()->add($tag);
        }
    }

    /**
     * @param mixed $tags
     */
    public function setTags($tags)
    {
        $this->getTags()->clear();
        $this->addTags($tags);
    }

    /**
     * @param mixed $tag
     */
    public function addTag($tag)
    {
        $this->addTags($tag);
    }

    /**
     * @param string $tags
     */
    public function removeTags($tags)
    {
        $tags = $this->validateTags($tags);
        $oldTags = $this->getTags()->filter(function($entry) use ($tags) {
            return in_array($entry->getName(), $tags);
        });
        foreach($oldTags as $tag) {
            $this->getTags()->removeElement($tag);
        }
    }

    /**
     * @param string $tag
     */
    public function removeTag($tag)
    {
        $this->removeTags($tag);
    }

    public function clearTags()
    {
        $this->getTags()->clear();
    }
}
