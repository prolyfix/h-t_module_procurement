<?php

namespace Prolyfix\ProcurementBundle\Exception;

class BookkeepingUploadException extends \RuntimeException
{
    public function __construct(string $message = '', int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public static function serviceNotAvailable(string $serviceName): self
    {
        return new self(sprintf('Bookkeeping service "%s" is not available or not configured.', $serviceName));
    }

    public static function uploadFailed(string $serviceName, string $reason): self
    {
        return new self(sprintf('Upload to "%s" failed: %s', $serviceName, $reason));
    }

    public static function unsupportedDocumentType(string $documentType, string $serviceName): self
    {
        return new self(sprintf('Document type "%s" is not supported by "%s".', $documentType, $serviceName));
    }

    public static function authenticationFailed(string $serviceName): self
    {
        return new self(sprintf('Authentication with "%s" failed.', $serviceName));
    }
}
