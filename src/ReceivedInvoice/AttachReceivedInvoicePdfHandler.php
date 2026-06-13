<?php

declare(strict_types=1);

namespace NenePayout\ReceivedInvoice;

use Nene2\Http\JsonResponseFactory;
use Nene2\Routing\Router;
use Nene2\Validation\ValidationError;
use Nene2\Validation\ValidationException;
use NenePayout\Support\AuthContext;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UploadedFileInterface;

final readonly class AttachReceivedInvoicePdfHandler
{
    public function __construct(
        private AttachReceivedInvoicePdfUseCaseInterface $useCase,
        private JsonResponseFactory $response,
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $params = $request->getAttribute(Router::PARAMETERS_ATTRIBUTE, []);
        $id = is_array($params) && isset($params['received_invoice_id']) && is_string($params['received_invoice_id'])
            ? $params['received_invoice_id']
            : '';

        $file = $this->validatedFile($request);

        $invoice = $this->useCase->execute(AuthContext::actorUserId($request), $id, $file);

        return $this->response->create(ReceivedInvoiceResponse::toArray($invoice));
    }

    private function validatedFile(ServerRequestInterface $request): UploadedFileInterface
    {
        $files = $request->getUploadedFiles();
        $file = $files['file'] ?? null;

        if (!$file instanceof UploadedFileInterface || $file->getError() !== UPLOAD_ERR_OK) {
            throw new ValidationException([new ValidationError('file', 'A PDF file upload is required.', 'required')]);
        }

        if ($file->getClientMediaType() !== 'application/pdf') {
            throw new ValidationException([new ValidationError('file', 'The uploaded file must be a PDF.', 'invalid_type')]);
        }

        return $file;
    }
}
