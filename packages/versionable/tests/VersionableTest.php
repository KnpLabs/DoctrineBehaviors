<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Versionable\Tests;

use Doctrine\ORM\Tools\SchemaTool;
use Knp\DoctrineBehaviors\Tests\AbstractBehaviorTestCase;
use Knp\DoctrineBehaviors\Versionable\Entity\ResourceVersion;
use Knp\DoctrineBehaviors\Versionable\EventSubscriber\VersionableEventSubscriber;
use Knp\DoctrineBehaviors\Versionable\Tests\Entity\BlogPost;
use Knp\DoctrineBehaviors\Versionable\VersionManager;

final class VersionableTest extends AbstractBehaviorTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $versionableEventSubscriber = new VersionableEventSubscriber($this->entityManager);
        $this->eventManager->addEventSubscriber($versionableEventSubscriber);

        $schemaTool = new SchemaTool($this->entityManager);

        $classMetadatas = [
            $this->entityManager->getClassMetadata(BlogPost::class),
            $this->entityManager->getClassMetadata(ResourceVersion::class),
        ];
        $schemaTool->dropSchema($classMetadatas);
        $schemaTool->createSchema($classMetadatas);

        $this->loadDatabase();
    }

    public function testMakeVersionSnapshot(): void
    {
        /** @var BlogPost $blogPost */
        $blogPost = $this->entityManager->find(BlogPost::class, 1);

        $blogPost->title = 'Foozbaz!';
        $blogPost->content = 'Oerr!';

        $this->entityManager->persist($blogPost);
        $this->entityManager->flush();

        $versionManager = new VersionManager($this->entityManager);
        $versions = $versionManager->getVersions($blogPost);

        $this->assertCount(1, $versions);

        $firstVersion = $versions[1];
        $this->assertInstanceOf(ResourceVersion::class, $firstVersion);

        /** @var ResourceVersion $firstVersion */
        $this->assertSame(BlogPost::class, $firstVersion->getResourceName());
        $this->assertSame('Hello World!', $firstVersion->getVersionedColumn('title'));
        $this->assertSame('Barbaz', $firstVersion->getVersionedColumn('content'));

        $this->assertSame(1, $firstVersion->getVersion());
    }

    public function testRevert(): void
    {
        /** @var BlogPost $blogPost */
        $blogPost = $this->entityManager->find(BlogPost::class, 2);

        $this->assertSame('bar', $blogPost->title);
        $this->assertSame('bar', $blogPost->content);

        $versionManager = new VersionManager($this->entityManager);
        $versionManager->revert($blogPost, 1);

        $this->assertSame('foo', $blogPost->title);
        $this->assertSame('foo', $blogPost->content);
    }

    private function loadDatabase(): void
    {
        // load fixtures
        $firstBlogPost = new BlogPost();
        $firstBlogPost->id = 1;
        $firstBlogPost->title = 'Hello World!';
        $firstBlogPost->content = 'Barbaz';
        $firstBlogPost->version = 1;

        $secondBlogPost = new BlogPost();
        $secondBlogPost->id = 2;
        $secondBlogPost->title = 'bar';
        $secondBlogPost->content = 'bar';
        $secondBlogPost->version = 1;

        $this->entityManager->persist($firstBlogPost);
        $this->entityManager->persist($secondBlogPost);

        $versionedData = [
            'title' => 'foo',
            'content' => 'foo',
        ];

        $resourceVersion = new ResourceVersion(BlogPost::class, 2, $versionedData, 1);
        $this->entityManager->persist($resourceVersion);

        $this->entityManager->flush();
    }
}
