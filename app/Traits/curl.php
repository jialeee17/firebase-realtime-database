<?php

namespace App\Traits;

use Illuminate\Http\Request;

trait Curl
{
    public function curl($url, $params)
    {
        $ch = curl_init();

        /* Set options and execute */
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt(
            $ch,
            CURLOPT_POSTFIELDS,
            http_build_query(array_merge($params, [
                'token' => env('HAP2PY_API_TOKEN')
            ]))
        );
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept: application/json',
            'Content-Type: application/json',
            'Authorization: Bearer ' . env('HAP2PY_API_TOKEN'),
        ]);
        $output = curl_exec($ch);

        /* Close handle and return output */
        curl_close($ch);

        return $output;
    }
}
