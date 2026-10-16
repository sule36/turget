<?php

namespace App\Http\Controllers;

use App\Services\MidtransService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MidtransWebhookController extends Controller
{
    public function __construct(
        protected MidtransService $midtransService
    ) {}

    /**
     * Handle incoming payment notification webhook from Midtrans.
     */
    public function handle(Request $request): JsonResponse
    {
        $payload = $request->all();

        $result = $this->midtransService->handleNotification($payload);

        if (($result['status'] ?? '') === 'error') {
            return response()->json($result, 400);
        }

        return response()->json(['status' => 'ok', 'message' => 'Notification handled successfully']);
    }
}
