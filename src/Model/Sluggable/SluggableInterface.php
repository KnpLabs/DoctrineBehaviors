<?php

namespace Knp\DoctrineBehaviors\Model\Sluggable;

/**
 * Interface SluggableInterface
 *
 * @author Victor Bocharsky <bocharsky.bw@gmail.com>
 */
interface SluggableInterface
{
    /**
     * Returns an array of the fields used to generate the slug.
     *
     * @return array
     */
    public function getSluggableFields();

    /**
     * Sets the entity's slug.
     *
     * @param $slug
     * @return $this
     */
    public function setSlug($slug);

    /**
     * Returns the entity's slug.
     *
     * @return string
     */
    public function getSlug();

    /**
     * Generates and sets the entity's slug. Called by prePersist and preUpdate events
     */
    public function generateSlug();
}
