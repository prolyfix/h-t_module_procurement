<?php

namespace Prolyfix\ProcurementBundle\Repository;

use Prolyfix\ProcurementBundle\Entity\DeliverySlipLine;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<DeliverySlipLine>
 */
class DeliverySlipLineRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DeliverySlipLine::class);
    }
}
