<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Tests\ORM\Translatable;

use Doctrine\Persistence\ObjectRepository;
use Knp\DoctrineBehaviors\Tests\AbstractBehaviorTestCase;
use Knp\DoctrineBehaviors\Tests\Fixtures\Entity\Translatable\ExtendedTranslatableEntity;
use Knp\DoctrineBehaviors\Tests\Fixtures\Entity\Translatable\ExtendedTranslatableEntityWithJoinTableInheritance;

final class TranslatableJoinTableInheritanceTest extends AbstractBehaviorTestCase
{
    /**
     * @var ObjectRepository<ExtendedTranslatableEntityWithJoinTableInheritance>
     */
    private ObjectRepository $objectRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->objectRepository = $this->entityManager->getRepository(
            ExtendedTranslatableEntityWithJoinTableInheritance::class
        );
    }

    public function testShouldPersistTranslationsWithJoinTableInheritance(): void
    {
        $entity = new ExtendedTranslatableEntityWithJoinTableInheritance();
        $entity->setUntranslatedField('untranslated');
        $entity->translate('fr')
            ->setTitle('fabuleux');
        $entity->translate('en')
            ->setTitle('awesome');
        $entity->translate('ru')
            ->setTitle('удивительный');

        $entity->mergeNewTranslations();

        $this->entityManager->persist($entity);
        $this->entityManager->flush();

        $id = $entity->getId();
        $this->entityManager->clear();

        /** @var ExtendedTranslatableEntityWithJoinTableInheritance $entity */
        $entity = $this->objectRepository->find($id);
        $this->assertSame('untranslated', $entity->getUntranslatedField());
        $this->assertSame('fabuleux', $entity->translate('fr')->getTitle());
        $this->assertSame('awesome', $entity->translate('en')->getTitle());
        $this->assertSame('удивительный', $entity->translate('ru')->getTitle());
    }
}
