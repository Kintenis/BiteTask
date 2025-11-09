<?php declare(strict_types=1);

namespace App\Utils\Managers;

use App\Repository\IpRepository;
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

    public function getOneByIp(object $entityClass, string $ipAddress): ?object
    {
        if ($entity = $entityClass->findOneBy(['ip' => $ipAddress])) {
            return $entity;
        }

        return null;
    }

    public function deleteOneByIp(object $entityClass, string $ipAddress): bool
    {
        if ($entity = $this->getOneByIp($entityClass, $ipAddress)) {
            $this->entityManager->remove($entity);
            $this->entityManager->flush();

            $cache = new FilesystemAdapter();

            if (($entityClass instanceof IpRepository) && $cache->getItem($ipAddress)->isHit()) {
                $cache->deleteItem($ipAddress);
            }

            return true;
        }

        return false;
    }

    public function deleteBulkByIp(object $entityClass, array $ipAddresses): ?array
    {
        $errors = [];

        foreach ($ipAddresses as $ipAddress) {
            if ($entity = $this->getOneByIp($entityClass, $ipAddress)) {
                $this->entityManager->remove($entity);

                $cache = new FilesystemAdapter();

                if (($entityClass instanceof IpRepository) && $cache->getItem($ipAddress)->isHit()) {
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

    public function dateDiffInSeconds(\DateTimeImmutable $date1, \DateTimeImmutable $date2): int
    {
        if ($date1 >= $date2) {
            $greaterDate = $date1;
            $lesserDate = $date2;
        } else {
            $greaterDate = $date2;
            $lesserDate = $date1;
        }

        return $greaterDate->getTimestamp() - $lesserDate->getTimestamp();
    }
}