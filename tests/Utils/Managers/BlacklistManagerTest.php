<?php declare(strict_types=1);

namespace App\Tests\Utils\Managers;

use App\Entity\Blacklist;
use App\Model\Response as ModelResponse;
use App\Utils\Managers\BlacklistManager;
use App\Utils\Providers\IpStackProvider;
use App\Utils\Validators\IpValidator;
use App\Utils\Validators\RequestInputValidator;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class BlacklistManagerTest extends TestCase
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

        $this->requestValidator->method('checkSingle')->willReturn(null);
        $this->requestValidator->method('checkBulk')->willReturn(null);

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

        // Use real IpValidator so jsonResponse works
        $params = $this->createMock(ParameterBagInterface::class);
        $this->ipValidator = new IpValidator($this->em, $this->serializer, $params);
    }

    private function createManager(): BlacklistManager
    {
        return new BlacklistManager($this->em, $this->requestValidator, $this->ipValidator, $this->ipStackProvider, $this->serializer);
    }

    public function testFetchEmptyList(): void
    {
        $repo = $this->createMock(EntityRepository::class);
        $repo->method('findAll')->willReturn([]);
        $this->em->method('getRepository')->willReturn($repo);

        $manager = $this->createManager();
        $response = new ModelResponse();
        $json = $manager->fetch($response);

        $decoded = json_decode($json->getContent(), true, 512, JSON_THROW_ON_ERROR);
        $this->assertTrue($decoded['success']);
        $this->assertSame([], $decoded['content']);
    }

    public function testFetchReturnsList(): void
    {
        $b1 = (new Blacklist())->setIp('1.1.1.1');
        $b2 = (new Blacklist())->setIp('8.8.8.8');

        $repo = $this->createMock(EntityRepository::class);
        $repo->method('findAll')->willReturn([$b1, $b2]);
        $this->em->method('getRepository')->willReturn($repo);

        $manager = $this->createManager();
        $response = new ModelResponse();
        $json = $manager->fetch($response);

        $decoded = json_decode($json->getContent(), true, 512, JSON_THROW_ON_ERROR);
        $this->assertTrue($decoded['success']);
        $this->assertSame(['1.1.1.1', '8.8.8.8'], $decoded['content']);
    }

    public function testAddSingleSuccess(): void
    {
        $ip = '2.2.2.2';
        $repo = $this->createMock(EntityRepository::class);
        $repo->method('findOneBy')->with(['ip' => $ip])->willReturn(null);
        $this->em->method('getRepository')->willReturn($repo);

        // Expect persist of new Blacklist entity and flush
        $this->em->expects($this->once())->method('persist')->with($this->callback(function ($entity) use ($ip) {
            return $entity instanceof Blacklist && $entity->getIp() === $ip;
        }));
        $this->em->expects($this->once())->method('flush');

        $manager = $this->createManager();
        $response = new ModelResponse();
        $json = $manager->addSingle($response, $ip);

        $decoded = json_decode($json->getContent(), true, 512, JSON_THROW_ON_ERROR);
        $this->assertTrue($decoded['success']);
        $this->assertSame(['IP ' . $ip . ' was added to the blacklist successfully.'], $decoded['content']);
    }

    public function testAddSingleAlreadyExists(): void
    {
        $ip = '2.2.2.2';
        $repo = $this->createMock(EntityRepository::class);
        $repo->method('findOneBy')->with(['ip' => $ip])->willReturn(new Blacklist());
        $this->em->method('getRepository')->willReturn($repo);

        $this->em->expects($this->never())->method('persist');
        $this->em->expects($this->never())->method('flush');

        $manager = $this->createManager();
        $response = new ModelResponse();
        $json = $manager->addSingle($response, $ip);

        $decoded = json_decode($json->getContent(), true, 512, JSON_THROW_ON_ERROR);
        $this->assertFalse($decoded['success']);
        $this->assertSame(['IP ' . $ip . ' already exists in the blacklist.'], $decoded['content']);
    }

    public function testDeleteSingleSuccess(): void
    {
        $ip = '3.3.3.3';
        $repo = $this->createMock(EntityRepository::class);
        $entity = (new Blacklist())->setIp($ip);
        $repo->method('findOneBy')->with(['ip' => $ip])->willReturn($entity);
        $this->em->method('getRepository')->willReturn($repo);

        $this->em->expects($this->once())->method('remove')->with($entity);
        $this->em->expects($this->once())->method('flush');

        $manager = $this->createManager();
        $response = new ModelResponse();
        $json = $manager->deleteSingle($response, $ip);

        $decoded = json_decode($json->getContent(), true, 512, JSON_THROW_ON_ERROR);
        $this->assertTrue($decoded['success']);
        $this->assertSame(['IP ' . $ip . ' was removed from the blacklist successfully.'], $decoded['content']);
    }

    public function testDeleteSingleNotExists(): void
    {
        $ip = '3.3.3.3';
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
        $this->assertSame(['IP ' . $ip . ' does not exist in the blacklist.'], $decoded['content']);
    }

    public function testBulkAddSuccess(): void
    {
        $ips = ['4.4.4.4', '5.5.5.5'];
        $repo = $this->createMock(EntityRepository::class);
        $repo->method('findOneBy')->willReturnOnConsecutiveCalls(null, null);
        $this->em->method('getRepository')->willReturn($repo);

        // Persist called for each, flush once
        $this->em->expects($this->exactly(2))->method('persist')->with($this->isInstanceOf(Blacklist::class));
        $this->em->expects($this->once())->method('flush');

        $manager = $this->createManager();
        $response = new ModelResponse();
        $json = $manager->bulk($response, ['action' => 'ADD', 'ipAddresses' => $ips]);

        $decoded = json_decode($json->getContent(), true, 512, JSON_THROW_ON_ERROR);
        $this->assertTrue($decoded['success']);
        $this->assertSame(['IP Addresses [' . implode(', ', $ips) . '] were added to the blacklist successfully.'], $decoded['content']);
    }

    public function testBulkAddWithDuplicates(): void
    {
        $ips = ['4.4.4.4', '5.5.5.5'];
        $repo = $this->createMock(EntityRepository::class);
        // First does not exist, second exists
        $repo->method('findOneBy')->willReturnOnConsecutiveCalls(null, new Blacklist());
        $this->em->method('getRepository')->willReturn($repo);

        $this->em->expects($this->once())->method('persist')->with($this->isInstanceOf(Blacklist::class));
        $this->em->expects($this->never())->method('flush');

        $manager = $this->createManager();
        $response = new ModelResponse();
        $json = $manager->bulk($response, ['action' => 'ADD', 'ipAddresses' => $ips]);

        $decoded = json_decode($json->getContent(), true, 512, JSON_THROW_ON_ERROR);
        $this->assertFalse($decoded['success']);
        $this->assertSame(['IP Addresses [' . $ips[1] . '] already exist in the blacklist.'], $decoded['content']);
    }

    public function testBulkDeleteSuccess(): void
    {
        $ips = ['9.9.9.9', '7.7.7.7'];
        $repo = $this->createMock(EntityRepository::class);
        $repo->method('findOneBy')->willReturnOnConsecutiveCalls(new Blacklist(), new Blacklist());
        $this->em->method('getRepository')->willReturn($repo);

        $this->em->expects($this->exactly(2))->method('remove')->with($this->isInstanceOf(Blacklist::class));
        $this->em->expects($this->once())->method('flush');

        $manager = $this->createManager();
        $response = new ModelResponse();
        $json = $manager->bulk($response, ['action' => 'DELETE', 'ipAddresses' => $ips]);

        $decoded = json_decode($json->getContent(), true, 512, JSON_THROW_ON_ERROR);
        $this->assertTrue($decoded['success']);
        $this->assertSame(['IP Addresses [' . implode(', ', $ips) . '] were removed from the blacklist successfully.'], $decoded['content']);
    }

    public function testBulkDeleteWithErrors(): void
    {
        $ips = ['9.9.9.9', '7.7.7.7'];
        $repo = $this->createMock(EntityRepository::class);
        $repo->method('findOneBy')->willReturnOnConsecutiveCalls(new Blacklist(), null);
        $this->em->method('getRepository')->willReturn($repo);

        $this->em->expects($this->once())->method('remove')->with($this->isInstanceOf(Blacklist::class));
        $this->em->expects($this->never())->method('flush');

        $manager = $this->createManager();
        $response = new ModelResponse();
        $json = $manager->bulk($response, ['action' => 'DELETE', 'ipAddresses' => $ips]);

        $decoded = json_decode($json->getContent(), true, 512, JSON_THROW_ON_ERROR);
        $this->assertFalse($decoded['success']);
        $this->assertSame(['IP Addresses [' . $ips[1] . '] do not exist in the blacklist.'], $decoded['content']);
    }
}