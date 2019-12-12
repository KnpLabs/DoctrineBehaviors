# Geocodable

Geocodable provides extensions to PostgreSQL platform in order to work with cube and earth distance extensions.

It allows you to query entities based on geographical coordinates.

It also provides an easy entry point to use 3rd party libraries like the excellent [geocoder](https://github.com/willdurand/Geocoder) to transform addresses into latitude and longitude.

## Entity

```php
<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Contract\Entity\GeocodableInterface;
use Knp\DoctrineBehaviors\Model\Geocodable\GeocodableTrait;

/**
 * @ORM\Entity
 */
class Category implements GeocodableInterface
{
    use GeocodableTrait;
}
```

## Usage

```php
<?php

$geocoder = new \Geocoder\Geocoder;
// register geocoder providers

// $subscriber instanceof GeocodableSubscriber (add "knp.doctrine_behaviors.geocodable_subscriber" into your services.yml)
$subscriber->setGeolocationCallable(function($entity) use($geocoder) {
    $location = $geocoder->geocode($entity->getAddress());
    return new Point(
        $location->getLatitude(),
        $location->getLongitude()
    ));
});

$category = new Category;
$entityManager->persist($category);

$location = $category->getLocation(); // instanceof Point

// find cities in a circle of 500 km around point 47 lon., 7 lat.
$nearCities = $repository->findByDistance(new Point(47, 7), 500);
```