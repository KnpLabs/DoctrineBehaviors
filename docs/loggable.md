# Loggable

Loggable is able to track lifecycle modifications and log them using any third party log system.

## Entity

```php
<?php
 
declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Model\Loggable\LoggableTrait;
use Knp\DoctrineBehaviors\Contract\Entity\LoggableInterface;

/**
 * @ORM\Entity
 */
class Category implements LoggableInterface
{
    use LoggableTrait;

    // you can override the default log messages defined in trait:
    public function getUpdateLogMessage(array $changeSets = []): string
    {
        return 'Changed: '.print_r($changeSets, true);
    }

    public function getRemoveLogMessage(): string
    {
        return 'removed!';
    }
}
```

## Logger Interface

These messages are then passed to the configured logger.
You can define your own, by passing a class that implements `Psr\Log\LoggerInterface`.
