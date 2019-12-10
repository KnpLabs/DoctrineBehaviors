<?php

declare(strict_types=1);

namespace Tests\Knp\DoctrineBehaviors\ORM\Tree;

use BehaviorFixtures\ORM\TreeNodeEntity;
use Doctrine\Common\EventManager;
use Knp\DoctrineBehaviors\Model\Tree\NodeInterface;
use Knp\DoctrineBehaviors\ORM\Tree\TreeSubscriber;
use Knp\DoctrineBehaviors\Reflection\ClassAnalyzer;
use Tests\Knp\DoctrineBehaviors\ORM\EntityManagerProvider;

require_once __DIR__ . '/../EntityManagerProvider.php';

class NodeTest extends \PHPUnit\Framework\TestCase
{
    use EntityManagerProvider;

    public function testBuildTree(): void
    {
        $root = $this->buildNode(['setMaterializedPath' => '', 'setName' => 'root', 'setId' => 1]);
        $flatTree = [
            $this->buildNode(['setMaterializedPath' => '/1', 'setName' => 'Villes', 'setId' => 2]),
            $this->buildNode(['setMaterializedPath' => '/1/2', 'setName' => 'Nantes', 'setId' => 3]),
            $this->buildNode(['setMaterializedPath' => '/1/2/3', 'setName' => 'Nantes Est', 'setId' => 4]),
            $this->buildNode(['setMaterializedPath' => '/1/2/3', 'setName' => 'Nantes Nord', 'setId' => 5]),
            $this->buildNode(['setMaterializedPath' => '/1/2/3/5', 'setName' => 'St-Mihiel', 'setId' => 6]),
        ];

        $root->buildTree($flatTree);
        $this->assertCount(1, $root->getChildNodes());

        $this->assertCount(1, $root->getChildNodes()->first()->getChildNodes());
        $this->assertCount(2, $root->getChildNodes()->first()->getChildNodes()->first()->getChildNodes());

        $this->assertSame(1, $root->getNodeLevel());
        $this->assertSame(4, $root->getChildNodes()->first()->getChildNodes()->first()->getChildNodes()->first()->getNodeLevel());
    }

    public function testIsRoot(): void
    {
        $tree = $this->buildTree();

        $this->assertTrue($tree->getRootNode()->isRootNode());
        $this->assertTrue($tree->isRootNode());
    }

    public function testIsLeaf(): void
    {
        $tree = $this->buildTree();

        $this->assertTrue($tree[0][0][0]->isLeafNode());
        $this->assertTrue($tree[1]->isLeafNode());
    }

    public function testGetRoot(): void
    {
        $tree = $this->buildTree();

        $this->assertSame($tree, $tree->getRootNode());
        $this->assertNull($tree->getRootNode()->getParentNode());

        $this->assertSame($tree, $tree->getChildNodes()->get(0)->getChildNodes()->get(0)->getRootNode());
    }

    public function provideRootPaths()
    {
        return [
            [$this->buildNode(['setMaterializedPath' => '/0/1']), '/0'],
            [$this->buildNode(['setMaterializedPath' => '/']), '/'],
            [$this->buildNode(['setMaterializedPath' => '']), '/'],
            [$this->buildNode(['setMaterializedPath' => '/test']), '/test'],
            [$this->buildNode(['setMaterializedPath' => '/0/1/2/3/4/5/6/']), '/0'],
        ];
    }

    /**
     * @dataProvider provideisChildNodeOf
     **/
    public function testTestisChildNodeOf(NodeInterface $child, NodeInterface $parent, $expected): void
    {
        $this->assertSame($expected, $child->isChildNodeOf($parent));
    }

    public function provideisChildNodeOf()
    {
        $tree = $this->buildTree();

        return [
            [$tree[0][0],  $tree[0],  true],
            [$tree[0][0][0],  $tree[0][0],  true],
            [$tree[0][0][0],  $tree[0],  false],
            [$tree[0][0][0],  $tree[0][0][0],  false],
        ];
    }

