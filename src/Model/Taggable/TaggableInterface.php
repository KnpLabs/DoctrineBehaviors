<?php

namespace Knp\DoctrineBehaviors\Model\Taggable;

interface TaggableInterface
{
    public function addTag($tag);

    public function addTags($tags);

    public function setTags($tags);

    public function removeTag($tag);

    public function removeTags($tags);

    public function clearTags();
}
