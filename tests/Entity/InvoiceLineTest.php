<?php

declare(strict_types=1);

namespace Prolyfix\ProcurementBundle\Tests\Entity;

use PHPUnit\Framework\TestCase;
use Prolyfix\ProcurementBundle\Entity\InvoiceLine;

final class InvoiceLineTest extends TestCase
{
    public function testDefaultsAreNull(): void
    {
        $line = new InvoiceLine();

        $this->assertNull($line->getId());
        $this->assertNull($line->getInvoice());
        $this->assertNull($line->getDescription());
        $this->assertNull($line->getQuantity());
        $this->assertNull($line->getMeasure());
        $this->assertNull($line->getGrossPrice());
        $this->assertNull($line->getNetPrice());
        $this->assertNull($line->getVat());
    }

    public function testScalarFieldsSetterGetter(): void
    {
        $line = new InvoiceLine();

        $line->setDescription('Gloves box');
        $line->setQuantity(10.0);
        $line->setMeasure('box');
        $line->setGrossPrice(24.0);
        $line->setNetPrice(20.0);
        $line->setVat(4.0);

        $this->assertSame('Gloves box', $line->getDescription());
        $this->assertSame(10.0, $line->getQuantity());
        $this->assertSame('box', $line->getMeasure());
        $this->assertSame(24.0, $line->getGrossPrice());
        $this->assertSame(20.0, $line->getNetPrice());
        $this->assertSame(4.0, $line->getVat());
    }
}
