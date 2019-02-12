<?php

/*
 * This file is part of Gæstehåndtering.
 *
 * (c) 2017–2019 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace AppBundle\Entity;

use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Expr\Comparison;
use Doctrine\ORM\EntityRepository;

class CodeRepository extends EntityRepository
{
    public function findExpired()
    {
        $now = new \DateTime('now', new \DateTimeZone('UTC'));
        $criteria = (new Criteria())
            ->where(new Comparison('endTime', Comparison::LT, $now));

        return $this->matching($criteria);
    }
}
