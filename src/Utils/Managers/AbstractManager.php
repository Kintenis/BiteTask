<?php

namespace App\Utils\Managers;

use App\Utils\Providers\IpStackProvider;
use App\Utils\Validators\IpValidator;
use App\Utils\Validators\RequestInputValidator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

abstract class AbstractManager
{
    protected EntityManagerInterface $entityManager;
    protected RequestInputValidator $requestInputValidator;
    protected IpValidator $ipValidator;
    protected IpStackProvider $ipStackProvider;
    protected SerializerInterface $serializer;

    public function __construct(
        EntityManagerInterface $entityManager,
        RequestInputValidator $requestInputValidator,
        IpValidator $ipValidator,
        IpStackProvider $ipStackProvider,
        SerializerInterface $serializer
    ) {
        $this->entityManager = $entityManager;
        $this->requestInputValidator = $requestInputValidator;
        $this->ipValidator = $ipValidator;
        $this->ipStackProvider = $ipStackProvider;
        $this->serializer = $serializer;
    }

    public function getOneByIp(string $entityName, string $ipAddress): ?object
    {
        if (!in_array($entityName, ['Ip', 'Blacklist'])) {
            return null;
        }

        if ($entity = $this->entityManager->getRepository('App\\Entity\\' . $entityName)->findOneBy(['ip' => $ipAddress])) {
            return $entity;
        }

        return null;
    }

    public function deleteOneByIp(string $entityName, string $ipAddress): bool
    {
        if ($entity = $this->getOneByIp($entityName, $ipAddress)) {
            $this->entityManager->remove($entity);
            $this->entityManager->flush();

            $cache = new FilesystemAdapter();

            if (($entityName === 'Ip') && $cache->getItem($ipAddress)->isHit()) {
                $cache->deleteItem($ipAddress);
            }

            return true;
        }

        return false;
    }

    public function deleteBulkByIp(string $entityName, array $ipAddresses): ?array
    {
        $errors = [];

        foreach ($ipAddresses as $ipAddress) {
            if ($entity = $this->getOneByIp($entityName, $ipAddress)) {
                $this->entityManager->remove($entity);

                $cache = new FilesystemAdapter();

                if (($entityName === 'Ip') && $cache->getItem($ipAddress)->isHit()) {
                    $cache->deleteItem($ipAddress);
                }
            } else {
                $errors[] = $ipAddress;
            }
        }

        if (!empty($errors)) {
            return $errors;
        }

        $this->entityManager->flush();

        return null;
    }
}