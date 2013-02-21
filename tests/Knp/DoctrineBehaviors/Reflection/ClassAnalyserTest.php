<?php

namespace Tests\Knp\DoctrineBehaviors\Reflection;

use Knp\DoctrineBehaviors\Reflection\ClassAnalyzer;
use BehaviorFixtures\ORM\DefaultDeletableEntity as DeletableEntity;
use BehaviorFixtures\ORM\DeletableEntityInherit;
use BehaviorFixtures\ORM\DefaultGeocodableEntity;
use BehaviorFixtures\ORM\RenamedGeocodableEntity;
use BehaviorFixtures\ORM\DefaultTranslatableEntity as TranslatableEntity;
use BehaviorFixtures\ORM\DefaultTranslatableEntityTranslation as TranslatableEntityTranslation;

class ClassAnalyserTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @test
     */
    public function it_should_test_if_object_use_trait ()
    {

        $analyser = new ClassAnalyzer;

        $object = new DeletableEntity;

        $use = $analyser->hasTrait(
            new \ReflectionClass($object), 
            'Knp\DoctrineBehaviors\Model\SoftDeletable\SoftDeletable', 
            false
        );

        $this->assertTrue($use);
    }

    /**
     * @test
     */
    public function it_should_test_if_object_dont_use_trait ()
    {

        $analyser = new ClassAnalyzer;

        $object = new DeletableEntity;

        $use = $analyser->hasTrait(
            new \ReflectionClass($object), 
            'Knp\DoctrineBehaviors\Model\Blameable\Blameable', 
            false
        );

        $this->assertFalse($use);
    }

    /**
     * @test
     */
    public function it_should_test_if_object_or_his_parent_classes_use_trait ()
    {

        $analyser = new ClassAnalyzer;

        $object = new DeletableEntityInherit;

        $use = $analyser->hasTrait(
            new \ReflectionClass($object), 
            'Knp\DoctrineBehaviors\Model\SoftDeletable\SoftDeletable', 
            false
        );

        $this->assertFalse($use);

        $useInherit = $analyser->hasTrait(
            new \ReflectionClass($object), 
            'Knp\DoctrineBehaviors\Model\SoftDeletable\SoftDeletable', 
            true
        );

        $this->assertTrue($useInherit);
    }

    /**
     * @test
     */
    public function it_should_test_if_object_use_trait_used_by_a_trait ()
    {

        $analyser = new ClassAnalyzer;

        $object = new TranslatableEntityTranslation;

        $useTranslation = $analyser->hasTrait(
            new \ReflectionClass($object), 
            'Knp\DoctrineBehaviors\Model\Translatable\Translation', 
            true
        );

        $useTranslationProperties = $analyser->hasTrait(
            new \ReflectionClass($object), 
            'Knp\DoctrineBehaviors\Model\Translatable\TranslationProperties', 
            true
        );

        $this->assertTrue($useTranslation);
        $this->assertFalse($useTranslationProperties);
    }

    /**
     * @test
     */
    public function it_should_test_if_object_has_a_method ()
    {

        $analyser = new ClassAnalyzer;

        $object = new DefaultGeocodableEntity;

        $use = $analyser->hasMethod(
            new \ReflectionClass($object), 
            'getLocation'
        );

        $this->assertTrue($use);
    }

    /**
     * @test
     */
    public function it_should_test_if_object_dont_has_a_method ()
    {

        $analyser = new ClassAnalyzer;

        $object = new DeletableEntity;

        $use = $analyser->hasMethod(
            new \ReflectionClass($object), 
            'getLocation'
        );

        $this->assertFalse($use);
    }

    /**
     * @test
     */
    public function it_should_test_if_object_has_a_property ()
    {

        $analyser = new ClassAnalyzer;

        $object = new TranslatableEntity;

        $use = $analyser->hasProperty(
            new \ReflectionClass($object), 
            'translations'
        );

        $this->assertTrue($use);
    }

    /**
     * @test
     */
    public function it_should_test_if_object_dont_has_a_property ()
    {

        $analyser = new ClassAnalyzer;

        $object = new DeletableEntity;

        $use = $analyser->hasProperty(
            new \ReflectionClass($object), 
            'translations'
        );

        $this->assertFalse($use);
    }

    /**
     * @test
     */
    public function it_should_test_if_object_or_his_parent_classes_has_a_property ()
    {

        $analyser = new ClassAnalyzer;

        $object = new DeletableEntityInherit;

        $use = $analyser->hasProperty(
            new \ReflectionClass($object), 
            'deletedAt'
        );

        $this->assertTrue($use);
    }

    /**
     * @test
     */
    public function it_should_return_renamed_trait_method()
    {

        $analyzer = new ClassAnalyzer;

        $object = new RenamedGeocodableEntity;

        $name = $analyzer->getRealTraitMethodName(
            new \ReflectionClass($object),
            'Knp\DoctrineBehaviors\Model\Geocodable\Geocodable',
            'getLocation'
        );

        $this->assertEquals($name, 'getTraitLocation');

        $name2 = $analyzer->getRealTraitMethodName(
            new \ReflectionClass($object),
            'Knp\DoctrineBehaviors\Model\Geocodable\Geocodable',
            'setLocation'
        );

        $this->assertEquals($name2, 'setTraitLocation');

    }

    /**
     * @test
     */
    public function it_should_return_trait_method_when_not_renamed()
    {

        $analyzer = new ClassAnalyzer;

        $object = new DeletableEntity;

        $name = $analyzer->getRealTraitMethodName(
            new \ReflectionClass($object),
            'Knp\DoctrineBehaviors\Model\SoftDeletable\SoftDeletable',
            'restore'
        );

        $this->assertEquals($name, 'restore');

    }

    /**
     * @test
     */
    public function it_should_return_null_when_object_dont_use_trait()
    {

        $analyzer = new ClassAnalyzer;

        $object = new DefaultGeocodableEntity;

        $name = $analyzer->getRealTraitMethodName(
            new \ReflectionClass($object),
            'Knp\DoctrineBehaviors\Model\SoftDeletable\SoftDeletable',
            'restore'
        );

        $this->assertNull($name);

    }

    /**
     * @test
     */
    public function it_should_return_null_when_the_method_dont_exists()
    {

        $analyzer = new ClassAnalyzer;

        $object = new DeletableEntity;

        $name = $analyzer->getRealTraitMethodName(
            new \ReflectionClass($object),
            'Knp\DoctrineBehaviors\Model\SoftDeletable\SoftDeletable',
            'getLocation'
        );

        $this->assertNull($name);

    }

}
