<?php

namespace App\Utils\Managers;

use DateTimeImmutable;
use App\Entity\Ip;
use App\Model\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Contracts\Cache\ItemInterface;

class IpManager extends AbstractManager
{
    public function fetch(Response $response, string $ipAddress): ?JsonResponse
    {
        if ($error = $this->requestInputValidator->checkSingle($response, $ipAddress)) {
            return $error;
        }

        if ($error = $this->ipValidator->checkBlacklist($response, $ipAddress)) {
            return $error;
        }

        $cache = new FilesystemAdapter();
        $ipEntity = $this->entityManager->getRepository(Ip::class)->findOneBy(['ip' => $ipAddress]);

        if (!$ipEntity) {
            if ($cache->getItem($ipAddress)->isHit()) {
                $cache->deleteItem($ipAddress);
            }

            $cacheIp = $cache->get($ipAddress, function (ItemInterface $item) use ($ipAddress): string {
                $item->expiresAfter(86400);

                return $this->serializer->serialize($this->flushToDatabase($ipAddress, new Ip()), 'json');
            });

            return $this->ipValidator->jsonResponse($response, true, [json_decode($cacheIp, true)]);
        }

        $now = new DateTimeImmutable('now');
        $entityUpdatedAt = $ipEntity->getUpdatedAt();
        $diffInSeconds = $now->getTimestamp() - $entityUpdatedAt->getTimestamp();

        if ($diffInSeconds > 86400) {
            if ($cache->getItem($ipAddress)->isHit()) {
                $cache->deleteItem($ipAddress);
            }

            $cacheIp = $cache->get($ipAddress, function (ItemInterface $item) use ($ipAddress, $ipEntity): string {
                $item->expiresAfter(86400);

                return $this->serializer->serialize($this->flushToDatabase($ipAddress, $ipEntity), 'json');
            });

            return $this->ipValidator->jsonResponse($response, true, [json_decode($cacheIp, true)]);
        }

        return $this->ipValidator->jsonResponse($response, true, [json_decode($cache->getItem($ipAddress)->get(), true)]);
    }

    public function deleteSingle(Response $response, string $ipAddress): JsonResponse
    {
        if ($error = $this->requestInputValidator->checkSingle($response, $ipAddress)) {
            return $error;
        }

        if ($this->deleteOneByIp('Ip', $ipAddress)) {
            return $this->ipValidator->jsonResponse($response, true, ['IP ' . $ipAddress . ' was removed from the database successfully.']);
        }

        return $this->ipValidator->jsonResponse($response, false, ['IP ' . $ipAddress . ' does not exist in the database.']);
    }

    public function bulk(Response $response, mixed $requestBody): JsonResponse
    {
        if ($error = $this->requestInputValidator->checkBulk($response, $requestBody)) {
            return $error;
        }

        if ($requestBody['action'] === 'DELETE') {
            $errors = $this->deleteBulkByIp('Ip', $requestBody['ipAddresses']);

            if (!$errors) {
                return $this->ipValidator->jsonResponse($response, true, ['IP Addresses were removed from the local database successfully.']);
            }

            return $this->ipValidator->jsonResponse($response, false, ['IP Addresses [' . implode(', ', $errors) . '] do not exist in the local database.']);
        }

        return $this->ipValidator->jsonResponse($response, true, ['TO BE CONTINUED.']);
    }

    private function flushToDatabase(string $ipAddress, object $ipEntity): object
    {
        $ipStackApiResponse = $this->ipStackProvider->fetchData($ipAddress);

        $ipEntity->setIp($ipStackApiResponse->getIp())
            ->setType($ipStackApiResponse->getType())
            ->setContinentCode($ipStackApiResponse->getContinentCode())
            ->setContinentName($ipStackApiResponse->getContinentName())
            ->setCountryCode($ipStackApiResponse->getCountryCode())
            ->setCountryName($ipStackApiResponse->getCountryName())
            ->setRegionCode($ipStackApiResponse->getRegionCode())
            ->setRegionName($ipStackApiResponse->getRegionName())
            ->setZip($ipStackApiResponse->getZip())
            ->setCity($ipStackApiResponse->getCity())
            ->setLatitude($ipStackApiResponse->getLatitude())
            ->setLongitude($ipStackApiResponse->getLongitude())
            ->setRadius($ipStackApiResponse->getRadius())
            ->setIpRoutingType($ipStackApiResponse->getIpRoutingType())
            ->setConnectionType($ipStackApiResponse->getConnectionType())
            ->setGeoNameId($ipStackApiResponse->getLocation()->getGeoNameId())
            ->setCapital($ipStackApiResponse->getLocation()->getCapital())
            ->setLanguageCode($ipStackApiResponse->getLocation()->getLanguages()[0]['code'])
            ->setLanguageName($ipStackApiResponse->getLocation()->getLanguages()[0]['name'])
            ->setLanguageNameNative($ipStackApiResponse->getLocation()->getLanguages()[0]['native'])
            ->setCountryFlag($ipStackApiResponse->getLocation()->getCountryFlag())
            ->setCountryFlagEmoji($ipStackApiResponse->getLocation()->getCountryFlagEmoji())
            ->setCountryFlagEmojiUnicode($ipStackApiResponse->getLocation()->getCountryFlagEmojiUnicode())
            ->setCallingCode($ipStackApiResponse->getLocation()->getCallingCode())
            ->setUpdatedAt(new DateTimeImmutable('now'));

        $this->entityManager->persist($ipEntity);
        $this->entityManager->flush();

        return $ipStackApiResponse;
    }
}