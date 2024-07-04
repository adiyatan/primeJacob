<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use GuzzleHttp\Client;

class telegramController extends Controller
{
    protected $telegramApiUrl;
    protected $apiToken;

    public function __construct()
    {
        $this->telegramApiUrl = getenv('TELEGRAM_API_URL');
    }

    public function setWebhook()
    {
        $client = new Client();
        $response = $client->post($this->telegramApiUrl . 'setWebhook', [
            'json' => ['url' => route('api.setWebhook')]
        ]);

        return response()->json(json_decode($response->getBody(), true));
    }

    public function handleWebhook(Request $request)
    {
        $update = $request->all();
        Log::info('Webhook received:', $update);

        if (isset($update['message'])) {
            $this->handleMessage($update['message']);
        }

        return response()->json(['status' => 'success']);
    }

    protected function handleMessage($message)
    {
        $client = new Client();
        $chatId = $message['chat']['id'];
        $text = $message['text'];

        if ($text === '/chekgrupID') {
            $responseText = 'The group ID is: ' . $chatId;
            $this->sendMessage($client, $chatId, $responseText);
        }
    }

    protected function sendMessage($client, $chatId, $text)
    {
        $client->post($this->telegramApiUrl . 'sendMessage', [
            'json' => [
                'chat_id' => $chatId,
                'text' => $text
            ]
        ]);
    }
}
