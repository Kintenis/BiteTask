<?php

namespace App\Utils\Validators;

use App\Model\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

class RequestInputValidator extends AbstractValidator
{
    public function checkSingle(Response $response, mixed $ipAddress): ?JsonResponse
    {
        if (!isset($ipAddress)) {
            return $this->jsonResponse($response, false, ['ipAddress: I MUST BE SET.']);
        }

        if (!is_string($ipAddress)) {
            return $this->jsonResponse($response, false, ['ipAddress: I MUST BE A STRING.']);
        }

        $ipValidator = new IpValidator($this->entityManager, $this->serializer, $this->parameterBag);

        if ($error = $ipValidator->validateIp($response, $ipAddress)) {
            return $error;
        }

        return null;
    }

    public function checkBulk(Response $response, mixed $requestBody): ?JsonResponse
    {
        if (!is_array($requestBody)) {
            return $this->jsonResponse($response, false, ['Request NOT an array.']);
        }

        if (!isset($requestBody['action'])) {
            return $this->jsonResponse($response, false, ['action: I MUST BE SET.']);
        }

        if (!is_string($requestBody['action'])) {
            return $this->jsonResponse($response, false, ['action: I MUST BE A STRING.']);
        }

        if (!in_array($requestBody['action'], ['ADD', 'DELETE'])) {
            return $this->jsonResponse($response, false, ['action: I CAN ONLY HAVE ONE OF TWO VALUES. ADD|DELETE']);
        }

        if (!isset($requestBody['ipAddresses'])) {
            return $this->jsonResponse($response, false, ['ipAddresses: I MUST BE SET.']);
        }

        if (!is_array($requestBody['ipAddresses'])) {
            return $this->jsonResponse($response, false, ['ipAddresses: I MUST BE AN ARRAY.']);
        }

        if (count($requestBody['ipAddresses']) > $maxItemsPerRequest = $this->parameterBag->get('app.max_items_per_request')) {
            return $this->jsonResponse($response, false, ['Cannot have more than ' . $maxItemsPerRequest . ' IP addresses per request.']);
        }

        $ipValidator = new IpValidator($this->entityManager, $this->serializer, $this->parameterBag);

        foreach ($requestBody['ipAddresses'] as $ipAddress) {
            if ($error = $ipValidator->validateIp($response, $ipAddress)) {
                return $error;
            }
        }

        return null;
    }
}