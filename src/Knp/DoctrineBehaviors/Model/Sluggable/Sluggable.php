<?php
/**
 * @author Lusitanian
 * Freely released with no restrictions, re-license however you'd like!
 */

namespace Knp\DoctrineBehaviors\Model\Sluggable;

/**
 * Sluggable trait.
 *
 * Should be used inside entities for which slugs should automatically be generated on creation for SEO/permalinks.
 */
trait Sluggable
{
    /**
     * @var string $slug
     *
     * @ORM\Column(type="string")
     */
    protected $slug;

    /**
     * Returns an array of the fields used to generate the slug.
     *
     * @abstract
     * @return array
     */
    abstract public function getSluggableFields();

    /**
     * Returns the slug's delimiter
     *
     * @return string
     */
    private function getSlugDelimiter()
    {
        return '-';
    }

    /**
     * Returns whether or not the slug gets regenerated on update.
     *
     * @return bool
     */
    private function getRegenerateSlugOnUpdate()
    {
        return true;
    }

    /**
     * Sets the entity's slug.
     *
     * @param $slug
     * @return $this
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * Returns the entity's slug.
     *
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * Generates and sets the entity's slug. Called prePersist and preUpdate
     */
    public function generateSlug()
    {
        if ($this->getRegenerateSlugOnUpdate() || empty($this->slug)) {
            $fields = $this->getSluggableFields();
            $usableValues = [];

            foreach ($fields as $field) {
                // Too bad empty is a language construct...otherwise we could use the return value in a write context :)
                $val = $this->{$field};
                if (!empty($val)) {
                    $usableValues[] = $val;
                }
            }

            if (count($usableValues) < 1) {
                throw new \UnexpectedValueException('Sluggable expects to have at least one usable (non-empty) field from the following: [ ' . implode($fields, ',') . ' ]');
            }

            // generate the slug itself
            $this->slug = $this->transliterate(implode($usableValues, ' '));
        }
    }

    /**
     * This method should transliterate given string
     * Feel free to override this any way you want.
     *
     * @param string
     * @return string
     */
    protected function transliterate($string)
    {
        $string = transliterator_transliterate("Any-Latin; NFD; [:Nonspacing Mark:] Remove; NFC; [:Punctuation:] Remove; Lower();", $string);
        $string = preg_replace('/[-\s]+/', '-', $string);

        return trim($string, '-');
    }
}
