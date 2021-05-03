<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Versionable\Tests;

use Knp\DoctrineBehaviors\Tests\AbstractBehaviorTestCase;
use Knp\DoctrineBehaviors\Versionable\Entity\ResourceVersion;
use Knp\DoctrineBehaviors\Versionable\Tests\Entity\BlogPost;
use Knp\DoctrineBehaviors\Versionable\VersionManager;

final class VersionableTest extends AbstractBehaviorTestCase
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

    public function testMakeVersionSnapshot(): void
    {
        /** @var BlogPost $blogPost */
        $blogPost = $this->entityManager->find(BlogPost::class, 1);
        $blogPost->changeContent('Oerr!');

        $this->entityManager->persist($blogPost);
        $this->entityManager->flush();

        $versions = $this->versionManager->getVersions($blogPost);

        $this->assertCount(1, $versions);

        $firstVersion = $versions[1];
        $this->assertInstanceOf(ResourceVersion::class, $firstVersion);

        /** @var ResourceVersion $firstVersion */
        $this->assertSame(BlogPost::class, $firstVersion->getResourceName());
        $this->assertSame('Barbaz', $firstVersion->getVersionedColumn('content'));

        $this->assertSame(1, $firstVersion->getVersion());
    }

    public function testChangeEntityVersion(): void
    {
        /** @var BlogPost $blogPost */
        $blogPost = $this->entityManager->find(BlogPost::class, 1);
        $this->assertSame(1, $blogPost->version);

        $blogPost->changeContent('Different content');
        $this->entityManager->persist($blogPost);
        $this->entityManager->flush();

        $this->assertSame(2, $blogPost->version);
    }

    public function testRevert(): void
    {
        /** @var BlogPost $blogPost */
        $blogPost = $this->entityManager->find(BlogPost::class, 2);

        $this->assertSame('bar', $blogPost->getContent());

        $this->versionManager->revert($blogPost, 1);

        $this->assertSame('foo', $blogPost->getContent());
    }

    protected function provideCustomConfig(): ?string
    {
        return __DIR__ . '/config/versionable_config.php';
    }

    private function loadDatabaseFixtures(): void
    {
        // load fixtures
        $firstBlogPost = new BlogPost(1, 'Barbaz');
        $secondBlogPost = new BlogPost(2, 'bar');

        $this->entityManager->persist($firstBlogPost);
        $this->entityManager->persist($secondBlogPost);

        $versionedData = [
            'content' => 'foo',
        ];

        $resourceVersion = new ResourceVersion(BlogPost::class, $secondBlogPost->id, $versionedData, 1);

        $this->entityManager->persist($resourceVersion);
        $this->entityManager->flush();
    }
}
