<?php declare(strict_types=1);

namespace App\Tests\Utils\Validators;

use App\Entity\Blacklist;
use App\Model\Response as ModelResponse;
use App\Utils\Validators\IpValidator;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Serializer\SerializerInterface;

class IpValidatorTest extends TestCase
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

    private function createValidator(): IpValidator
    {
        return new IpValidator($this->em, $this->serializer, $this->params);
        
    }

    public function testValidateIpReturnsNullForValidIPv4(): void
    {
        $validator = $this->createValidator();
        $response = new ModelResponse();

        $result = $validator->validateIp($response, '192.168.1.10');

        $this->assertNull($result, 'Expected null for valid IPv4 address');
    }

    public function testValidateIpReturnsNullForValidIPv6(): void
    {
        $validator = $this->createValidator();
        $response = new ModelResponse();

        $result = $validator->validateIp($response, '2001:0db8:85a3:0000:0000:8a2e:0370:7334');

        $this->assertNull($result, 'Expected null for valid IPv6 address');
    }

    public function testValidateIpReturnsJsonResponseForInvalidIp(): void
    {
        $validator = $this->createValidator();
        $response = new ModelResponse();
        $ip = 'not_an_ip';

        $result = $validator->validateIp($response, $ip);

        $decoded = json_decode($result->getContent(), true, 512, JSON_THROW_ON_ERROR);
        $this->assertFalse($decoded['success']);
        $this->assertSame(['IP: ' . $ip . ' is not a valid IP Address.'], $decoded['content']);
    }

    public function testCheckBlacklistReturnsNullWhenNotBlacklisted(): void
    {
        $repo = $this->createMock(EntityRepository::class);
        $repo->method('findOneBy')->willReturn(null);

        $this->em
            ->method('getRepository')
            ->with(Blacklist::class)
            ->willReturn($repo);

        $validator = $this->createValidator();
        $response = new ModelResponse();

        $result = $validator->checkBlacklist($response, '1.2.3.4');

        $this->assertNull($result, 'Expected null when IP not in blacklist');
    }

    public function testCheckBlacklistReturnsJsonResponseWhenBlacklisted(): void
    {
        $repo = $this->createMock(EntityRepository::class);
        $repo->method('findOneBy')->willReturn(new Blacklist());

        $this->em
            ->method('getRepository')
            ->with(Blacklist::class)
            ->willReturn($repo);

        $validator = $this->createValidator();
        $response = new ModelResponse();
        $ip = '1.2.3.4';

        $result = $validator->checkBlacklist($response, $ip);

        $decoded = json_decode($result->getContent(), true, 512, JSON_THROW_ON_ERROR);
        $this->assertFalse($decoded['success']);
        $this->assertSame(['IP: ' . $ip . ' IS IN THE BLACKLIST.'], $decoded['content']);

        $this->assertFalse($response->isSuccess());
        $this->assertSame(['IP: ' . $ip . ' IS IN THE BLACKLIST.'], $response->getContent());
    }
}
