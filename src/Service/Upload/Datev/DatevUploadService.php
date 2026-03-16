<?php

namespace Prolyfix\ProcurementBundle\Service\Upload\Datev;

use Prolyfix\ProcurementBundle\Exception\BookkeepingUploadException;
use Prolyfix\ProcurementBundle\Interface\BookkeepingUploadInterface;
use Prolyfix\ProcurementBundle\Service\Upload\UploadDocument;
use Prolyfix\ProcurementBundle\Service\Upload\UploadResult;

/**
 * DATEV document upload service.
 *
 * DATEV is a major German bookkeeping software provider. As of the time of
 * writing, DATEV does not offer a publicly accessible REST API for document
 * uploads without a formal partner agreement and a licensed DATEV account.
 *
 * Access to the DATEV Connect / Unternehmen-online API requires:
 *   - A DATEV partner account (Softwarepartner)
 *   - OAuth 2.0 client credentials issued by DATEV
 *   - A client certificate for mTLS authentication
 *
 * Until those credentials are available, this class throws
 * {@see BookkeepingUploadException} from every mutating method.
 * {@see isAvailable()} returns false so callers can detect the situation
 * gracefully.
 *
 * Once DATEV credentials are obtained, configure the service via the
 * constructor parameters and remove the {@see BookkeepingUploadException}
 * guard in {@see upload()}.
 *
 * @see https://developer.datev.de (partner portal, login required)
 */
class DatevUploadService implements BookkeepingUploadInterface
{
    public const SERVICE_NAME = 'DATEV';

    /**
     * Document types recognised by the DATEV "Unternehmen online" API.
     * Keys are UploadDocument::TYPE_* constants, values are the DATEV
     * "Belegart" identifiers (adjust once partner documentation is available).
     */
    private const SUPPORTED_DOCUMENT_TYPES = [
        UploadDocument::TYPE_INVOICE       => 'Eingangsrechnung',
        UploadDocument::TYPE_RECEIPT       => 'Kassenbeleg',
        UploadDocument::TYPE_DELIVERY_SLIP => 'Lieferschein',
    ];

    /**
     * @param string|null $clientId     OAuth 2.0 client ID issued by DATEV
     * @param string|null $clientSecret OAuth 2.0 client secret issued by DATEV
     * @param string|null $apiBaseUrl   DATEV API base URL (default: https://api.datev.de)
     */
    public function __construct(
        private readonly ?string $clientId = null,
        private readonly ?string $clientSecret = null,
        private readonly string $apiBaseUrl = 'https://api.datev.de',
    ) {
    }

    /**
     * {@inheritdoc}
     *
     * @throws BookkeepingUploadException Always, until official DATEV API
     *                                    credentials are configured.
     */
    public function upload(UploadDocument $document): UploadResult
    {
        if (!$this->isAvailable()) {
            throw BookkeepingUploadException::serviceNotAvailable(self::SERVICE_NAME);
        }

        if (!$this->supports($document->getDocumentType())) {
            throw BookkeepingUploadException::unsupportedDocumentType(
                $document->getDocumentType(),
                self::SERVICE_NAME
            );
        }

        /*
         * Implementation stub.
         *
         * When DATEV API credentials are available, this section should:
         *
         * 1. Obtain an OAuth 2.0 access token:
         *    POST {$this->apiBaseUrl}/auth/token
         *    Body: grant_type=client_credentials
         *    Auth: HTTP Basic with $this->clientId / $this->clientSecret
         *
         * 2. Upload the document as multipart/form-data:
         *    POST {$this->apiBaseUrl}/document-management/v1/documents
         *    Header: Authorization: Bearer <access_token>
         *    Body: file=<binary>, type=<datev_belegart>, metadata=<json>
         *
         * 3. Return UploadResult::success(<document_id_from_response>)
         *    or   UploadResult::failure(<errors_from_response>)
         *
         * Example (using Symfony HttpClient):
         *
         *   $token  = $this->requestAccessToken();
         *   $belegart = self::SUPPORTED_DOCUMENT_TYPES[$document->getDocumentType()];
         *   $response = $this->httpClient->request('POST', $this->apiBaseUrl . '/document-management/v1/documents', [
         *       'auth_bearer' => $token,
         *       'body' => [
         *           'file'     => fopen($document->getFilePath(), 'r'),
         *           'type'     => $belegart,
         *           'metadata' => json_encode($document->getMetadata()),
         *       ],
         *   ]);
         *   $data = $response->toArray();
         *   return UploadResult::success($data['id'] ?? '', 'Document uploaded to DATEV.');
         */

        throw BookkeepingUploadException::serviceNotAvailable(self::SERVICE_NAME);
    }

    public function supports(string $documentType): bool
    {
        return array_key_exists($documentType, self::SUPPORTED_DOCUMENT_TYPES);
    }

    public function getServiceName(): string
    {
        return self::SERVICE_NAME;
    }

    /**
     * Returns true only when API credentials are configured.
     *
     * DATEV does not provide a publicly accessible API; credentials must be
     * requested via the DATEV partner programme.
     */
    public function isAvailable(): bool
    {
        return $this->clientId !== null && $this->clientSecret !== null;
    }
}
