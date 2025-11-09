<?php declare(strict_types=1);

namespace App\Utils\Providers;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

abstract class AbstractProvider
{
    protected HttpClientInterface $httpClient;
    protected ParameterBagInterface $parameterBag;
    protected SerializerInterface $serializer;

    public function __construct(
        HttpClientInterface $httpClient,
        ParameterBagInterface $parameterBag,
        SerializerInterface $serializer
    ) {
        $this->httpClient = $httpClient;
        $this->parameterBag = $parameterBag;
        $this->serializer = $serializer;
    }
}