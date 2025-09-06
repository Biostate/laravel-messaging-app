<?php

namespace App\Http\Controllers\Api;

use App\Enums\CampaignRecipientStatus;
use App\Http\Resources\MessageResource;
use App\Http\Resources\MessageStatsResource;
use App\Services\Contracts\CampaignRecipientServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MessageController extends BaseApiController
{
    public function __construct(
        private CampaignRecipientServiceInterface $campaignRecipientService
    ) {}

    /**
     * @OA\Get(
     *     path="/api/messages",
     *     summary="Get list of messages",
     *     description="Retrieve a paginated list of messages with optional filtering by status",
     *     tags={"Messages"},
     *
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filter messages by status",
     *         required=false,
     *
     *         @OA\Schema(type="string", enum={"pending", "sent", "failed"}, default="sent")
     *     ),
     *
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         description="Number of messages per page",
     *         required=false,
     *
     *         @OA\Schema(type="integer", minimum=1, maximum=100, default=50)
     *     ),
     *
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number",
     *         required=false,
     *
     *         @OA\Schema(type="integer", minimum=1, default=1)
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Successful response",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *
     *                 @OA\Items(ref="#/components/schemas/Message")
     *             ),
     *
     *             @OA\Property(
     *                 property="meta",
     *                 type="object",
     *                 @OA\Property(property="total", type="integer", example=25),
     *                 @OA\Property(property="page", type="integer", example=1),
     *                 @OA\Property(property="limit", type="integer", example=50),
     *                 @OA\Property(property="status", type="string", example="sent")
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $status = $request->query('status', CampaignRecipientStatus::Sent->value);
        $limit = (int) $request->query('limit', 50);
        $page = (int) $request->query('page', 1);

        $statusEnum = CampaignRecipientStatus::tryFrom($status) ?? CampaignRecipientStatus::Sent;

        $campaignRecipients = $this->campaignRecipientService
            ->getByStatusWithLimit($statusEnum, $limit * $page)
            ->take($limit);

        return response()->json([
            'success' => true,
            'data' => MessageResource::collection($campaignRecipients),
            'meta' => [
                'total' => $campaignRecipients->count(),
                'page' => $page,
                'limit' => $limit,
                'status' => $status,
            ],
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/messages/{id}",
     *     summary="Get specific message",
     *     description="Retrieve details of a specific message by ID",
     *     tags={"Messages"},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Message ID",
     *         required=true,
     *
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Successful response",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/Message")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Message not found",
     *
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
    public function show(int $id): JsonResponse
    {
        $campaignRecipient = $this->campaignRecipientService->getById($id);

        if (! $campaignRecipient) {
            return response()->json([
                'success' => false,
                'message' => 'Message not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => new MessageResource($campaignRecipient),
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/messages/stats/overview",
     *     summary="Get message statistics",
     *     description="Retrieve overview statistics of all messages including counts and success rate",
     *     tags={"Messages"},
     *
     *     @OA\Response(
     *         response=200,
     *         description="Successful response",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/MessageStats")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
    public function stats(): JsonResponse
    {
        $totalMessages = $this->campaignRecipientService->getAll()->count();
        $sentMessages = $this->campaignRecipientService->getByStatus(CampaignRecipientStatus::Sent)->count();
        $pendingMessages = $this->campaignRecipientService->getByStatus(CampaignRecipientStatus::Pending)->count();
        $failedMessages = $this->campaignRecipientService->getByStatus(CampaignRecipientStatus::Failed)->count();

        $statsData = [
            'total_messages' => $totalMessages,
            'sent_messages' => $sentMessages,
            'pending_messages' => $pendingMessages,
            'failed_messages' => $failedMessages,
            'success_rate' => $totalMessages > 0 ? round(($sentMessages / $totalMessages) * 100, 2) : 0,
        ];

        return response()->json([
            'success' => true,
            'data' => new MessageStatsResource($statsData),
        ]);
    }
}
