<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Versionable\Tests;

use Knp\DoctrineBehaviors\Tests\AbstractBehaviorTestCase;
use Knp\DoctrineBehaviors\Versionable\Exception\MissingColumnException;
use Knp\DoctrineBehaviors\Versionable\Exception\VersionableException;
use Knp\DoctrineBehaviors\Versionable\Tests\Entity\BlogPost;
use Knp\DoctrineBehaviors\Versionable\Tests\Entity\UnversionedVideo;
use Knp\DoctrineBehaviors\Versionable\VersionManager;

final class VersionableErrorTest extends AbstractBehaviorTestCase
{
    /**
     * @var VersionManager
     */
    private $versionManager;

    protected function setUp(): void
    {
        parent::setUp();

        $this->versionManager = $this->getService(VersionManager::class);

        $this->loadDatabaseFixtures();
    }

    public function testFail(): void
    {
        /** @var UnversionedVideo $unversionedVideo */
        $unversionedVideo = $this->entityManager->find(UnversionedVideo::class, 1);

        $this->expectException(VersionableException::class);

        $unversionedVideo->changeTitle('New fast!');
        $this->persistAndFlush($unversionedVideo);
    }

    public function testUnknownColumn(): void
    {
        /** @var BlogPost $blogPost */
        $blogPost = $this->entityManager->find(BlogPost::class, 1);

        // the blog needs to be changed first, we have only 1st version here so nothing to change
        $versions = $this->versionManager->getVersions($blogPost);
        $this->assertEmpty($versions);

        $blogPost->changeContent('New content');
        $this->persistAndFlush($blogPost);

        $versions = $this->versionManager->getVersions($blogPost);
        $this->assertCount(1, $versions);

        $firstVersion = $versions[1];

        $this->expectException(MissingColumnException::class);
        $firstVersion->getVersionedColumn('missing');
    }

    protected function provideCustomConfig(): ?string
    {
        return __DIR__ . '/config/versionable_config.php';
    }

    private function loadDatabaseFixtures(): void
    {
        $unversionedVideo = new UnversionedVideo(1, 'How to Refuctor');
        $this->persistAndFlush($unversionedVideo);

        $blogPost = new BlogPost(1, 'Big content');
        $this->persistAndFlush($blogPost);
    }
}
