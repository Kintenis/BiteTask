<?php declare(strict_types=1);

namespace App\Utils\Providers;

use App\Model\IpStack\Response;

class IpStackProvider extends AbstractProvider
{
    public function fetchData(string $ipAddress): Response
    {
        $response = $this->httpClient->request(
            'GET',
            'http://api.ipstack.com/' . $ipAddress,
            [
                'query' => [
                    'access_key' => $this->parameterBag->get('app.ip_stack_auth_key'),
                    'language' => 'en',
                    'output' => 'json'
                ]
            ]
        );

        return $this->serializer->deserialize($response->getContent(), Response::class, 'json');
    }
}