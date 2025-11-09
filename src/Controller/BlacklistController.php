<?php declare(strict_types=1);

namespace App\Controller;

use App\Model\RequestBulk;
use App\Model\Response;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Nelmio\ApiDocBundle\Attribute\Model;
use App\Utils\Managers\BlacklistManager;
use Symfony\Component\HttpFoundation\RequestStack;

class BlacklistController extends AbstractController
{
    private BlacklistManager $blacklistManager;
    private RequestStack $requestStack;

    public function __construct(
        BlacklistManager $blacklistManager,
        RequestStack $requestStack
    ) {
        $this->blacklistManager = $blacklistManager;
        $this->requestStack = $requestStack;
    }

    #[Route('/api/fetch-blacklist', name: 'api_fetch_blacklist', methods: ['GET'])]
    #[OA\Get(
        path: '/api/fetch-blacklist',
        summary: 'Fetch all IP addresses that are in the blacklist.',
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
    #[OA\Tag(name: 'blacklist')]
    public function fetch(): JsonResponse
    {
        return $this->blacklistManager->fetch(new Response());
    }

    #[Route('/api/blacklist-add-single/{ipAddress}', name: 'api_blacklist_add_single', methods: ['PUT'])]
    #[OA\Put(
        path: '/api/blacklist-add-single/{ipAddress}',
        summary: 'Add an IP to the blacklist.',
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
    #[OA\Tag(name: 'blacklist')]
    public function addSingle(string $ipAddress): JsonResponse
    {
        return $this->blacklistManager->addSingle(new Response(), $ipAddress);
    }

    #[Route('/api/blacklist-delete-single/{ipAddress}', name: 'api_blacklist_delete_single', methods: ['DELETE'])]
    #[OA\Delete(
        path: '/api/blacklist-delete-single/{ipAddress}',
        summary: 'Remove an IP from the blacklist.',
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
    #[OA\Tag(name: 'blacklist')]
    public function deleteSingle(string $ipAddress): JsonResponse
    {
        return $this->blacklistManager->deleteSingle(new Response(), $ipAddress);
    }

    #[Route('/api/bulk-blacklist', name: 'api_bulk_blacklist', methods: ['POST'])]
    #[OA\Post(
        path: '/api/bulk-blacklist',
        summary: 'Endpoint for managing multiple IPs in the blacklist at the time.',
        parameters: [
            new OA\Parameter(
                name: 'requestBody',
                description: 'Request body.',
                in: 'query',
                required: true,
                allowEmptyValue: false,
                schema: new OA\Schema(ref: new Model(type: RequestBulk::class)),
                example: '{"action": "ADD", "ipAddresses": ["1.1.1.1", "2.2.2.2", "3.3.3.3"]}',
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
    #[OA\Tag(name: 'blacklist')]
    public function bulk(): JsonResponse
    {
        return $this->blacklistManager->bulk(
            new Response(),
            json_decode($this->requestStack->getCurrentRequest()->get('requestBody'), true)
        );
    }
}