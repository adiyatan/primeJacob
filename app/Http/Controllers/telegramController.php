<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Client;

class TelegramController extends Controller
{
    protected $telegramApiUrl;

    public function __construct()
    {
        $apiToken = getenv('TELEGRAM_API_URL');
        if ($apiToken === false) {
            throw new \Exception("TELEGRAM_API_URL environment variable is not set.");
        }
        $this->telegramApiUrl = "https://api.telegram.org/bot$apiToken/";
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

        // Check if the message contains text
        if (isset($message['text'])) {
            $text = $message['text'];

            if ($text === '/chekgrupID') {
                $responseText = 'The group ID is: ' . $chatId;
                $this->sendMessage($client, $chatId, $responseText);
            }
            if (strpos(strtolower($text), 'jacob') !== false) {
                $response = $client->get('https://jsonplaceholder.typicode.com/posts/1');
                $responseText = json_decode($response->getBody(), true)['title'];
                $this->sendMessage($client, $chatId, $responseText);
            }
        } else {
            Log::info('Received message without text:', $message);
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
