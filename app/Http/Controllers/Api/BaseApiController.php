<?php

namespace App\Http\Controllers\Api;

use Illuminate\Routing\Controller;

/**
 * @OA\Info(
 *     title="Laravel Messaging App API",
 *     version="1.0.0",
 *     description="API for managing SMS messaging campaigns with rate limiting and comprehensive logging",
 *
 *     @OA\Contact(
 *         email="support@example.com"
 *     ),
 *
 *     @OA\License(
 *         name="MIT",
 *         url="https://opensource.org/licenses/MIT"
 *     )
 * )
 *
 * @OA\Server(
 *     url=L5_SWAGGER_CONST_HOST,
 *     description="API Server"
 * )
 *
 * @OA\Tag(
 *     name="Messages",
 *     description="Message management endpoints"
 * )
 *
 * @OA\Schema(
 *     schema="Message",
 *     type="object",
 *
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="campaign_id", type="integer", example=1),
 *     @OA\Property(property="recipient_id", type="integer", example=1),
 *     @OA\Property(property="phone_number", type="string", example="+1234567890"),
 *     @OA\Property(property="message_content", type="string", example="Welcome to our service!"),
 *     @OA\Property(property="status", type="string", enum={"pending", "sent", "failed"}, example="sent"),
 *     @OA\Property(property="sent_at", type="string", format="date-time", example="2024-01-15T10:30:00Z"),
 *     @OA\Property(property="failure_reason", type="string", nullable=true, example=null),
 *     @OA\Property(property="message_id", type="string", nullable=true, example="msg_123456"),
 *     @OA\Property(property="cached_message_id", type="string", nullable=true, example="msg_123456"),
 *     @OA\Property(property="cached_sent_at", type="string", format="date-time", nullable=true, example="2024-01-15T10:30:00Z")
 * )
 *
 * @OA\Schema(
 *     schema="MessageStats",
 *     type="object",
 *
 *     @OA\Property(property="total_messages", type="integer", example=1000),
 *     @OA\Property(property="sent_messages", type="integer", example=950),
 *     @OA\Property(property="pending_messages", type="integer", example=30),
 *     @OA\Property(property="failed_messages", type="integer", example=20),
 *     @OA\Property(property="success_rate", type="number", format="float", example=95.0)
 * )
 *
 * @OA\Schema(
 *     schema="ApiResponse",
 *     type="object",
 *
 *     @OA\Property(property="success", type="boolean", example=true),
 *     @OA\Property(property="data", type="object"),
 *     @OA\Property(property="meta", type="object", nullable=true)
 * )
 *
 * @OA\Schema(
 *     schema="ErrorResponse",
 *     type="object",
 *
 *     @OA\Property(property="success", type="boolean", example=false),
 *     @OA\Property(property="message", type="string", example="Error message")
 * )
 */
class BaseApiController extends Controller
{
    //
}
