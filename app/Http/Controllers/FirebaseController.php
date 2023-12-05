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
                'message.content' => ['nullable', 'string', 'required_without_all:message.image_path'],
                'message.image_path' => ['nullable', 'string', 'required_without_all:message.content'],
                'message.is_admin' => ['required', 'boolean'],
            ]);

            $this->realtimeDatabaseService->storeMessage($request->admin_id, $request->customer_id, $request->message);

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
