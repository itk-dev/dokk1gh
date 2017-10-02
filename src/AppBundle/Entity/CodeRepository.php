<?php

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

        return $this->matching(new Criteria());
    }
}
