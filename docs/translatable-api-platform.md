# Translatable - API Platform

How to use Translatable with API platform.

Let's say you have a Document Entity like so:

Please note: you don't have a `title` property in this entity, however it has a getter for it, we are accessing the `DocumentTranslation` via
the `TranslatableMethodsTrait` you can do this for all the translatable properties.

```php
use Knp\DoctrineBehaviors\Contract\Entity\TranslatableInterface;
use Knp\DoctrineBehaviors\Model\Translatable\TranslatableTrait;

class Document implements TranslatableInterface
{
use TranslatableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    public function getTitle(): string
    {
        return $this->translate()
            ->getTitle();
    }
}
```

Then in the DocumentTranslation entity:

```php
use Knp\DoctrineBehaviors\Contract\Entity\TranslationInterface;
use Knp\DoctrineBehaviors\Model\Translatable\TranslationTrait;

#[ORM\Entity]
class DocumentTranslation implements TranslationInterface
{
    use TranslationTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\Column(type: 'string', length: 255)]
    private string $title;

    public function getId(): int
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }
}
```

Now we can implement an Event Subscriber to listen to the accept-language header on each request:

```php
<?php

declare(strict_types=1);

namespace App\Event\Subscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class LocaleSubscriber implements EventSubscriberInterface
{
    /**
     * @return array<string, array<int[]|string[]>>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => [['onKernelRequest', 20]],
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();
        $acceptLanguage = $request->headers->get('accept-language');
        if (empty($acceptLanguage)) {
            return;
        }

        $arr = HeaderUtils::split($acceptLanguage, ',;');
        if (empty($arr[0][0])) {
            return;
        }

        // Symfony expects underscore instead of dash in locale
        $locale = str_replace('-', '_', $arr[0][0]);

        $request->setLocale($locale);
    }
}
```

Now we can make a request to `/api/documents` do not forget to add the `Accept-Language` header value, in this case it's set to 'cz'. 
When you change the `Accept-Language` header value, notice the title change language.

```json
{
  "@context": "/api/contexts/Document",
  "@id": "/api/documents",
  "@type": "hydra:Collection",
  "hydra:member": [
    {
      "@id": "/api/documents/1",
      "@type": "Document",
      "id": 1,
      "newTranslations": [],
      "currentLocale": "cz",
      "defaultLocale": "en",
      "title": "příkladová data",
      "translationEntityClass": "App\\Entity\\DocumentTranslation"
    }
  ]
}
```

That's it!
