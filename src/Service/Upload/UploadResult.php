<?php

namespace Prolyfix\ProcurementBundle\Service\Upload;

class UploadResult
{
    /**
     * @param bool        $success     Whether the upload succeeded
     * @param string|null $referenceId Reference ID returned by the bookkeeping service
     * @param string      $message     Human-readable status message
     * @param array       $errors      List of error messages if the upload failed
     */
    public function __construct(
        private readonly bool $success,
        private readonly ?string $referenceId = null,
        private readonly string $message = '',
        private readonly array $errors = [],
    ) {
    }

    public static function success(string $referenceId, string $message = ''): self
    {
        return new self(true, $referenceId, $message);
    }

    public static function failure(array $errors, string $message = ''): self
    {
        return new self(false, null, $message, $errors);
    }

    public function isSuccess(): bool
    {
        return $this->success;
    }

    public function getReferenceId(): ?string
    {
        return $this->referenceId;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
