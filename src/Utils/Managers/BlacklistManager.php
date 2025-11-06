<?php

namespace App\Utils\Managers;

use App\Entity\Blacklist;
use App\Model\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

class BlacklistManager extends AbstractManager
{
    public function fetch(Response $response): JsonResponse
    {
        $ipAddresses = [];

        if ($entities = $this->entityManager->getRepository(Blacklist::class)->findAll()) {
            foreach ($entities as $entity) {
                $ipAddresses[] = $entity->getIp();
            }

            return $this->ipValidator->jsonResponse($response, true, $ipAddresses);
        }

        return $this->ipValidator->jsonResponse($response, true, []);
    }

    public function addSingle(Response $response, string $ipAddress): JsonResponse
    {
        if ($error = $this->requestInputValidator->checkSingle($response, $ipAddress)) {
            return $error;
        }

        if ($this->flushSingle($ipAddress)) {
            return $this->ipValidator->jsonResponse($response, true, ['IP ' . $ipAddress . ' was added to the blacklist successfully.']);
        }

        return $this->ipValidator->jsonResponse($response, false, ['IP ' . $ipAddress . ' already exists in the blacklist.']);
    }

    public function deleteSingle(Response $response, string $ipAddress): JsonResponse
    {
        if ($error = $this->requestInputValidator->checkSingle($response, $ipAddress)) {
            return $error;
        }

        if ($this->deleteOneByIp('Blacklist', $ipAddress)) {
            return $this->ipValidator->jsonResponse($response, true, ['IP ' . $ipAddress . ' was removed from the blacklist successfully.']);
        }

        return $this->ipValidator->jsonResponse($response, false, ['IP ' . $ipAddress . ' does not exist in the blacklist.']);
    }

    public function bulk(Response $response, mixed $requestBody): JsonResponse
    {
        if ($error = $this->requestInputValidator->checkBulk($response, $requestBody)) {
            return $error;
        }

        if ($requestBody['action'] === 'DELETE') {
            $errors = $this->deleteBulkByIp('Blacklist', $requestBody['ipAddresses']);

            if (!$errors) {
                return $this->ipValidator->jsonResponse($response, true, ['IP Addresses were removed from the blacklist successfully.']);
            }

            return $this->ipValidator->jsonResponse($response, false, ['IP Addresses [' . implode(', ', $errors) . '] do not exist in the blacklist.']);
        }

        $errors = [];

        foreach ($requestBody['ipAddresses'] as $ipAddress) {
            if (!$this->getOneByIp('Blacklist', $ipAddress)) {
                $blacklist = new Blacklist();
                $blacklist->setIp($ipAddress);

                $this->entityManager->persist($blacklist);
            } else {
                $errors[] = $ipAddress;
            }
        }

        if (empty($errors)) {
            $this->entityManager->flush();

            return $this->ipValidator->jsonResponse($response, true, ['IP Addresses were added to the blacklist successfully.']);
        }

        return $this->ipValidator->jsonResponse($response, false, ['IP Addresses [' . implode(', ', $errors) . '] already exist in the blacklist.']);
    }

    private function flushSingle(string $ipAddress): bool
    {
        if (!$this->entityManager->getRepository(Blacklist::class)->findOneBy(['ip' => $ipAddress])) {
            $blacklist = new Blacklist();
            $blacklist->setIp($ipAddress);

            $this->entityManager->persist($blacklist);
            $this->entityManager->flush();

            return true;
        }

        return false;
    }
}