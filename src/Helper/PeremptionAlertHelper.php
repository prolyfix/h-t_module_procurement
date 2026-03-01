<?php

namespace Prolyfix\ProcurementBundle\Helper;

use Prolyfix\ProcurementBundle\Entity\Inventar;
use Prolyfix\ProcurementBundle\Entity\Product;

class PeremptionAlertHelper
{
    /**
     * @param Inventar[] $movements
     */
    public function getAlertMessage(Product $product, array $movements, float $currentStock): ?string
    {
        if (!$product->hasPeremption() || $currentStock <= 0) {
            return null;
        }

        $today = new \DateTimeImmutable('today');
        foreach ($movements as $movement) {
            if (!$movement instanceof Inventar) {
                continue;
            }

            if (($movement->getQuantity() ?? 0.0) <= 0) {
                continue;
            }

            $expirationDate = $movement->getExpirationDate();
            if ($expirationDate === null) {
                continue;
            }

            if ($expirationDate < $today) {
                return 'Alert: this product has expired stock.';
            }
        }

        return null;
    }
}
