<?php

declare(strict_types=1);

namespace Tests\Knp\DoctrineBehaviors\ORM;

use BehaviorFixtures\ORM\SluggableEntity;
use DateTime;
use Doctrine\Common\EventManager;
use Knp\DoctrineBehaviors\Model\Sluggable\Sluggable;
use Knp\DoctrineBehaviors\ORM\Sluggable\SluggableSubscriber;
use Knp\DoctrineBehaviors\Reflection\ClassAnalyzer;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/EntityManagerProvider.php';

class SluggableTest extends TestCase
{
    use EntityManagerProvider;

    public function testSlugLoading(): void
    {
        $entityManager = $this->getEntityManager();

        $entity = new SluggableEntity();

        $expected = 'the-name';

        $entity->setName('The name');

        $entityManager->persist($entity);
        $entityManager->flush();

        $this->assertNotNull($id = $entity->getId());

        $entityManager->clear();

        $entity = $entityManager->getRepository(SluggableEntity::class)->find($id);

        $this->assertNotNull($entity);
        $this->assertSame($expected, $entity->getSlug());
    }

    public function testNotUpdatedSlug(): void
    {
        $entityManager = $this->getEntityManager();

        $data = [
            [
                'slug' => 'the-name',
                'name' => 'The name',
            ],
            [
                'slug' => 'loic-rene',
                'name' => 'Löic & René',
            ],
            [
                'slug' => 'ivan-ivanovich',
                'name' => 'Иван Иванович',
            ],
            [
                'slug' => 'chateauneuf-du-pape',
                'name' => 'Châteauneuf du Pape',
            ],
            [
                'slug' => 'zlutoucky-kun',
                'name' => 'Žluťoučký kůň',
            ],
        ];

        foreach ($data as $row) {
            $entity = new SluggableEntity();

            $entity->setName($row['name']);

            $entityManager->persist($entity);
            $entityManager->flush();

            $entity->setDate(new DateTime());

            $entityManager->persist($entity);
            $entityManager->flush();

            $this->assertSame($row['slug'], $entity->getSlug());
        }
    }

    public function testUpdatedSlug(): void
    {
        $entityManager = $this->getEntityManager();

        $entity = new SluggableEntity();

        $expected = 'the-name';

        $entity->setName('The name');

        $entityManager->persist($entity);
        $entityManager->flush();

        $this->assertSame($entity->getSlug(), $expected);

        $expected = 'the-name-2';

        $entity->setName('The name 2');

        $entityManager->persist($entity);
        $entityManager->flush();

        $this->assertSame($expected, $entity->getSlug());
    }

    protected function getUsedEntityFixtures()
    {
        return [SluggableEntity::class];
    }

    protected function getEventManager(): EventManager
    {
        $eventManager = new EventManager();

        $eventManager->addEventSubscriber(new SluggableSubscriber(new ClassAnalyzer(), false, Sluggable::class));

        return $eventManager;
    }
}
