<?php

namespace Knp\DoctrineBehaviors\Model\Uuidable;

use Ramsey\Uuid;

/**
 * Uuidable trait.
 *
 * Should be used inside entity, that needs to have an UUID4 for example for an API use
 */
trait Uuidable
{
    /**
     * @var string
     */
    private $uuid;

    /**
     * Initialize the Uuid
     */
    private function initializeUuid()
    {
        $this->uuid = Uuid::uuid4();

        return $this;
    }

    /**
     * Set Uuid
     *
     * @param string $uuid
     * @return $this
     */
    public function setUuid($uuid)
    {
        $this->uuid = $uuid;

        return $this;
    }

    /**
     * Get Uuid
     *
     * @return string
     */
    public function getUuid()
    {
        return $this->uuid;
    }

    /**
     * @return mixed|string
     */
    public function generateUuid()
    {
        $this->uuid = Uuid::Uuid4();
    }
}
