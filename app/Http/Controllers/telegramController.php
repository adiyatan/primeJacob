<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use GeminiAPI\Client;
use GeminiAPI\Resources\Parts\TextPart;

class TelegramController extends Controller
{
    protected $telegramApiUrl;
    protected $geminiApiKey;
    protected $geminiClient;

    public function __construct()
    {
        $telegramApiToken = getenv('TELEGRAM_API_URL');
        $this->geminiApiKey = getenv('GEMINI_API_KEY');
        
        if ($telegramApiToken === false) {
            throw new \Exception("TELEGRAM_API_URL environment variable is not set.");
        }
        if ($this->geminiApiKey === false) {
            throw new \Exception("GEMINI_API_KEY environment variable is not set.");
        }

        $this->telegramApiUrl = "https://api.telegram.org/bot$telegramApiToken/";
        $this->geminiClient = new Client($this->geminiApiKey); // Initialize Gemini client
    }

    public function setWebhook()
    {
        $client = new \GuzzleHttp\Client();
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
        $client = new \GuzzleHttp\Client();
        $chatId = $message['chat']['id'];
        $userName = $message['from']['first_name'];

        if (isset($message['text'])) {
            $text = $message['text'];

            if ($text === '/chekgrupID') {
                $responseText = 'The group ID is: ' . $chatId;
                $this->sendMessage($client, $chatId, $responseText);
            }
            if (strpos(strtolower($text), 'jacob') !== false) {
                $responseText = $this->getGeminiResponse($userName, $text);
                $this->sendMessage($client, $chatId, $responseText);
            }
        } else {
            Log::info('Received message without text:', $message);
        }
    }

    protected function getGeminiResponse($userName, $text)
    {
        try {
            $response = $this->geminiClient->geminiPro()->generateContent(
                new TextPart("Kamu adalah asisten virtual, tolong jawab text berikut dengan bahasa yang tidak kaku: " . $text)
            );

            return $response->text();
        } catch (\Exception $e) {
            Log::error("Gemini API request failed: " . $e->getMessage());
            return "Maaf $userName, terjadi kesalahan saat menghubungi API Gemini.";
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
