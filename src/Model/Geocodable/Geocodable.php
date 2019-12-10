<?php

declare(strict_types=1);

/**
 * This file is part of the KnpDoctrineBehaviors package.
 *
 * (c) KnpLabs <http://knplabs.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Knp\DoctrineBehaviors\Model\Geocodable;

/**
 * Geocodable trait.
 *
 * Should be used inside entity where you need to manipulate geographical information
 */
trait Geocodable
{
    use GeocodableProperties;
    use GeocodableMethods
    ;
}
