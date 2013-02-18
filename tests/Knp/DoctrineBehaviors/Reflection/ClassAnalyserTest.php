<?php

namespace Tests\Knp\DoctrineBehaviors\Reflection;

use Knp\DoctrineBehaviors\Reflection\ClassAnalyzer;
use BehaviorFixtures\ORM\DeletableEntity;
use BehaviorFixtures\ORM\DeletableEntityInherit;
use BehaviorFixtures\ORM\GeocodableEntity;
use BehaviorFixtures\ORM\TranslatableEntity;

class ClassAnalyserTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @test
     */
    public function it_should_test_if_object_use_trait () {

        $analyser = new ClassAnalyzer;

        $object = new DeletableEntity;

        $use = $analyser->isObjectUseTrait(
            new \ReflectionClass($object), 
            'Knp\DoctrineBehaviors\Model\SoftDeletable\SoftDeletable', 
            false
        );

        $this->assertTrue($use);
    }

    /**
     * @test
     */
    public function it_should_test_if_object_dont_use_trait () {

        $analyser = new ClassAnalyzer;

        $object = new DeletableEntity;

        $use = $analyser->isObjectUseTrait(
            new \ReflectionClass($object), 
            'Knp\DoctrineBehaviors\Model\Blameable\Blameable', 
            false
        );

        $this->assertFalse($use);
    }

    /**
     * @test
     */
    public function it_should_test_if_object_or_his_parent_classes_use_trait () {

        $analyser = new ClassAnalyzer;

        $object = new DeletableEntityInherit;

        $use = $analyser->isObjectUseTrait(
            new \ReflectionClass($object), 
            'Knp\DoctrineBehaviors\Model\SoftDeletable\SoftDeletable', 
            false
        );

        $this->assertFalse($use);

        $useInherit = $analyser->isObjectUseTrait(
            new \ReflectionClass($object), 
            'Knp\DoctrineBehaviors\Model\SoftDeletable\SoftDeletable', 
            true
        );

        $this->assertTrue($useInherit);
    }

    /**
     * @test
     */
    public function it_should_test_if_object_has_a_method () {

        $analyser = new ClassAnalyzer;

        $object = new GeocodableEntity;

        $use = $analyser->isObjectHasMethod(
            new \ReflectionClass($object), 
            'getLocation'
        );

        $this->assertTrue($use);
    }

    /**
     * @test
     */
    public function it_should_test_if_object_dont_has_a_method () {

        $analyser = new ClassAnalyzer;

        $object = new DeletableEntity;

        $use = $analyser->isObjectHasMethod(
            new \ReflectionClass($object), 
            'getLocation'
        );

        $this->assertFalse($use);
    }

    /**
     * @test
     */
    public function it_should_test_if_object_has_a_property () {

        $analyser = new ClassAnalyzer;

        $object = new TranslatableEntity;

        $use = $analyser->isObjectHasProperty(
            new \ReflectionClass($object), 
            'translations', 
            false
        );

        $this->assertTrue($use);
    }

    /**
     * @test
     */
    public function it_should_test_if_object_dont_has_a_property () {

        $analyser = new ClassAnalyzer;

        $object = new DeletableEntity;

        $use = $analyser->isObjectHasProperty(
            new \ReflectionClass($object), 
            'translations', 
            false
        );

        $this->assertFalse($use);
    }

    /**
     * @test
     */
    public function it_should_test_if_object_or_his_perent_classes_has_a_property () {

        $analyser = new ClassAnalyzer;

        $object = new DeletableEntityInherit;

        $use = $analyser->isObjectHasProperty(
            new \ReflectionClass($object), 
            'deletedAt', 
            true
        );

        $this->assertTrue($use);
    }

}
