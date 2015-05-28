<?php
namespace Knp\DoctrineBehaviors\Model\Sluggable;

interface TransliteratorInterface
{
	public function transliterate($text, $separator = '-');
}
