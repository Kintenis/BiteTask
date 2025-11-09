<?php declare(strict_types=1);

namespace App\Utils\Managers;

use App\Entity\Blacklist;
use App\Model\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

class BlacklistManager extends AbstractManager
{
    public function fetch(Response $response): JsonResponse
    {
        if ($entities = $this->entityManager->getRepository(Blacklist::class)->findAll()) {
            $ipAddresses = [];

            foreach ($entities as $entity) {
                $ipAddresses[] = $entity->getIp();
            }

            return $this->ipValidator->jsonResponse(
                $response,
                true,
                $ipAddresses
            );
        }

        return $this->ipValidator->jsonResponse(
            $response,
            true,
            []
        );
    }

    public function addSingle(Response $response, string $ipAddress): JsonResponse
    {
        if ($error = $this->requestInputValidator->checkSingle($response, $ipAddress)) {
            return $error;
        }

        if (!$this->getOneByIp($this->entityManager->getRepository(Blacklist::class), $ipAddress)) {
            $this->newIpPersist($ipAddress);
            $this->entityManager->flush();

            return $this->ipValidator->jsonResponse(
                $response,
                true,
                ['IP ' . $ipAddress . ' was added to the blacklist successfully.']
            );
        }

        return $this->ipValidator->jsonResponse(
            $response,
            false,
            ['IP ' . $ipAddress . ' already exists in the blacklist.']
        );
    }

    public function deleteSingle(Response $response, string $ipAddress): JsonResponse
    {
        if ($error = $this->requestInputValidator->checkSingle($response, $ipAddress)) {
            return $error;
        }

        if ($this->deleteOneByIp($this->entityManager->getRepository(Blacklist::class), $ipAddress)) {
            return $this->ipValidator->jsonResponse(
                $response,
                true,
                ['IP ' . $ipAddress . ' was removed from the blacklist successfully.']
            );
        }

        return $this->ipValidator->jsonResponse(
            $response,
            false,
            ['IP ' . $ipAddress . ' does not exist in the blacklist.']
        );
    }

    public function bulk(Response $response, mixed $requestBody): JsonResponse
    {
        $ipAddressesString = implode(', ', $requestBody['ipAddresses']);

        if ($error = $this->requestInputValidator->checkBulk($response, $requestBody)) {
            return $error;
        }

        if ($requestBody['action'] === 'DELETE') {
            $errors = $this->deleteBulkByIp($this->entityManager->getRepository(Blacklist::class), $requestBody['ipAddresses']);

            if (!$errors) {
                return $this->ipValidator->jsonResponse(
                    $response,
                    true,
                    ['IP Addresses [' . $ipAddressesString . '] were removed from the blacklist successfully.']
                );
            }

            return $this->ipValidator->jsonResponse(
                $response,
                false,
                ['IP Addresses [' . implode(', ', $errors) . '] do not exist in the blacklist.']);
        }

        $errors = $this->addIpAddresses($requestBody);

        if (empty($errors)) {
            $this->entityManager->flush();

            return $this->ipValidator->jsonResponse(
                $response,
                true,
                ['IP Addresses [' . $ipAddressesString . '] were added to the blacklist successfully.']
            );
        }

        return $this->ipValidator->jsonResponse(
            $response,
            false,
            ['IP Addresses [' . implode(', ', $errors) . '] already exist in the blacklist.']
        );
    }

    private function addIpAddresses(array $requestBody): array
    {
        $errors = [];

        foreach ($requestBody['ipAddresses'] as $ipAddress) {
            if (!$this->getOneByIp($this->entityManager->getRepository(Blacklist::class), $ipAddress)) {
                $this->newIpPersist($ipAddress);
            } else {
                $errors[] = $ipAddress;
            }
        }

        return $errors;
    }

    private function newIpPersist(string $ipAddress): void
    {
        $blacklist = new Blacklist();
        $blacklist->setIp($ipAddress);

        $this->entityManager->persist($blacklist);
    }
}