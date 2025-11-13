<?php

namespace App\Repository;

use App\Entity\Code;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\AbstractLazyCollection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Expr\Comparison;
use Doctrine\Common\Collections\Selectable;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Code|null find($id, $lockMode = null, $lockVersion = null)
 * @method Code|null findOneBy(array $criteria, array $orderBy = null)
 * @method Code[]    findAll()
 * @method Code[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CodeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Code::class);
    }

    public function findExpired(): AbstractLazyCollection&Selectable
    {
        $now = new \DateTime('now', new \DateTimeZone('UTC'));
        $criteria = (new Criteria())
            ->where(new Comparison('endTime', Comparison::LT, $now));

        return $this->matching($criteria);
    }

    public function remove(Code $code, bool $flush = false): void
    {
        $this->getEntityManager()->remove($code);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
