<?php

namespace App\Http\Services;

use App\Traits\Curl;
use Illuminate\Support\Facades\Http;

class Hap2pyService
{
    use Curl;

    public function getCmsUserChatStatus($params)
    {
        $url = env('HAP2PY_API_URL') . '/cms-user/get-cms-user-chat-status';

        $response = Http::withHeaders([
            'Authorization' => 'Basic ' . env('HAP2PY_API_TOKEN'),
            'mobileType' => 'web',
        ])
            ->attach('aid', $params['aid'] ?? null)
            ->acceptJson()
            ->post($url);

        if (!$response->successful()) {
            $response->throw();
        }

        return $response['data'] ?? [];
    }

    public function getChatStatusByName($params)
    {
        $url = env('HAP2PY_API_URL') . '/general/get-chats-status-by-name';

        $response = Http::withToken(env('HAP2PY_API_TOKEN'))
            ->withHeaders([
                'Authorization' => 'Basic ' . env('HAP2PY_API_TOKEN'),
                'mobileType' => 'web',
            ])
            ->attach('name', $params['name'] ?? null)
            ->acceptJson()
            ->post($url);

        if (!$response->successful()) {
            $response->throw();
        }

        return $response['data'] ?? [];
    }

    public function getBusinessHourStatus()
    {
        $url = env('HAP2PY_API_URL') . '/general/get-business-hour-status';

        $response = Http::withToken(env('HAP2PY_API_TOKEN'))
            ->withHeaders([
                'Authorization' => 'Basic ' . env('HAP2PY_API_TOKEN'),
                'mobileType' => 'web',
            ])
            ->acceptJson()
            ->post($url);

        if (!$response->successful()) {
            $response->throw();
        }

        return $response['data'] ?? [];
    }
}
