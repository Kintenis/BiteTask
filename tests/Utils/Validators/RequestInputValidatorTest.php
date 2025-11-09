<?php declare(strict_types=1);

namespace App\Tests\Utils\Validators;

use App\Model\Response as ModelResponse;
use App\Utils\Validators\RequestInputValidator;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Serializer\SerializerInterface;

class RequestInputValidatorTest extends TestCase
{
    /** @var EntityManagerInterface&MockObject */
    private EntityManagerInterface $em;

    /** @var SerializerInterface&MockObject */
    private SerializerInterface $serializer;

    /** @var ParameterBagInterface&MockObject */
    private ParameterBagInterface $params;

    protected function setUp(): void
    {
        $this->em = $this->createMock(EntityManagerInterface::class);
        $this->serializer = $this->createMock(SerializerInterface::class);
        $this->params = $this->createMock(ParameterBagInterface::class);

        // Default serializer behavior to encode our Response model to JSON, like the app would.
        $this->serializer
            ->method('serialize')
            ->willReturnCallback(function ($data, string $format) {
                if ($data instanceof ModelResponse && $format === 'json') {
                    return json_encode([
                        'success' => $data->isSuccess(),
                        'content' => $data->getContent(),
                    ], JSON_THROW_ON_ERROR);
                }

                return '';
            });
    }

    private function createValidator(): RequestInputValidator
    {
        return new RequestInputValidator($this->em, $this->serializer, $this->params);

    }

    public function testCheckSingleReturnsJsonResponseForIpAddressNotSet(): void
    {
        $validator = $this->createValidator();
        $response = new ModelResponse();
        $ip = null;

        $result = $validator->checkSingle($response, $ip);

        $decoded = json_decode($result->getContent(), true, 512, JSON_THROW_ON_ERROR);
        $this->assertFalse($decoded['success']);
        $this->assertSame(['ipAddress: I MUST BE SET.'], $decoded['content']);
    }

    public function testCheckSingleReturnsJsonResponseForIpAddressNotString(): void
    {
        $validator = $this->createValidator();
        $response = new ModelResponse();
        $ip = 1;

        $result = $validator->checkSingle($response, $ip);

        $decoded = json_decode($result->getContent(), true, 512, JSON_THROW_ON_ERROR);
        $this->assertFalse($decoded['success']);
        $this->assertSame(['ipAddress: I MUST BE A STRING.'], $decoded['content']);
    }

    public function testCheckSingleReturnsJsonResponseForIpAddressNotValid(): void
    {
        $validator = $this->createValidator();
        $response = new ModelResponse();
        $ip = 'Ip Address.';

        $result = $validator->checkSingle($response, $ip);

        $decoded = json_decode($result->getContent(), true, 512, JSON_THROW_ON_ERROR);
        $this->assertFalse($decoded['success']);
        $this->assertSame(['IP: ' . $ip . ' is not a valid IP Address.'], $decoded['content']);
    }

    public function testCheckSingleReturnsNullWhenEverythingValidated(): void
    {
        $validator = $this->createValidator();
        $response = new ModelResponse();
        $ip = '192.168.1.10';

        $result = $validator->checkSingle($response, $ip);

        $this->assertNull($result, 'Expected null for valid input.');
    }

    public function testCheckBulkReturnsJsonResponseForRequestBodyNotAnArray(): void
    {
        $validator = $this->createValidator();
        $response = new ModelResponse();
        $requestBody = 1;

        $result = $validator->checkBulk($response, $requestBody);

        $decoded = json_decode($result->getContent(), true, 512, JSON_THROW_ON_ERROR);
        $this->assertFalse($decoded['success']);
        $this->assertSame(['Request NOT an array.'], $decoded['content']);
    }

    public function testCheckBulkReturnsJsonResponseForActionNotSet(): void
    {
        $validator = $this->createValidator();
        $response = new ModelResponse();
        $requestBody = ['ACTION' => 'ADD', 'ipAddresses' => ['192.168.1.10']];

        $result = $validator->checkBulk($response, $requestBody);

        $decoded = json_decode($result->getContent(), true, 512, JSON_THROW_ON_ERROR);
        $this->assertFalse($decoded['success']);
        $this->assertSame(['action: I MUST BE SET.'], $decoded['content']);
    }

    public function testCheckBulkReturnsJsonResponseForActionNotString(): void
    {
        $validator = $this->createValidator();
        $response = new ModelResponse();
        $requestBody = ['action' => 1, 'ipAddresses' => ['192.168.1.10']];

        $result = $validator->checkBulk($response, $requestBody);

        $decoded = json_decode($result->getContent(), true, 512, JSON_THROW_ON_ERROR);
        $this->assertFalse($decoded['success']);
        $this->assertSame(['action: I MUST BE A STRING.'], $decoded['content']);
    }

    public function testCheckBulkReturnsJsonResponseForActionWrongValue(): void
    {
        $validator = $this->createValidator();
        $response = new ModelResponse();
        $requestBody = ['action' => 'UPDATE', 'ipAddresses' => ['192.168.1.10']];

        $result = $validator->checkBulk($response, $requestBody);

        $decoded = json_decode($result->getContent(), true, 512, JSON_THROW_ON_ERROR);
        $this->assertFalse($decoded['success']);
        $this->assertSame(['action: I CAN ONLY HAVE ONE OF TWO VALUES. ADD|DELETE'], $decoded['content']);
    }

    public function testCheckBulkReturnsJsonResponseForIpAddressesNotSet(): void
    {
        $validator = $this->createValidator();
        $response = new ModelResponse();
        $requestBody = ['action' => 'ADD', 'IPADDRESSES' => ['192.168.1.10']];

        $result = $validator->checkBulk($response, $requestBody);

        $decoded = json_decode($result->getContent(), true, 512, JSON_THROW_ON_ERROR);
        $this->assertFalse($decoded['success']);
        $this->assertSame(['ipAddresses: I MUST BE SET.'], $decoded['content']);
    }

    public function testCheckBulkReturnsJsonResponseForIpAddressesNotAnArray(): void
    {
        $validator = $this->createValidator();
        $response = new ModelResponse();
        $requestBody = ['action' => 'ADD', 'ipAddresses' => 1];

        $result = $validator->checkBulk($response, $requestBody);

        $decoded = json_decode($result->getContent(), true, 512, JSON_THROW_ON_ERROR);
        $this->assertFalse($decoded['success']);
        $this->assertSame(['ipAddresses: I MUST BE AN ARRAY.'], $decoded['content']);
    }

    public function testCheckBulkReturnsJsonResponseForIpAddressesNotValid(): void
    {
        $validator = $this->createValidator();
        $response = new ModelResponse();
        $requestBody = ['action' => 'ADD', 'ipAddresses' => ['ipAddress']];

        $result = $validator->checkBulk($response, $requestBody);

        $decoded = json_decode($result->getContent(), true, 512, JSON_THROW_ON_ERROR);
        $this->assertFalse($decoded['success']);
        $this->assertSame(['IP: ipAddress is not a valid IP Address.'], $decoded['content']);
    }

    public function testCheckBulkReturnsNullWhenEverythingValidated(): void
    {
        $validator = $this->createValidator();
        $response = new ModelResponse();
        $requestBody = ['action' => 'DELETE', 'ipAddresses' => ['192.168.1.10']];

        $result = $validator->checkBulk($response, $requestBody);

        $this->assertNull($result, 'Expected null for valid input.');
    }
}
