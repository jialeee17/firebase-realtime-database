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
                'customer_name' => ['nullable', 'string'],
                'content' => ['nullable', 'string', 'required_without:image_path'],
                'image_path' => ['nullable', 'string', 'required_without:content'],
                'is_admin' => ['required', 'boolean'],
            ]);

            $data = $this->realtimeDatabaseService->storeMessage($request->admin_id, $request->customer_id, $request->customer_name, $request->content, $request->image_path, $request->is_admin);

            return new ApiSuccessResponse(
                $data,
                'Message stored successfully!',
            );
        } catch (Exception $e) {
            return new ApiErrorResponse(
                $e->getMessage(),
                $e,
            );
        }
    }

    public function deleteChat(Request $request)
    {
        try {
            $request->validate([
                'admin_id' => ['required', 'integer'],
                'customer_id' => ['required', 'integer'],
            ]);

            $this->realtimeDatabaseService->deleteChat($request->admin_id, $request->customer_id);

            return new ApiSuccessResponse(
                [],
                'Chat deleted successfully!',
            );
        } catch (Exception $e) {
            return new ApiErrorResponse(
                $e->getMessage(),
                $e,
            );
        }
    }
}
