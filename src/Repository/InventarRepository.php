<?php

namespace Prolyfix\ProcurementBundle\Repository;

use Prolyfix\ProcurementBundle\Entity\Inventar;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Prolyfix\ProcurementBundle\Entity\Product;

/**
 * @extends ServiceEntityRepository<Inventar>
 */
class InventarRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Inventar::class);
    }

    public function getOverview()
    {
        return $this->createQueryBuilder('i')
            ->addSelect('sum(i.quantity) as quantity')
            ->addSelect('p.name as productName')
            ->leftJoin('i.product', 'p')
            ->groupBy('i.product')
            ->getQuery()
            ->getResult();
    }
    public function StockByProduct(Product $product)
    {
        return $this->createQueryBuilder('i')
            ->select('SUM(i.quantity) as quantity')
            ->andWhere('i.product = :product')
            ->setParameter('product', $product)
            ->getQuery()
            ->getResult();
    }
}
