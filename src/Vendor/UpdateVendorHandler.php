<?php

declare(strict_types=1);

namespace NenePayout\Vendor;

use Nene2\Http\JsonRequestBodyParser;
use Nene2\Http\JsonResponseFactory;
use Nene2\Routing\Router;
use NenePayout\Support\AuthContext;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final readonly class UpdateVendorHandler
{
    public function __construct(
        private UpdateVendorUseCaseInterface $useCase,
        private JsonResponseFactory $response,
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $params = $request->getAttribute(Router::PARAMETERS_ATTRIBUTE, []);
        $id = is_array($params) && isset($params['vendor_id']) && is_string($params['vendor_id']) ? $params['vendor_id'] : '';

        $input = VendorInputMapper::update(JsonRequestBodyParser::parse($request));

        $vendor = $this->useCase->execute(AuthContext::actorUserId($request), $id, $input);

        return $this->response->create(VendorResponse::toArray($vendor));
    }
}
