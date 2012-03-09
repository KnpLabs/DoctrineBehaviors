<?php

namespace Tests\Knp\DoctrineBehaviors\ORM\Tree;

use Knp\DoctrineBehaviors\ORM\Tree\NodeInterface;

require_once 'EntityManagerProvider.php';

class NodeTest extends \PHPUnit_Framework_TestCase
{
    use EntityManagerProvider;

    protected function getUsedEntityFixtures()
    {
        return array(
            'BehaviorFixtures\\ORM\\TreeNodeEntity'
        );
    }


    protected function buildNode(array $values = array())
    {
        $node = new ;
        foreach($values as $method => $value) {
            $node->$method($value);
        }

        return $node;
    }

    private function buildTree()
    {
        $item = $this->buildNode();
        $item->setPath('/1');
        $item->setId(1);

        $childItem = $this->buildNode();
        $childItem->setPath('/1/2');
        $childItem->setId(2);
        $childItem->setChildOf($item);

        $secondChildItem = $this->buildNode();
        $secondChildItem->setPath('/1/3');
        $secondChildItem->setId(3);
        $secondChildItem->setChildOf($item);

        $childChildItem = $this->buildNode();
        $childChildItem->setId(4);
        $childChildItem->setPath('/1/2/4');
        $childChildItem->setChildOf($childItem);

        $childChildChildItem = $this->buildNode();
        $childChildChildItem->setId(5);
        $childChildChildItem->setPath('/1/2/4/5');
        $childChildChildItem->setChildOf($childChildItem);

        return $item;
    }

    public function testBuildTree()
    {
        $root = $this->buildNode(array('setPath' => '/0'     , 'setName' => 'root'        , 'setId' => 0));
        $flatTree = new \ArrayObject(array(
            $this->buildNode(array('setPath' => '/0/1'       , 'setName' => 'Villes'      , 'setId' => 1)) ,
            $this->buildNode(array('setPath' => '/0/1/2'     , 'setName' => 'Nantes'      , 'setId' => 2)) ,
            $this->buildNode(array('setPath' => '/0/1/2/3'   , 'setName' => 'Nantes Est'  , 'setId' => 3)) ,
            $this->buildNode(array('setPath' => '/0/1/2/4'   , 'setName' => 'Nantes Nord' , 'setId' => 4)) ,
            $this->buildNode(array('setPath' => '/0/1/2/4/5' , 'setName' => 'St-Mihiel'   , 'setId' => 5)) ,
        ));

        $root->buildTree($flatTree);

        $this->assertEquals(1, $root->getNodeChildren()->count());
        $this->assertEquals(1, $root->getNodeChildren()->get(0)->getNodeChildren()->count());
        $this->assertEquals(2, $root->getNodeChildren()->get(0)->getNodeChildren()->get(0)->getNodeChildren()->count());

        $this->assertEquals(1, $root->getLevel());
        $this->assertEquals(4, $root->getNodeChildren()->get(0)->getNodeChildren()->get(0)->getNodeChildren()->get(0)->getLevel());
    }

    public function testGetRoot()
    {
        $tree = $this->buildTree();

        $this->assertEquals($tree, $tree->getRoot());
        $this->assertNull($tree->getRoot()->getParent());

        $this->assertEquals($tree, $tree->getNodeChildren()->get(0)->getNodeChildren()->get(0)->getRoot());
    }

    /**
     * @dataProvider provideRootPaths
     **/
    public function testGetRootPath(NodeInterface $node, $expected)
    {
        $this->assertEquals($expected, $node->getRootPath());
    }

    public function provideRootPaths()
    {
        return array(
            array($this->buildNode(array('setPath' => '/0/1'))            , '/0'),
            array($this->buildNode(array('setPath' => '/'))               , '/'),
            array($this->buildNode(array('setPath' => ''))                , '/'),
            array($this->buildNode(array('setPath' => '/test'))           , '/test'),
            array($this->buildNode(array('setPath' => '/0/1/2/3/4/5/6/')) , '/0'),
        );
    }

    /**
     * @dataProvider provideIsChildOf
     **/
    public function testIsChildOf(NodeInterface $child, NodeInterface $parent, $expected)
    {
        $this->assertEquals($expected, $child->isChildOf($parent));
    }

    public function provideIsChildOf()
    {
        $tree = $this->buildTree();

        return array(
            array($tree[0][0]    ,  $tree[0]          ,  true),
            array($tree[0][0][0] ,  $tree[0][0]       ,  true),
            array($tree[0][0][0] ,  $tree[0]          ,  false),
            array($tree[0][0][0] ,  $tree[0][0][0]    ,  false),
        );
    }

