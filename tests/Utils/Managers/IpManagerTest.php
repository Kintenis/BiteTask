<?php declare(strict_types=1);

namespace App\Tests\Utils\Managers;

use App\Entity\Ip;
use App\Model\Response as ModelResponse;
use App\Utils\Managers\IpManager;
use App\Utils\Providers\IpStackProvider;
use App\Utils\Validators\IpValidator;
use App\Utils\Validators\RequestInputValidator;
use DateInterval;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

class IpManagerTest extends TestCase
{
    /** @var EntityManagerInterface&MockObject */
    private EntityManagerInterface $em;

    /** @var RequestInputValidator&MockObject */
    private RequestInputValidator $requestValidator;

    private IpValidator $ipValidator;

    /** @var IpStackProvider&MockObject */
    private IpStackProvider $ipStackProvider;

    /** @var SerializerInterface&MockObject */
    private SerializerInterface $serializer;

    protected function setUp(): void
    {
        $this->em = $this->createMock(EntityManagerInterface::class);
        $this->requestValidator = $this->createMock(RequestInputValidator::class);
        $this->ipStackProvider = $this->createMock(IpStackProvider::class);
        $this->serializer = $this->createMock(SerializerInterface::class);

        // By default validators succeed (return null)
        $this->requestValidator->method('checkSingle')->willReturn(null);
        $this->requestValidator->method('checkBulk')->willReturn(null);

        // Serializer encodes ModelResponse like in production
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

        // Real IpValidator (so jsonResponse works), but stub checkBlacklist to always allow
        $params = $this->createMock(ParameterBagInterface::class);
        $this->ipValidator = $this->getMockBuilder(IpValidator::class)
            ->setConstructorArgs([$this->em, $this->serializer, $params])
            ->onlyMethods(['checkBlacklist'])
            ->getMock();
        $this->ipValidator->method('checkBlacklist')->willReturn(null);
    }

    private function createManager(): IpManager
    {
        return new IpManager($this->em, $this->requestValidator, $this->ipValidator, $this->ipStackProvider, $this->serializer);
    }

    public function testDeleteSingleSuccess(): void
    {
        $ip = '1.2.3.4';
        $entity = (new Ip())->setIp($ip)->setUpdatedAt(new DateTimeImmutable('now'));

        $repo = $this->createMock(EntityRepository::class);
        $repo->method('findOneBy')->with(['ip' => $ip])->willReturn($entity);

        $this->em->method('getRepository')->willReturn($repo);
        $this->em->expects($this->once())->method('remove')->with($entity);
        $this->em->expects($this->once())->method('flush');

        $manager = $this->createManager();
        $response = new ModelResponse();

        $json = $manager->deleteSingle($response, $ip);
        $decoded = json_decode($json->getContent(), true, 512, JSON_THROW_ON_ERROR);
        $this->assertTrue($decoded['success']);
        $this->assertSame(['IP ' . $ip . ' was removed from the database successfully.'], $decoded['content']);
    }

    public function testDeleteSingleNotFound(): void
    {
        $ip = '1.2.3.4';
        $repo = $this->createMock(EntityRepository::class);
        $repo->method('findOneBy')->with(['ip' => $ip])->willReturn(null);
        $this->em->method('getRepository')->willReturn($repo);

        $this->em->expects($this->never())->method('remove');
        $this->em->expects($this->never())->method('flush');

        $manager = $this->createManager();
        $response = new ModelResponse();

        $json = $manager->deleteSingle($response, $ip);
        $decoded = json_decode($json->getContent(), true, 512, JSON_THROW_ON_ERROR);
        $this->assertFalse($decoded['success']);
        $this->assertSame(['IP ' . $ip . ' does not exist in the database.'], $decoded['content']);
    }

    public function testBulkDeleteSuccess(): void
    {
        $ips = ['1.1.1.1', '8.8.8.8'];
        $repo = $this->createMock(EntityRepository::class);

        $repo->method('findOneBy')->willReturnOnConsecutiveCalls(
            (new Ip())->setIp($ips[0])->setUpdatedAt(new DateTimeImmutable('now')),
            (new Ip())->setIp($ips[1])->setUpdatedAt(new DateTimeImmutable('now'))
        );

        $this->em->method('getRepository')->willReturn($repo);
        $this->em->expects($this->exactly(2))->method('remove');
        $this->em->expects($this->once())->method('flush');

        $manager = $this->createManager();
        $response = new ModelResponse();

        $json = $manager->bulk($response, ['action' => 'DELETE', 'ipAddresses' => $ips]);
        $decoded = json_decode($json->getContent(), true, 512, JSON_THROW_ON_ERROR);
        $this->assertTrue($decoded['success']);
        $this->assertSame(['IP Addresses [' . implode(', ', $ips) . '] were removed from the local database successfully.'], $decoded['content']);
    }

    public function testBulkDeleteWithErrors(): void
    {
        $ips = ['1.1.1.1', '8.8.8.8'];
        $repo = $this->createMock(EntityRepository::class);

        // First exists, second missing
        $repo->method('findOneBy')->willReturnOnConsecutiveCalls(
            (new Ip())->setIp($ips[0])->setUpdatedAt(new DateTimeImmutable('now')),
            null
        );

        $this->em->method('getRepository')->willReturn($repo);
        $this->em->expects($this->once())->method('remove');
        $this->em->expects($this->never())->method('flush');

        $manager = $this->createManager();
        $response = new ModelResponse();

        $json = $manager->bulk($response, ['action' => 'DELETE', 'ipAddresses' => $ips]);
        $decoded = json_decode($json->getContent(), true, 512, JSON_THROW_ON_ERROR);
        $this->assertFalse($decoded['success']);
        $this->assertSame(['IP Addresses [' . $ips[1] . '] do not exist in the local database.'], $decoded['content']);
    }
}
