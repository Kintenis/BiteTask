<?php

namespace App\Utils\Validators;

use App\Entity\Blacklist;
use App\Model\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

class IpValidator extends AbstractValidator
{
    public function validateIp(Response $response, string $ipAddress): ?JsonResponse
    {
        if (filter_var($ipAddress, FILTER_VALIDATE_IP)) {
            return null;
        }

        return $this->jsonResponse($response, false, ['IP: ' . $ipAddress . ' is not a valid IP Address.']);
    }

    public function checkBlacklist(Response $response, string $ipAddress): ?JsonResponse
    {
        if ($this->entityManager->getRepository(Blacklist::class)->findOneBy(['ip' => $ipAddress])) {
            return $this->jsonResponse($response, false, ['IP: ' . $ipAddress . ' IS IN THE BLACKLIST.']);
        }

        return null;
    }
}