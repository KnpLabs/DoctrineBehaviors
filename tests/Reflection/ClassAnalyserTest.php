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
    public function testItShouldTestIfObjectUseTrait(): void
    {
        $analyser = new ClassAnalyzer();

        $object = new DeletableEntity();

        $use = $analyser->hasTrait(new ReflectionClass($object), SoftDeletable::class, false);

        $this->assertTrue($use);
    }

    public function testItShouldTestIfObjectDontUseTrait(): void
    {
        $analyser = new ClassAnalyzer();

        $object = new DeletableEntity();

        $use = $analyser->hasTrait(new ReflectionClass($object), Blameable::class, false);

        $this->assertFalse($use);
    }

    public function testItShouldTestIfObjectOrHisParentClassesUseTrait(): void
    {
        $analyser = new ClassAnalyzer();

        $object = new DeletableEntityInherit();

        $use = $analyser->hasTrait(new ReflectionClass($object), SoftDeletable::class, false);

        $this->assertFalse($use);

        $useInherit = $analyser->hasTrait(new ReflectionClass($object), SoftDeletable::class, true);

        $this->assertTrue($useInherit);
    }

    public function testItShouldTestIfObjectHasAMethod(): void
    {
        $analyser = new ClassAnalyzer();

        $object = new GeocodableEntity();

        $use = $analyser->hasMethod(new ReflectionClass($object), 'getLocation');

        $this->assertTrue($use);
    }

    public function testItShouldTestIfObjectDontHasAMethod(): void
    {
        $analyser = new ClassAnalyzer();

        $object = new DeletableEntity();

        $use = $analyser->hasMethod(new ReflectionClass($object), 'getLocation');

        $this->assertFalse($use);
    }

    public function testItShouldTestIfObjectHasAProperty(): void
    {
        $analyser = new ClassAnalyzer();

        $object = new TranslatableEntity();

        $use = $analyser->hasProperty(new ReflectionClass($object), 'translations');

        $this->assertTrue($use);
    }

    public function testItShouldTestIfObjectDontHasAProperty(): void
    {
        $analyser = new ClassAnalyzer();

        $object = new DeletableEntity();

        $use = $analyser->hasProperty(new ReflectionClass($object), 'translations');

        $this->assertFalse($use);
    }

    public function testItShouldTestIfObjectOrHisParentClassesHasAProperty(): void
    {
        $analyser = new ClassAnalyzer();

        $object = new DeletableEntityInherit();

        $use = $analyser->hasProperty(new ReflectionClass($object), 'deletedAt');

        $this->assertTrue($use);
    }
}
