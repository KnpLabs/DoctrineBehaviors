<?php
/**
 * @author Lusitanian
 * Freely released with no restrictions, re-license however you'd like!
 */

namespace Knp\DoctrineBehaviors\Model\Sluggable;

/**
* Transliteration utility
*/
class Transliterator implements TransliteratorInterface
{
	public function transliterate($text, $separator = '-')
	{
		return \Behat\Transliterator\Transliterator::transliterate($text, $separator);
	}
}
