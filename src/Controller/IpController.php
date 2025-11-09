<?php declare(strict_types=1);

namespace App\Controller;

use App\Model\RequestBulk;
use App\Model\Response;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Nelmio\ApiDocBundle\Attribute\Model;
use App\Utils\Managers\IpManager;
use Symfony\Component\HttpFoundation\RequestStack;

class IpController extends AbstractController
{
    private IpManager $ipManager;
    private RequestStack $requestStack;

    public function __construct(
        IpManager $ipManager,
        RequestStack $requestStack
    ) {
        $this->ipManager = $ipManager;
        $this->requestStack = $requestStack;
    }

    #[Route('/api/fetch-single/{ipAddress}', name: 'api_fetch_single', methods: ['GET'])]
    #[OA\Get(
        path: '/api/fetch-single/{ipAddress}',
        summary: 'Fetch IP data.',
        parameters: [
            new OA\Parameter(
                name: 'ipAddress',
                description: 'IP Address.',
                in: 'path',
                required: true,
                allowEmptyValue: false,
                schema: new OA\Schema(type: 'string'),
                example: '1.1.1.1'
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Returns success response.',
                content: new OA\JsonContent(ref: new Model(type: Response::class))
            ),
            new OA\Response(
                response: 404,
                description: 'Returns error response.',
                content: new OA\JsonContent(ref: new Model(type: Response::class))
            )
        ]
    )]
    #[OA\Tag(name: 'ip')]
    public function fetchData(string $ipAddress): JsonResponse
    {
        return $this->ipManager->fetch(new Response(), $ipAddress);
    }

    #[Route('/api/delete-single/{ipAddress}', name: 'api_delete_single', methods: ['DELETE'])]
    #[OA\Delete(
        path: '/api/delete-single/{ipAddress}',
        summary: 'Delete IP data from the local database.',
        parameters: [
            new OA\Parameter(
                name: 'ipAddress',
                description: 'IP Address.',
                in: 'path',
                required: true,
                allowEmptyValue: false,
                schema: new OA\Schema(type: 'string'),
                example: '1.1.1.1'
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Returns success response.',
                content: new OA\JsonContent(ref: new Model(type: Response::class))
            ),
            new OA\Response(
                response: 404,
                description: 'Returns error response.',
                content: new OA\JsonContent(ref: new Model(type: Response::class))
            )
        ]
    )]
    #[OA\Tag(name: 'ip')]
    public function deleteSingle(string $ipAddress): JsonResponse
    {
        return $this->ipManager->deleteSingle(new Response(), $ipAddress);
    }

    #[Route('/api/bulk-ip', name: 'api_bulk_ip', methods: ['POST'])]
    #[OA\Post(
        path: '/api/bulk-ip',
        summary: 'Endpoint for managing multiple IPs in the local database at the time.',
        parameters: [
            new OA\Parameter(
                name: 'requestBody',
                description: 'Request body.',
                in: 'query',
                required: true,
                allowEmptyValue: false,
                schema: new OA\Schema(ref: new Model(type: RequestBulk::class)),
                example: '{"action": "DELETE", "ipAddresses": ["1.1.1.1", "2.2.2.2", "3.3.3.3"]}',
                content: new OA\JsonContent(ref: new Model(type: RequestBulk::class))
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Returns success response.',
                content: new OA\JsonContent(ref: new Model(type: Response::class))
            ),
            new OA\Response(
                response: 404,
                description: 'Returns error response.',
                content: new OA\JsonContent(ref: new Model(type: Response::class))
            )
        ]
    )]
    #[OA\Tag(name: 'ip')]
    public function bulk(): JsonResponse
    {
        return $this->ipManager->bulk(
            new Response(),
            json_decode($this->requestStack->getCurrentRequest()->get('requestBody'), true)
        );
    }
}