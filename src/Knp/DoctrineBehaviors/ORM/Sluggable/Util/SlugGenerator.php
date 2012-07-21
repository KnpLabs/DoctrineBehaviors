<?php
/**
 * @author Lusitanian
 * Freely released with no restrictions, re-license however you'd like!
 */

namespace Knp\DoctrineBehaviors\ORM\Sluggable\Util;

class SlugGenerator
{
    /**
     * URLizes the text.
     *
     * @static
     * @param $text
     * @return mixed
     */
    static public function urlize($text, $delimiter)
    {

        $urlized = strtolower( trim( preg_replace("/[^a-zA-Z0-9\/_|+ -]/", '', iconv('UTF-8', 'ASCII//TRANSLIT', $text) ), $delimiter ) );
        $urlized = preg_replace("/[\/_|+ -]+/", $delimiter, $urlized);

        return $urlized;
    }
}