    public function provideToArray()
    {
        return [
            1 =>
            [
                'node' => '',
                'children' =>
                [
                    2 =>
                    [
                        'node' => '',
                        'children' =>
                        [
                            4 =>
                            [
                                'node' => '',
                                'children' =>
                                [
                                    5 =>
                                    [
                                        'node' => '',
                                        'children' =>
                                        [
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    3 =>
                    [
                        'node' => '',
                        'children' =>
                        [
                        ],
                    ],
                ],
            ],
        ];
    }

    public function testToArray(): void
    {
        $expected = $this->provideToArray();
        $tree = $this->buildTree();
        $this->assertSame($expected, $tree->toArray());
    }

    public function testToJson(): void
    {
        $expected = $this->provideToArray();
        $tree = $this->buildTree();
        $this->assertSame(json_encode($expected), $tree->toJson());
    }

    public function testToFlatArray(): void
    {
        $tree = $this->buildTree();

        $expected = [
            1 => '',
            2 => '----',
            4 => '------',
            5 => '--------',
            3 => '----',
        ];

        $this->assertSame($expected, $tree->toFlatArray());
    }

    public function testArrayAccess(): void
    {
        $tree = $this->buildTree();

        $tree[] = $this->buildNode(['setId' => 45]);
        $tree[] = $this->buildNode(['setId' => 46]);
        $this->assertSame(4, $tree->getChildNodes()->count());

        $tree[2][] = $this->buildNode(['setId' => 47]);
        $tree[2][] = $this->buildNode(['setId' => 48]);
        $this->assertSame(2, $tree[2]->getChildNodes()->count());

        $this->assertTrue(isset($tree[2][1]));
        $this->assertFalse(isset($tree[2][1][2]));

        unset($tree[2][1]);
        $this->assertFalse(isset($tree[2][1]));
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage You must provide an id for this node if you want it to be part of a tree.
     **/
    public function testTestsetChildNodeOfWithoutId(): void
    {
        $this->buildNode(['setMaterializedPath' => '/0/1'])->setChildNodeOf($this->buildNode(['setMaterializedPath' => '/0']));
    }

    public function testChildrenCount(): void
    {
        $tree = $this->buildTree();

        $this->assertSame(2, $tree->getChildNodes()->count());
        $this->assertSame(1, $tree->getChildNodes()->get(0)->getChildNodes()->count());
    }

    public function testGetPath(): void
    {
        $tree = $this->buildTree();

        $this->assertSame('/1', $tree->getRealMaterializedPath());
        $this->assertSame('/1/2', $tree->getChildNodes()->get(0)->getRealMaterializedPath());
        $this->assertSame('/1/2/4', $tree->getChildNodes()->get(0)->getChildNodes()->get(0)->getRealMaterializedPath());
        $this->assertSame('/1/2/4/5', $tree->getChildNodes()->get(0)->getChildNodes()->get(0)->getChildNodes()->get(0)->getRealMaterializedPath());

        $childChildItem = $tree->getChildNodes()->get(0)->getChildNodes()->get(0);
        $childChildChildItem = $tree->getChildNodes()->get(0)->getChildNodes()->get(0)->getChildNodes()->get(0);
        $childChildItem->setChildNodeOf($tree);
        $this->assertSame('/1/4', $childChildItem->getRealMaterializedPath(), 'The path has been updated fo the node');
        $this->assertSame('/1/4/5', $childChildChildItem->getRealMaterializedPath(), 'The path has been updated fo the node and all its descendants');
        $this->assertTrue($tree->getChildNodes()->contains($childChildItem), 'The children collection has been updated to reference the moved node');
    }

    public function testMoveChildren(): void
    {
        $tree = $this->buildTree();

        $childChildItem = $tree->getChildNodes()->get(0)->getChildNodes()->get(0);
        $childChildChildItem = $tree->getChildNodes()->get(0)->getChildNodes()->get(0)->getChildNodes()->get(0);
        $this->assertSame(4, $childChildChildItem->getNodeLevel(), 'The level is well calcuated');

        $childChildItem->setChildNodeOf($tree);
        $this->assertSame('/1/4', $childChildItem->getRealMaterializedPath(), 'The path has been updated fo the node');
        $this->assertSame('/1/4/5', $childChildChildItem->getRealMaterializedPath(), 'The path has been updated fo the node and all its descendants');
        $this->assertTrue($tree->getChildNodes()->contains($childChildItem), 'The children collection has been updated to reference the moved node');

        $this->assertSame(3, $childChildChildItem->getNodeLevel(), 'The level has been updated');
    }

    public function testGetTree(): void
    {
        $em = $this->getEntityManager();
        $repo = $em->getRepository('BehaviorFixtures\ORM\TreeNodeEntity');

        $entity = new TreeNodeEntity(1);
        $entity[0] = new TreeNodeEntity(2);
        $entity[0][0] = new TreeNodeEntity(3);

        $em->persist($entity);
        $em->persist($entity[0]);
        $em->persist($entity[0][0]);
        $em->flush();

        $root = $repo->getTree();

        $this->assertSame($root[0][0], $entity[0][0]);
    }

    protected function getUsedEntityFixtures()
    {
        return [
            'BehaviorFixtures\\ORM\\TreeNodeEntity',
        ];
    }

    protected function getEventManager()
    {
        $em = new EventManager();

        $em->addEventSubscriber(
            new TreeSubscriber(
                new ClassAnalyzer(),
                false,
                'Knp\DoctrineBehaviors\Model\Tree\Node'
            )
        );

        return $em;
    }

    protected function buildNode(array $values = [])
    {
        $node = new TreeNodeEntity();
        foreach ($values as $method => $value) {
            $node->{$method}($value);
        }

        return $node;
    }

    private function buildTree()
    {
        $item = $this->buildNode();
        $item->setMaterializedPath('');
        $item->setId(1);

        $childItem = $this->buildNode();
        $childItem->setMaterializedPath('/1');
        $childItem->setId(2);
        $childItem->setChildNodeOf($item);

        $secondChildItem = $this->buildNode();
        $secondChildItem->setMaterializedPath('/1');
        $secondChildItem->setId(3);
        $secondChildItem->setChildNodeOf($item);

        $childChildItem = $this->buildNode();
        $childChildItem->setId(4);
        $childChildItem->setMaterializedPath('/1/2');
        $childChildItem->setChildNodeOf($childItem);

        $childChildChildItem = $this->buildNode();
        $childChildChildItem->setId(5);
        $childChildChildItem->setMaterializedPath('/1/2/4');
        $childChildChildItem->setChildNodeOf($childChildItem);

        return $item;
    }
}
