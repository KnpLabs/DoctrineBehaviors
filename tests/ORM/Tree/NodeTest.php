<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Tests\ORM\Tree;

use Iterator;
use Knp\DoctrineBehaviors\Contract\Model\Tree\NodeInterface;
use Knp\DoctrineBehaviors\Tests\AbstractBehaviorTestCase;
use Knp\DoctrineBehaviors\Tests\Fixtures\Entity\TreeNodeEntity;
use Knp\DoctrineBehaviors\Tests\Fixtures\Repository\TreeNodeRepository;
use LogicException;
use Nette\Utils\Json;

final class NodeTest extends AbstractBehaviorTestCase
{
    public function testBuildTree(): void
    {
        $root = $this->buildNode([
            'setMaterializedPath' => '',
            'setName' => 'root',
            'setId' => 1,
        ]);
        $flatTree = [
            $this->buildNode([
                'setMaterializedPath' => '/1',
                'setName' => 'Villes',
                'setId' => 2,
            ]),
            $this->buildNode([
                'setMaterializedPath' => '/1/2',
                'setName' => 'Nantes',
                'setId' => 3,
            ]),
            $this->buildNode([
                'setMaterializedPath' => '/1/2/3',
                'setName' => 'Nantes Est',
                'setId' => 4,
            ]),
            $this->buildNode([
                'setMaterializedPath' => '/1/2/3',
                'setName' => 'Nantes Nord',
                'setId' => 5,
            ]),
            $this->buildNode([
                'setMaterializedPath' => '/1/2/3/5',
                'setName' => 'St-Mihiel',
                'setId' => 6,
            ]),
        ];

        $root->buildTree($flatTree);
        $this->assertCount(1, $root->getChildNodes());

        $this->assertCount(1, $root->getChildNodes()->first()->getChildNodes());
        $this->assertCount(2, $root->getChildNodes()->first()->getChildNodes()->first()->getChildNodes());

        $this->assertSame(1, $root->getNodeLevel());
        $this->assertSame(
            4,
            $root->getChildNodes()->first()->getChildNodes()->first()->getChildNodes()->first()->getNodeLevel()
        );
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

    public function provideRootPaths(): Iterator
    {
        yield [$this->buildNode(['setMaterializedPath' => '/0/1']), '/0'];
        yield [$this->buildNode(['setMaterializedPath' => '/']), '/'];
        yield [$this->buildNode(['setMaterializedPath' => '']), '/'];
        yield [$this->buildNode(['setMaterializedPath' => '/test']), '/test'];
        yield [$this->buildNode(['setMaterializedPath' => '/0/1/2/3/4/5/6/']), '/0'];
    }

    /**
     * @dataProvider provideIsChildNodeOf()
     */
    public function testTestisChildNodeOf(NodeInterface $child, NodeInterface $parent, $expected): void
    {
        $this->assertSame($expected, $child->isChildNodeOf($parent));
    }

    public function provideIsChildNodeOf(): Iterator
    {
        $tree = $this->buildTree();

        yield [$tree[0][0], $tree[0], true];
        yield [$tree[0][0][0], $tree[0][0], true];
        yield [$tree[0][0][0], $tree[0], false];
        yield [$tree[0][0][0], $tree[0][0][0], false];
    }

    public function provideToArray(): array
    {
        return [
            1 => [
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
                                        [],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    3 =>
                    [
                        'node' => '',
                        'children' =>
                        [],
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
        $this->assertSame(Json::encode($expected), $tree->toJson());
    }

    public function testToFlatArray(): void
    {
        $expected = [
            1 => '',
            2 => '----',
            4 => '------',
            5 => '--------',
            3 => '----',
        ];

        $this->assertSame($expected, $this->buildTree()->toFlatArray());
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

    public function testTestsetChildNodeOfWithoutId(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('You must provide an id for this node if you want it to be part of a tree.');

        $this->buildNode(['setMaterializedPath' => '/0/1'])->setChildNodeOf(
            $this->buildNode(['setMaterializedPath' => '/0'])
        );
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
        $this->assertSame(
            '/1/2/4/5',
            $tree->getChildNodes()->get(0)->getChildNodes()->get(0)->getChildNodes()->get(0)->getRealMaterializedPath()
        );

        $childChildItem = $tree->getChildNodes()->get(0)->getChildNodes()->get(0);
        $childChildChildItem = $tree->getChildNodes()->get(0)->getChildNodes()->get(0)->getChildNodes()->get(0);
        $childChildItem->setChildNodeOf($tree);
        $this->assertSame('/1/4', $childChildItem->getRealMaterializedPath(), 'The path has been updated fo the node');
        $this->assertSame(
            '/1/4/5',
            $childChildChildItem->getRealMaterializedPath(),
            'The path has been updated fo the node and all its descendants'
        );
        $this->assertTrue(
            $tree->getChildNodes()->contains($childChildItem),
            'The children collection has been updated to reference the moved node'
        );
    }

    public function testMoveChildren(): void
    {
        $tree = $this->buildTree();

        $childChildItem = $tree->getChildNodes()->get(0)->getChildNodes()->get(0);
        $childChildChildItem = $tree->getChildNodes()->get(0)->getChildNodes()->get(0)->getChildNodes()->get(0);
        $this->assertSame(4, $childChildChildItem->getNodeLevel(), 'The level is well calcuated');

        $childChildItem->setChildNodeOf($tree);
        $this->assertSame('/1/4', $childChildItem->getRealMaterializedPath(), 'The path has been updated fo the node');
        $this->assertSame(
            '/1/4/5',
            $childChildChildItem->getRealMaterializedPath(),
            'The path has been updated fo the node and all its descendants'
        );
        $this->assertTrue(
            $tree->getChildNodes()->contains($childChildItem),
            'The children collection has been updated to reference the moved node'
        );

        $this->assertSame(3, $childChildChildItem->getNodeLevel(), 'The level has been updated');
    }

    public function testGetTree(): void
    {
        /** @var TreeNodeRepository $repository */
        $repository = $this->entityManager->getRepository(TreeNodeEntity::class);

        $entity = new TreeNodeEntity(1);
        $entity[0] = new TreeNodeEntity(2);
        $entity[0][0] = new TreeNodeEntity(3);

        $this->entityManager->persist($entity);
        $this->entityManager->persist($entity[0]);
        $this->entityManager->persist($entity[0][0]);
        $this->entityManager->flush();

        $root = $repository->getTree();

        $this->assertSame($root[0][0], $entity[0][0]);
    }

    private function buildNode(array $values = []): TreeNodeEntity
    {
        $node = new TreeNodeEntity();
        foreach ($values as $method => $value) {
            $node->{$method}($value);
        }

        return $node;
    }

    private function buildTree(): TreeNodeEntity
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
