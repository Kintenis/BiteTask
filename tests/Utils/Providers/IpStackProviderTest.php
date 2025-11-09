<?php declare(strict_types=1);

namespace App\Tests\Utils\Providers;

use App\Model\IpStack\Response as IpStackResponseModel;
use App\Utils\Providers\IpStackProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface as HttpResponseInterface;

class IpStackProviderTest extends TestCase
{
    /** @var HttpClientInterface&MockObject */
    private HttpClientInterface $httpClient;

    /** @var ParameterBagInterface&MockObject */
    private ParameterBagInterface $params;

    /** @var SerializerInterface&MockObject */
    private SerializerInterface $serializer;

    protected function setUp(): void
    {
        $this->httpClient = $this->createMock(HttpClientInterface::class);
        $this->params = $this->createMock(ParameterBagInterface::class);
        $this->serializer = $this->createMock(SerializerInterface::class);
    }

    private function createProvider(): IpStackProvider
    {
        return new IpStackProvider($this->httpClient, $this->params, $this->serializer);
    }

    public function testFetchDataMakesRequestAndDeserializes(): void
    {
        $ip = '1.2.3.4';
        $expectedAccessKey = 'test_key_123';
        $jsonPayload = '{"some":"json"}';

        // Parameter bag should provide the access key
        $this->params
            ->expects($this->once())
            ->method('get')
            ->with('app.ip_stack_auth_key')
            ->willReturn($expectedAccessKey);

        // Mock HTTP response to return our JSON payload
        $httpResponse = $this->createMock(HttpResponseInterface::class);
        $httpResponse
            ->expects($this->once())
            ->method('getContent')
            ->willReturn($jsonPayload);

        // Ensure correct HTTP request is performed with expected URL and query params
        $this->httpClient
            ->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                'http://api.ipstack.com/' . $ip,
                $this->callback(function (array $options) use ($expectedAccessKey): bool {
                    if (!isset($options['query']) || !is_array($options['query'])) {
                        return false;
                    }
                    $q = $options['query'];
                    return ($q['access_key'] ?? null) === $expectedAccessKey
                        && ($q['language'] ?? null) === 'en'
                        && ($q['output'] ?? null) === 'json';
                })
            )
            ->willReturn($httpResponse);

        // Serializer should deserialize the HTTP content into our model
        $expectedModel = new IpStackResponseModel();
        $this->serializer
            ->expects($this->once())
            ->method('deserialize')
            ->with($jsonPayload, IpStackResponseModel::class, 'json')
            ->willReturn($expectedModel);

        $provider = $this->createProvider();

        $result = $provider->fetchData($ip);

        $this->assertSame($expectedModel, $result);
    }

    public function testFetchDataPropagatesHttpClientException(): void
    {
        $ip = '5.6.7.8';

        $this->params
            ->method('get')
            ->with('app.ip_stack_auth_key')
            ->willReturn('any');

        $this->httpClient
            ->expects($this->once())
            ->method('request')
            ->willThrowException(new \RuntimeException('HTTP failure'));

        $provider = $this->createProvider();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('HTTP failure');

        // Should bubble up the exception from the HTTP client
        $provider->fetchData($ip);
    }
}
