<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Model\Sluggable;

use Knp\DoctrineBehaviors\Exception\SluggableException;
use Symfony\Component\String\Slugger\AsciiSlugger;

trait SluggableMethodsTrait
{
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
        if ($this->slug !== null && $this->shouldRegenerateSlugOnUpdate() === false) {
            return;
        }

        $values = [];
        foreach ($this->getSluggableFields() as $sluggableField) {
            $values[] = $this->resolveFieldValue($sluggableField);
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

    private function shouldRegenerateSlugOnUpdate(): bool
    {
        return true;
    }

    private function generateSlugValue(array $values): string
    {
        $usableValues = [];
        foreach ($values as $value) {
            if (! empty($value)) {
                $usableValues[] = $value;
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

        throw new SluggableException(sprintf(
            'Sluggable expects to have at least one non-empty field from the following: ["%s"]',
            implode('", "', array_keys($values))
        ));
    }

    /**
     * @return mixed|null
     */
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
