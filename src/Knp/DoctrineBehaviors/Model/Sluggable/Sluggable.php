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
        if ( $this->getRegenerateSlugOnUpdate() || empty( $this->slug ) ) {
            $fields = $this->getSluggableFields();
            $usableValues = [];

            foreach ($fields as $field) {
                // Too bad empty is a language construct...otherwise we could use the return value in a write context :)
                $val = $this->{$field};
                if ( !empty( $val ) ) {
                    $usableValues[] = $val;
                }
            }

            if ( count($usableValues) < 1 ) {
                throw new \UnexpectedValueException('Sluggable expects to have at least one usable (non-empty) field from the following: [ ' . implode($fields, ',') .' ]');
            }

            // generate the slug itself
            $sluggableText = implode($usableValues, ' ');
            $urlized = strtr($sluggableText, array(
                'À'=>'A','Á'=>'A','Â'=>'A','Ã'=>'A','Å'=>'A','Ä'=>'A','Æ'=>'AE',
                'à'=>'a','á'=>'a','â'=>'a','ã'=>'a','å'=>'a','ä'=>'a','æ'=>'ae',
                'Þ'=>'B','þ'=>'b','Č'=>'C','Ć'=>'C','Ç'=>'C','č'=>'c','ć'=>'c',
                'ç'=>'c','Ď'=>'D','ð'=>'d','ď'=>'d','Đ'=>'Dj','đ'=>'dj','È'=>'E',
                'É'=>'E','Ê'=>'E','Ë'=>'E','è'=>'e','é'=>'e','ê'=>'e','ë'=>'e',
                'Ì'=>'I','Í'=>'I','Î'=>'I','Ï'=>'I','ì'=>'i','í'=>'i','î'=>'i',
                'ï'=>'i','Ľ'=>'L','ľ'=>'l','Ñ'=>'N','Ň'=>'N','ñ'=>'n','ň'=>'n',
                'Ò'=>'O','Ó'=>'O','Ô'=>'O','Õ'=>'O','Ø'=>'O','Ö'=>'O','Œ'=>'OE',
                'ð'=>'o','ò'=>'o','ó'=>'o','ô'=>'o','õ'=>'o','ö'=>'o','œ'=>'oe',
                'ø'=>'o','Ŕ'=>'R','Ř'=>'R','ŕ'=>'r','ř'=>'r','Š'=>'S','š'=>'s',
                'ß'=>'ss','Ť'=>'T','ť'=>'t','Ù'=>'U','Ú'=>'U','Û'=>'U','Ü'=>'U',
                'Ů'=>'U','ù'=>'u','ú'=>'u','û'=>'u','ü'=>'u','ů'=>'u','Ý'=>'Y',
                'Ÿ'=>'Y','ý'=>'y','ý'=>'y','ÿ'=>'y','Ž'=>'Z','ž'=>'z'
            ));
            $urlized = strtolower( trim( preg_replace("/[^a-zA-Z0-9\/_|+ -]/", '', iconv('UTF-8', 'ASCII//TRANSLIT', $urlized) ), $this->getSlugDelimiter() ) );
            $urlized = preg_replace("/[\/_|+ -]+/", $this->getSlugDelimiter(), $urlized);

            $this->slug = $urlized;
        }
    }
}
