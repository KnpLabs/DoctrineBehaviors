<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Tests\Fixtures\ORM;

use Knp\DoctrineBehaviors\ORM\Filterable\FilterableRepositoryTrait;
use Doctrine\ORM\EntityRepository;
use Knp\DoctrineBehaviors\ORM\Filterable;

final class FilterableRepository extends EntityRepository
{
    use FilterableRepositoryTrait;

    public function getILikeFilterColumns()
    {
        return [];
    }

    public function getLikeFilterColumns()
    {
        return ['e:name'];
    }

    public function getEqualFilterColumns()
    {
        return ['e:code'];
    }

    public function getInFilterColumns()
    {
        return [];
    }
}
