<?php declare(strict_types=1);

namespace App\Utils\Validators;

use App\Model\Response;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

abstract class AbstractValidator
{
    protected EntityManagerInterface $entityManager;
    protected SerializerInterface $serializer;
    protected ParameterBagInterface $parameterBag;

    public function __construct(
        EntityManagerInterface $entityManager,
        SerializerInterface $serializer,
        ParameterBagInterface $parameterBag
    ) {
        $this->entityManager = $entityManager;
        $this->serializer = $serializer;
        $this->parameterBag = $parameterBag;
    }

    public function jsonResponse(Response $response, bool $isSuccess, array $content): JsonResponse
    {
        $response->setSuccess($isSuccess);
        $response->setContent($content);

        return JsonResponse::fromJsonString($this->serializer->serialize($response, 'json'));
    }
}