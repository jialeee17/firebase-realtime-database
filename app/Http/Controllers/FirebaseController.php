<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use App\Http\Responses\ApiErrorResponse;
use App\Http\Responses\ApiSuccessResponse;
use App\Http\Services\RealtimeDatabaseService;

class FirebaseController extends Controller
{
    private $realtimeDatabaseService;

    public function __construct(RealtimeDatabaseService $realtimeDatabaseService)
    {
        $this->realtimeDatabaseService = $realtimeDatabaseService;
    }

    /* -------------------------------------------------------------------------- */
    /*                              Realtime Database                             */
    /* -------------------------------------------------------------------------- */
    public function storeMessage(Request $request)
    {
        try {
            $request->validate([
                'admin_id' => ['required', 'integer'],
                'customer_id' => ['required', 'integer'],
                'message' => ['required', 'array:content,image_path,is_admin'],
                'message.content' => ['required', 'string'],
                'message.image_path' => ['nullable', 'string'],
                'message.is_admin' => ['required', 'boolean'],
            ]);

            // Default read status
            $message = $request->message;
            $message['is_read'] = false;

            $this->realtimeDatabaseService->storeMessage($request->admin_id, $request->customer_id, $message);

            return new ApiSuccessResponse(
                [],
                'Message stored successfully!',
            );
        } catch (Exception $e) {
            return new ApiErrorResponse(
                $e->getMessage(),
                $e,
            );
        }
    }
}
