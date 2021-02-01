<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Tests\ORM;

use Iterator;
use Knp\DoctrineBehaviors\Contract\Entity\TreeNodeInterface;
use Knp\DoctrineBehaviors\Exception\TreeException;
use Knp\DoctrineBehaviors\Tests\AbstractBehaviorTestCase;
use Knp\DoctrineBehaviors\Tests\Fixtures\Entity\TreeNodeEntity;
use Knp\DoctrineBehaviors\Tests\Fixtures\Repository\TreeNodeRepository;
use Nette\Utils\Json;

final class TreeNodeTest extends AbstractBehaviorTestCase
{
    public function testBuildTree(): void
    {
        $rootTreeNodeEntity = new TreeNodeEntity();
        $rootTreeNodeEntity->setId(1);
        $rootTreeNodeEntity->setMaterializedPath('');
        $rootTreeNodeEntity->setName('root');

        $treeNodeEntity2 = new TreeNodeEntity();
        $treeNodeEntity2->setMaterializedPath('/1');
        $treeNodeEntity2->setId(2);
        $treeNodeEntity2->setName('Villes');

        $treeNodeEntity3 = new TreeNodeEntity();
        $treeNodeEntity3->setId(3);
        $treeNodeEntity3->setMaterializedPath('/1/2');
        $treeNodeEntity3->setName('Nantes');

        $treeNodeEntity4 = new TreeNodeEntity();
        $treeNodeEntity4->setId(4);
        $treeNodeEntity4->setMaterializedPath('/1/2/3');
        $treeNodeEntity4->setName('Nantes Est');

        $treeNodeEntity5 = new TreeNodeEntity();
        $treeNodeEntity5->setId(5);
        $treeNodeEntity5->setMaterializedPath('/1/2/3');
        $treeNodeEntity5->setName('Nantes Nord');

        $treeNodeEntity6 = new TreeNodeEntity();
        $treeNodeEntity6->setId(6);
        $treeNodeEntity6->setMaterializedPath('/1/2/3/5');
        $treeNodeEntity6->setName('St-Mihiel');

        $flatTree = [$treeNodeEntity2, $treeNodeEntity3, $treeNodeEntity4, $treeNodeEntity5, $treeNodeEntity6];

        $rootTreeNodeEntity->buildTree($flatTree);

        $this->assertCount(1, $rootTreeNodeEntity->getChildNodes());

        $this->assertCount(1, $rootTreeNodeEntity->getChildNodes()->first()->getChildNodes());
        $this->assertCount(2, $rootTreeNodeEntity->getChildNodes()->first()->getChildNodes()->first()->getChildNodes());

        $this->assertSame(1, $rootTreeNodeEntity->getNodeLevel());
        $this->assertSame(
            4,
            $rootTreeNodeEntity->getChildNodes()
                ->first()
                ->getChildNodes()
                ->first()
                ->getChildNodes()
                ->first()
                ->getNodeLevel()
        );
    }

    public function testIsRoot(): void
    {
        $treeNodeEntity = $this->buildTree();

        $this->assertTrue($treeNodeEntity->getRootNode()->isRootNode());
        $this->assertTrue($treeNodeEntity->isRootNode());
    }

    public function testIsLeaf(): void
    {
        $treeNodeEntity = $this->buildTree();

        $this->assertTrue($treeNodeEntity[0][0][0]->isLeafNode());
        $this->assertTrue($treeNodeEntity[1]->isLeafNode());
    }

    public function testGetRoot(): void
    {
        $treeNodeEntity = $this->buildTree();

        $this->assertSame($treeNodeEntity, $treeNodeEntity->getRootNode());
        $this->assertNull($treeNodeEntity->getRootNode()->getParentNode());

        $this->assertSame(
            $treeNodeEntity,
            $treeNodeEntity->getChildNodes()
                ->get(0)
                ->getChildNodes()
                ->get(0)
                ->getRootNode()
        );
    }

    public function provideRootPaths(): Iterator
    {
        yield [$treeNodeEntity = new TreeNodeEntity(), '/0'];
        $treeNodeEntity->setMaterializedPath('/0/1');
        yield [$treeNodeEntity = new TreeNodeEntity(), '/'];
        $treeNodeEntity->setMaterializedPath('/');
        yield [$treeNodeEntity = new TreeNodeEntity(), '/'];
        $treeNodeEntity->setMaterializedPath('');
        yield [$treeNodeEntity = new TreeNodeEntity(), '/test'];
        $treeNodeEntity->setMaterializedPath('/test');
        yield [$treeNodeEntity = new TreeNodeEntity(), '/0'];
        $treeNodeEntity->setMaterializedPath('/0/1/2/3/4/5/6/');
    }

