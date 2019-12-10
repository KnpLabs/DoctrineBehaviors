<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Tests\Reflection;

use Knp\DoctrineBehaviors\Model\Blameable\Blameable;
use Knp\DoctrineBehaviors\Model\SoftDeletable\SoftDeletable;
use Knp\DoctrineBehaviors\Reflection\ClassAnalyzer;
use Knp\DoctrineBehaviors\Tests\Fixtures\ORM\DeletableEntity;
use Knp\DoctrineBehaviors\Tests\Fixtures\ORM\DeletableEntityInherit;
use Knp\DoctrineBehaviors\Tests\Fixtures\ORM\GeocodableEntity;
use Knp\DoctrineBehaviors\Tests\Fixtures\ORM\TranslatableEntity;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class ClassAnalyserTest extends TestCase
{
    /**
     * @var ClassAnalyzer
     */
    private $classAnalyzer;

    protected function setUp(): void
    {
        $this->classAnalyzer = new ClassAnalyzer();
    }

    public function testItShouldTestIfObjectUseTrait(): void
    {
        $object = new DeletableEntity();

        $use = $this->classAnalyzer->hasTrait(new ReflectionClass($object), SoftDeletable::class, false);

        $this->assertTrue($use);
    }

    public function testItShouldTestIfObjectDontUseTrait(): void
    {
        $object = new DeletableEntity();

        $use = $this->classAnalyzer->hasTrait(new ReflectionClass($object), Blameable::class, false);

        $this->assertFalse($use);
    }

    public function testItShouldTestIfObjectOrHisParentClassesUseTrait(): void
    {
        $object = new DeletableEntityInherit();

        $use = $this->classAnalyzer->hasTrait(new ReflectionClass($object), SoftDeletable::class, false);

        $this->assertFalse($use);

        $useInherit = $this->classAnalyzer->hasTrait(new ReflectionClass($object), SoftDeletable::class, true);

        $this->assertTrue($useInherit);
    }

    public function testItShouldTestIfObjectHasAMethod(): void
    {
        $object = new GeocodableEntity();

        $use = $this->classAnalyzer->hasMethod(new ReflectionClass($object), 'getLocation');

        $this->assertTrue($use);
    }

    public function testItShouldTestIfObjectDontHasAMethod(): void
    {
        $object = new DeletableEntity();

        $use = $this->classAnalyzer->hasMethod(new ReflectionClass($object), 'getLocation');

        $this->assertFalse($use);
    }

    public function testItShouldTestIfObjectHasAProperty(): void
    {
        $object = new TranslatableEntity();

        $use = $this->classAnalyzer->hasProperty(new ReflectionClass($object), 'translations');

        $this->assertTrue($use);
    }

    public function testItShouldTestIfObjectDontHasAProperty(): void
    {
        $object = new DeletableEntity();

        $use = $this->classAnalyzer->hasProperty(new ReflectionClass($object), 'translations');

        $this->assertFalse($use);
    }

    public function testItShouldTestIfObjectOrHisParentClassesHasAProperty(): void
    {
        $object = new DeletableEntityInherit();

        $use = $this->classAnalyzer->hasProperty(new ReflectionClass($object), 'deletedAt');

        $this->assertTrue($use);
    }
}
