<?php

namespace Prolyfix\ProcurementBundle\Interface;

use Prolyfix\ProcurementBundle\Service\Upload\UploadDocument;
use Prolyfix\ProcurementBundle\Service\Upload\UploadResult;

/**
 * Common interface for uploading procurement documents to bookkeeping services.
 *
 * Implementations of this interface connect to external bookkeeping platforms
 * (e.g. DATEV, Lexoffice, sevDesk) and submit documents such as invoices,
 * receipts, or delivery slips for further processing.
 */
interface BookkeepingUploadInterface
{
    /**
     * Upload a document to the bookkeeping service.
     *
     * @throws \Prolyfix\ProcurementBundle\Exception\BookkeepingUploadException
     */
    public function upload(UploadDocument $document): UploadResult;

    /**
     * Returns true when this service can handle documents of the given type.
     *
     * @param string $documentType One of the UploadDocument::TYPE_* constants
     */
    public function supports(string $documentType): bool;

    /**
     * Human-readable name of the bookkeeping service (e.g. "DATEV").
     */
    public function getServiceName(): string;

    /**
     * Returns true when the service is properly configured and reachable.
     */
    public function isAvailable(): bool;
}