    /**
     * @dataProvider provideIsChildNodeOf()
     */
    public function testTestisChildNodeOf(TreeNodeInterface $child, TreeNodeInterface $parent, $expected): void
    {
        $this->assertSame($expected, $child->isChildNodeOf($parent));
    }

    public function provideIsChildNodeOf(): Iterator
    {
        $treeNodeEntity = $this->buildTree();

        yield [$treeNodeEntity[0][0], $treeNodeEntity[0], true];
        yield [$treeNodeEntity[0][0][0], $treeNodeEntity[0][0], true];
        yield [$treeNodeEntity[0][0][0], $treeNodeEntity[0], false];
        yield [$treeNodeEntity[0][0][0], $treeNodeEntity[0][0][0], false];
    }

    public function provideToArray(): array
    {
        return [
            1 => [
                'node' => '',
                'children' => [
                    2 => [
                        'node' => '',
                        'children' => [
                            4 => [
                                'node' => '',
                                'children' => [
                                    5 => [
                                        'node' => '',
                                        'children' => [],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    3 => [
                        'node' => '',
                        'children' => [],
                    ],
                ],
            ],
        ];
    }

    public function testToArray(): void
    {
        $expected = $this->provideToArray();
        $treeNodeEntity = $this->buildTree();

        $this->assertSame($expected, $treeNodeEntity->toArray());
    }

    public function testToJson(): void
    {
        $expected = $this->provideToArray();
        $treeNodeEntity = $this->buildTree();
        $this->assertSame(Json::encode($expected), $treeNodeEntity->toJson());
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

        $treeNodeEntity45 = new TreeNodeEntity();
        $treeNodeEntity45->setId(45);
        $tree[] = $treeNodeEntity45;

        $treeNodeEntity46 = new TreeNodeEntity();
        $treeNodeEntity46->setId(46);
        $tree[] = $treeNodeEntity46;

        $this->assertSame(4, $tree->getChildNodes()->count());

        $treeNodeEntity47 = new TreeNodeEntity();
        $treeNodeEntity47->setId(47);
        $tree[2][] = $treeNodeEntity47;

        $treeNodeEntity48 = new TreeNodeEntity();
        $treeNodeEntity48->setId(48);
        $tree[2][] = $treeNodeEntity48;

        $this->assertSame(2, $tree[2]->getChildNodes()->count());

        $this->assertTrue(isset($tree[2][1]));
        $this->assertFalse(isset($tree[2][1][2]));

        unset($tree[2][1]);
        $this->assertFalse(isset($tree[2][1]));
    }

    public function testSetChildNodeOf(): void
    {
        $root1 = new TreeNodeEntity();
        $root1->setId(1);

        $child1 = new TreeNodeEntity();
        $child1->setId(2);

        $this->assertTrue($root1->isRootNode());
        $child1->setChildNodeOf($root1);
        $this->assertTrue($child1->isChildNodeOf($root1));
        $this->assertSame('/1', $child1->getMaterializedPath());

        $root2 = new TreeNodeEntity();
        $root2->setId(3);

        $child2 = new TreeNodeEntity();
        $child2->setId(4);

        $root2->setChildNodeOf(null);

        $this->assertTrue($root2->isRootNode());
        $child2->setChildNodeOf($root2);
        $this->assertSame($root2->getRealMaterializedPath(), $child2->getParentMaterializedPath());
        $this->assertTrue($child2->isChildNodeOf($root2));
        $this->assertSame('/3', $child2->getMaterializedPath());
    }

    public function testTestsetChildNodeOfWithoutId(): void
    {
        $this->expectException(TreeException::class);
        $this->expectExceptionMessage('You must provide an id for this node if you want it to be part of a tree.');

        $parentTreeNode = new TreeNodeEntity();
        $parentTreeNode->setMaterializedPath('/0');

        $childTreeNode = new TreeNodeEntity();
        $childTreeNode->setMaterializedPath('/0/1');
        $childTreeNode->setChildNodeOf($parentTreeNode);
    }

    public function testChildrenCount(): void
    {
        $treeNodeEntity = $this->buildTree();

        $this->assertSame(2, $treeNodeEntity->getChildNodes()->count());
        $this->assertSame(1, $treeNodeEntity->getChildNodes()->get(0)->getChildNodes()->count());
    }

    public function testGetPath(): void
    {
        $treeNodeEntity = $this->buildTree();

        $this->assertSame('/1', $treeNodeEntity->getRealMaterializedPath());
        $this->assertSame('/1/2', $treeNodeEntity->getChildNodes()->get(0)->getRealMaterializedPath());
        $this->assertSame(
            '/1/2/4',
            $treeNodeEntity->getChildNodes()
                ->get(0)
                ->getChildNodes()
                ->get(0)
                ->getRealMaterializedPath()
        );
        $this->assertSame(
            '/1/2/4/5',
            $treeNodeEntity->getChildNodes()
                ->get(0)
                ->getChildNodes()
                ->get(0)
                ->getChildNodes()
                ->get(0)
                ->getRealMaterializedPath()
        );

        $childChildItem = $treeNodeEntity->getChildNodes()
            ->get(0)
            ->getChildNodes()
            ->get(0);
        $childChildChildItem = $treeNodeEntity->getChildNodes()
            ->get(0)
            ->getChildNodes()
            ->get(0)
            ->getChildNodes()
            ->get(0);
        $childChildItem->setChildNodeOf($treeNodeEntity);
        $this->assertSame('/1/4', $childChildItem->getRealMaterializedPath(), 'The path has been updated fo the node');
        $this->assertSame(
            '/1/4/5',
            $childChildChildItem->getRealMaterializedPath(),
            'The path has been updated fo the node and all its descendants'
        );
        $this->assertTrue(
            $treeNodeEntity->getChildNodes()
                ->contains($childChildItem),
            'The children collection has been updated to reference the moved node'
        );
    }

    public function testMoveChildren(): void
    {
        $treeNodeEntity = $this->buildTree();

        $childChildItem = $treeNodeEntity->getChildNodes()
            ->get(0)
            ->getChildNodes()
            ->get(0);
        $childChildChildItem = $treeNodeEntity->getChildNodes()
            ->get(0)
            ->getChildNodes()
            ->get(0)
            ->getChildNodes()
            ->get(0);
        $this->assertSame(4, $childChildChildItem->getNodeLevel(), 'The level is well calcuated');

        $childChildItem->setChildNodeOf($treeNodeEntity);
        $this->assertSame('/1/4', $childChildItem->getRealMaterializedPath(), 'The path has been updated fo the node');
        $this->assertSame(
            '/1/4/5',
            $childChildChildItem->getRealMaterializedPath(),
            'The path has been updated fo the node and all its descendants'
        );
        $this->assertTrue(
            $treeNodeEntity->getChildNodes()
                ->contains($childChildItem),
            'The children collection has been updated to reference the moved node'
        );

        $this->assertSame(3, $childChildChildItem->getNodeLevel(), 'The level has been updated');
    }

    public function testGetTree(): void
    {
        /** @var TreeNodeRepository $repository */
        $repository = $this->entityManager->getRepository(TreeNodeEntity::class);

        $entity = new TreeNodeEntity();
        $entity->setId(1);

        $secondEntity = new TreeNodeEntity();
        $secondEntity->setId(2);
        $entity[0] = $secondEntity;

        $thirdEntity = new TreeNodeEntity();
        $thirdEntity->setId(3);
        $entity[0][0] = $thirdEntity;

        $this->entityManager->persist($entity);
        $this->entityManager->persist($entity[0]);
        $this->entityManager->persist($entity[0][0]);
        $this->entityManager->flush();

        $tree = $repository->getTree();
        $this->assertSame($tree[0][0], $entity[0][0]);
    }

    private function buildTree(): TreeNodeEntity
    {
        $item = new TreeNodeEntity();
        $item->setMaterializedPath('');
        $item->setId(1);

        $childItem = new TreeNodeEntity();
        $childItem->setMaterializedPath('/1');
        $childItem->setId(2);
        $childItem->setChildNodeOf($item);

        $secondChildItem = new TreeNodeEntity();
        $secondChildItem->setMaterializedPath('/1');
        $secondChildItem->setId(3);
        $secondChildItem->setChildNodeOf($item);

        $childChildItem = new TreeNodeEntity();
        $childChildItem->setId(4);
        $childChildItem->setMaterializedPath('/1/2');
        $childChildItem->setChildNodeOf($childItem);

        $childChildChildItem = new TreeNodeEntity();
        $childChildChildItem->setId(5);
        $childChildChildItem->setMaterializedPath('/1/2/4');
        $childChildChildItem->setChildNodeOf($childChildItem);

        return $item;
    }
}
