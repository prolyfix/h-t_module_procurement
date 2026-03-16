<?php

namespace Prolyfix\ProcurementBundle\EventListener;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Event\PostUpdateEventArgs;
use Prolyfix\ProcurementBundle\Entity\Invoice;

final class InvoiceListener
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {}

    public function postPersist(Invoice $invoice, PostPersistEventArgs $event): void
    {
        $this->matchBankingEntry($invoice);
    }

    public function postUpdate(Invoice $invoice, PostUpdateEventArgs $event): void
    {
        $this->matchBankingEntry($invoice);
    }

    private function matchBankingEntry(Invoice $invoice): void
    {
        if (!class_exists('Prolyfix\BankingBundle\Entity\Entry')) {
            return;
        }

        if ($invoice->getBankingEntryId() !== null) {
            return;
        }

        $total = $invoice->getTotal();
        if ($total <= 0) {
            return;
        }

        $entryRepository = $this->entityManager->getRepository('Prolyfix\BankingBundle\Entity\Entry');
        $allEntries = $entryRepository->findAll();

        $candidates = array_filter($allEntries, static function ($entry) use ($total): bool {
            return round(abs($entry->getAmount()), 2) === round($total, 2);
        });

        if (count($candidates) === 1) {
            $matchedEntry = array_values($candidates)[0];
            $this->entityManager->getUnitOfWork()->scheduleExtraUpdate(
                $invoice,
                ['bankingEntryId' => [null, $matchedEntry->getId()]]
            );
        }
    }
}
