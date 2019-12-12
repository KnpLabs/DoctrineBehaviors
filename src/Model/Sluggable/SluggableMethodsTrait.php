<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Model\Sluggable;

use Behat\Transliterator\Transliterator;
use Nette\Utils\Strings;
use UnexpectedValueException;

trait SluggableMethodsTrait
{
    /**
     * @return string[]
     */
    abstract public function getSluggableFields(): array;

    public function setSlug(string $slug): void
    {
        $this->slug = $slug;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    /**
     * Generates and sets the entity's slug. Called prePersist and preUpdate
     */
    public function generateSlug(): void
    {
        if (! $this->getRegenerateSlugOnUpdate()) {
            return;
        }

        $values = [];
        foreach ($this->getSluggableFields() as $field) {
            $values[] = $this->resolveFieldValue($field);
        }

        $this->slug = $this->generateSlugValue($values);
    }

    private function getSlugDelimiter(): string
    {
        return '-';
    }

    private function getRegenerateSlugOnUpdate(): bool
    {
        return true;
    }

    /**
     * @return mixed|string
     */
    private function generateSlugValue($values)
    {
        $usableValues = [];
        foreach ($values as $fieldValue) {
            if (! empty($fieldValue)) {
                $usableValues[] = $fieldValue;
            }
        }

        $this->ensureAtLeastOneUsableValue($values, $usableValues);

        // generate the slug itself
        $sluggableText = implode(' ', $usableValues);

        $sluggableText = Transliterator::transliterate($sluggableText, $this->getSlugDelimiter());

        $urlized = strtolower(
            trim(
                $sluggableText = Strings::replace($sluggableText, '#[^a-zA-Z0-9\\/_|+ -]#', ''),
                $this->getSlugDelimiter()
            )
        );

        return Strings::replace($urlized, '#[\\/_|+ -]+#', $this->getSlugDelimiter());
    }

    private function ensureAtLeastOneUsableValue(array $values, array $usableValues): void
    {
        if (count($usableValues) >= 1) {
            return;
        }

        throw new UnexpectedValueException(sprintf(
            'Sluggable expects to have at least one non-empty field from the following: ["%s"]',
            implode('", "', array_keys($values))
        ));
    }

    private function resolveFieldValue(string $field)
    {
        if (property_exists($this, $field)) {
            return $this->{$field};
        }

        $methodName = 'get' . ucfirst($field);
        if (method_exists($this, $methodName)) {
            return $this->{$methodName}();
        }

        return null;
    }
}