    public function provideToArray()
    {
        $expected = array (
            1 =>
            array (
                'node' => '',
                'children' =>
                array (
                    2 =>
                    array (
                        'node' => '',
                        'children' =>
                        array (
                            4 =>
                            array (
                                'node' => '',
                                'children' =>
                                array (
                                    5 =>
                                    array (
                                        'node' => '',
                                        'children' =>
                                        array (
                                        ),
                                    ),
                                ),
                            ),
                        ),
                    ),
                    3 =>
                    array (
                        'node' => '',
                        'children' =>
                        array (
                        ),
                    ),
                ),
            ),
        );

        return $expected;
    }

    public function testToArray()
    {
        $expected = $this->provideToArray();
        $tree = $this->buildTree();
        $this->assertEquals($expected, $tree->toArray());
    }

    public function testToJson()
    {
        $expected = $this->provideToArray();
        $tree = $this->buildTree();
        $this->assertEquals(json_encode($expected), $tree->toJson());
    }

    public function testToFlatArray()
    {
        $tree = $this->buildTree();

        $expected = array (
          1 => '',
          2 => '----',
          4 => '------',
          5 => '--------',
          3 => '----',
        );

        $this->assertEquals($expected, $tree->toFlatArray());
    }

    public function testArrayAccess()
    {
        $tree = $this->buildTree();

        $tree[] = $this->buildNode(array('setId' => 45));
        $tree[] = $this->buildNode(array('setId' => 46));
        $this->assertEquals(4, $tree->getNodeChildren()->count());

        $tree[2][] = $this->buildNode(array('setId' => 47));
        $tree[2][] = $this->buildNode(array('setId' => 48));
        $this->assertEquals(2, $tree[2]->getNodeChildren()->count());

        $this->assertTrue(isset($tree[2][1]));
        $this->assertFalse(isset($tree[2][1][2]));

        unset($tree[2][1]);
        $this->assertFalse(isset($tree[2][1]));
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage You must provide an id for this node if you want it to be part of a tree.
     **/
    public function testSetChildOfWithoutId()
    {
        $this->buildNode(array('setPath' => '/0/1'))->setChildOf($this->buildNode(array('setPath' => '/0')));
    }

    public function testChildrenCount()
    {
        $tree = $this->buildTree();

        $this->assertEquals(2, $tree->getNodeChildren()->count());
        $this->assertEquals(1, $tree->getNodeChildren()->get(0)->getNodeChildren()->count());
    }

    public function testGetPath()
    {
        $tree = $this->buildTree();

        $this->assertEquals('/1', $tree->getPath());
        $this->assertEquals('/1/2', $tree->getNodeChildren()->get(0)->getPath());
        $this->assertEquals('/1/2/4', $tree->getNodeChildren()->get(0)->getNodeChildren()->get(0)->getPath());
        $this->assertEquals('/1/2/4/5', $tree->getNodeChildren()->get(0)->getNodeChildren()->get(0)->getNodeChildren()->get(0)->getPath());

        $childChildItem = $tree->getNodeChildren()->get(0)->getNodeChildren()->get(0);
        $childChildChildItem = $tree->getNodeChildren()->get(0)->getNodeChildren()->get(0)->getNodeChildren()->get(0);
        $childChildItem->setChildOf($tree);
        $this->assertEquals('/1/4', $childChildItem->getPath(), 'The path has been updated fo the node');
        $this->assertEquals('/1/4/5', $childChildChildItem->getPath(), 'The path has been updated fo the node and all its descendants');
        $this->assertTrue($tree->getNodeChildren()->contains($childChildItem), 'The children collection has been updated to reference the moved node');
    }

    public function testMoveChildren()
    {
        $tree = $this->buildTree();

        $childChildItem = $tree->getNodeChildren()->get(0)->getNodeChildren()->get(0);
        $childChildChildItem = $tree->getNodeChildren()->get(0)->getNodeChildren()->get(0)->getNodeChildren()->get(0);
        $this->assertEquals(4, $childChildChildItem->getLevel(), 'The level is well calcuated');

        $childChildItem->setChildOf($tree);
        $this->assertEquals('/1/4', $childChildItem->getPath(), 'The path has been updated fo the node');
        $this->assertEquals('/1/4/5', $childChildChildItem->getPath(), 'The path has been updated fo the node and all its descendants');
        $this->assertTrue($tree->getNodeChildren()->contains($childChildItem), 'The children collection has been updated to reference the moved node');

        $this->assertEquals(3, $childChildChildItem->getLevel(), 'The level has been updated');
    }
}

