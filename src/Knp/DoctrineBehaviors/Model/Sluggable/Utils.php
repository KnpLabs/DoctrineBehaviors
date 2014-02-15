<?php

namespace Knp\DoctrineBehaviors\Model\Sluggable;

/**
  * This class contains function which transliterates string in russian using traditional russian way of transliterating instead of ISO transliteration rules.
 *
 * @package Knplabs\DoctrineBehaviors\Model\Sluggable
 * @author Alex Panshin <deadyaga@gmail.com>
 */
class Utils
{
    private static $ru_alphabet = array( 'а','б','в','г','д','е','ё','ж','з','и','й','к','л','м','н','о','п','р','с','т','у','ф','х','ц','ч','ш','щ','ъ','ы','ь','э','ю','я');
    private static $translit_alphabet = array('a','b','v','g','d','e','yo','zh', 'z', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'r', 's', 't', 'u','f', 'h', 'ts', 'ch', 'sh', 'sch', '', 'y', 'j', 'e', 'yu', 'ya');

    /**
     * This method prepares string to be an url's part
     * @param string $string
     * @return string
     */
    public static function transliterateRussian($string)
    {
        $string = function_exists('mb_strtolower') ? mb_strtolower($string, 'UTF-8') : strtolower($string);

        $string = str_replace(self::$ru_alphabet, self::$translit_alphabet, $string);

        $string = transliterator_transliterate("Any-Latin; NFD; [:Nonspacing Mark:] Remove; NFC; [:Punctuation:] Remove;", $string);
        $string = preg_replace('/[-\s]+/', '-', $string);

        return trim($string, '-');
    }
} 