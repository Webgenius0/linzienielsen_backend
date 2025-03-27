<?php

namespace App\Services\API\V1\Lulu;

use Illuminate\Support\Facades\Http;

class LuluService
{
    private static function getAccessToken()
    {
        $response = Http::asForm()->withHeaders([
            'Authorization' => 'Basic ' . base64_encode(env('LULU_CLIENT_KEY') . ':' . env('LULU_CLIENT_SECRET'))
        ])->post(env('LULU_AUTH_URL'), [
            'grant_type' => 'client_credentials',
        ]);

        if ($response->successful()) {
            return $response->json()['access_token'];
        }

        return null; // Handle errors properly
    }

    public static function createPrintJob($printJobData)
    {
        $accessToken = self::getAccessToken();
        if (!$accessToken) {
            return ['error' => 'Failed to get access token'];
        }

        $response = Http::withToken($accessToken)
            ->withHeaders([
                'Cache-Control' => 'no-cache',
                'Content-Type' => 'application/json',
            ])
            ->post(env('LULU_PRINT_JOB_URL'), $printJobData);

        return $response->json();
    }
}
