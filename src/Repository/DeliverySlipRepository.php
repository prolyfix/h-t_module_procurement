<?php

namespace Prolyfix\ProcurementBundle\Repository;

use Prolyfix\ProcurementBundle\Entity\DeliverySlip;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<DeliverySlip>
 */
class DeliverySlipRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DeliverySlip::class);
    }
}
