<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Tests\ORM;

use Doctrine\ORM\EntityRepository;
use Knp\DoctrineBehaviors\Contract\Entity\TranslatableInterface;
use Knp\DoctrineBehaviors\Tests\AbstractBehaviorTestCase;
use Knp\DoctrineBehaviors\Tests\Fixtures\ORM\Translatable\ExtendedTranslatableEntity;

final class TranslatableInheritanceTest extends AbstractBehaviorTestCase
{
    public function testShouldPersistTranslationsWithInheritance(): void
    {
        $entity = new ExtendedTranslatableEntity();
        $entity->translate('fr')->setTitle('fabuleux');
        $entity->translate('fr')->setExtendedTitle('fabuleux');

        $entity->translate('en')->setTitle('awesome');
        $entity->translate('en')->setExtendedTitle('awesome');

        $entity->translate('ru')->setTitle('удивительный');
        $entity->translate('ru')->setExtendedTitle('удивительный');
        $entity->mergeNewTranslations();

        $this->entityManager->persist($entity);
        $this->entityManager->flush();
        $id = $entity->getId();

        $this->entityManager->clear();

        /** @var EntityRepository $translatableRepository */
        $translatableRepository = $this->entityManager->getRepository(ExtendedTranslatableEntity::class);

        /** @var TranslatableInterface $entity */
        $entity = $translatableRepository->find($id);

        $this->assertSame('fabuleux', $entity->translate('fr')->getTitle());

        $this->assertSame('fabuleux', $entity->translate('fr')->getExtendedTitle());

        $this->assertSame('awesome', $entity->translate('en')->getTitle());

        $this->assertSame('awesome', $entity->translate('en')->getExtendedTitle());

        $this->assertSame('удивительный', $entity->translate('ru')->getTitle());

        $this->assertSame('удивительный', $entity->translate('ru')->getExtendedTitle());
    }
}
