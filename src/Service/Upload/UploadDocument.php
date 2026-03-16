<?php

namespace Prolyfix\ProcurementBundle\Service\Upload;

class UploadDocument
{
    public const TYPE_INVOICE       = 'invoice';
    public const TYPE_RECEIPT       = 'receipt';
    public const TYPE_DELIVERY_SLIP = 'delivery_slip';

    /**
     * @param string      $filePath     Absolute path to the file to upload
     * @param string      $documentType One of the TYPE_* constants defined on this class
     * @param string|null $fileName     Optional display name; defaults to basename of $filePath
     * @param string|null $mimeType     MIME type of the file (e.g. 'application/pdf', 'image/jpeg')
     * @param array       $metadata     Additional metadata passed to the bookkeeping service
     */
    public function __construct(
        private readonly string $filePath,
        private readonly string $documentType,
        private readonly ?string $fileName = null,
        private readonly ?string $mimeType = null,
        private readonly array $metadata = [],
    ) {
    }

    public function getFilePath(): string
    {
        return $this->filePath;
    }

    public function getDocumentType(): string
    {
        return $this->documentType;
    }

    public function getFileName(): string
    {
        return $this->fileName ?? basename($this->filePath);
    }

    public function getMimeType(): ?string
    {
        return $this->mimeType;
    }

    public function getMetadata(): array
    {
        return $this->metadata;
    }
}
