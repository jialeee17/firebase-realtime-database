<?php

namespace App\Http\Services;

use App\Traits\Curl;
use Exception;

class Hap2pyService
{
    use Curl;

    public function getCmsUserChatStatus($id)
    {
        $url = env('HAP2PY_API_URL') . '/post/get-cms-user-chat-status';

        $response = $this->curl($url);
        $data = json_decode($response, true);

        return $data;
    }

    public function getChatStatusByName($name)
    {
        $url = env('HAP2PY_API_URL') . '/post/get-chats-status-by-name';

        $response = $this->curl($url);
        $data = json_decode($response, true);

        return $data;
    }

    public function getBusinessHourStatus()
    {
        $url = env('HAP2PY_API_URL') . '/post/get-business-hour-status';

        $response = $this->curl($url);
        $data = json_decode($response, true);

        return $data;
    }
}
