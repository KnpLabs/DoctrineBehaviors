<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Model\Sluggable;

use Symfony\Component\String\Slugger\AsciiSlugger;
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
        if ($this->slug !== null && $this->getRegenerateSlugOnUpdate() === false) {
            return;
        }

        $values = [];
        foreach ($this->getSluggableFields() as $field) {
            $values[] = $this->resolveFieldValue($field);
        }

        $this->slug = $this->generateSlugValue($values);
    }

    public function shouldGenerateUniqueSlugs(): bool
    {
        return false;
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

        $unicodeString = (new AsciiSlugger())->slug($sluggableText, $this->getSlugDelimiter());

        return strtolower($unicodeString->toString());
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
