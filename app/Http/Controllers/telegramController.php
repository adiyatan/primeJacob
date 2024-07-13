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
        } elseif (isset($update['callback_query'])) {
            $this->handleCallbackQuery($update['callback_query']);
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
            } elseif (strpos(strtolower($text), 'jacob') !== false) {
                $responseText = $this->getGeminiResponse($userName, $text);
                $this->sendMessage($client, $chatId, $responseText);
            } elseif ($text === '/startwebapp') {
                $this->sendWebAppButton($client, $chatId);
            }
        } else {
            Log::info('Received message without text:', $message);
        }
    }

    protected function handleCallbackQuery($callbackQuery)
    {
        $client = new \GuzzleHttp\Client();
        $callbackQueryId = $callbackQuery['id'];
        $callbackData = $callbackQuery['data'];
        $chatId = $callbackQuery['message']['chat']['id'];

        if ($callbackData === 'absen_makan') {
            $this->answerCallbackQuery($client, $callbackQueryId, 'Anda memilih Absen Makan. Klik OK untuk melanjutkan.');
        } elseif ($callbackData === 'menu_makan') {
            $this->answerCallbackQuery($client, $callbackQueryId, 'Anda memilih Menu Makan. Klik OK untuk melanjutkan.');
        } elseif ($callbackData === 'close') {
            $this->answerCallbackQuery($client, $callbackQueryId, 'Pilihan telah ditutup.');
        }
    }

    protected function answerCallbackQuery($client, $callbackQueryId, $text)
    {
        $client->post($this->telegramApiUrl . 'answerCallbackQuery', [
            'json' => [
                'callback_query_id' => $callbackQueryId,
                'text' => $text,
                'show_alert' => true
            ]
        ]);
    }

    protected function getGeminiResponse($userName, $text)
    {
        try {
            $response = $this->geminiClient->geminiPro()->generateContent(
                new TextPart("Kamu adalah asisten virtual bernama jacob, tolong jawab text berikut dengan bahasa yang tidak kaku: " . $text . "dan yang mengirim pesan adalah " . $userName . " panggil namanya dengan panggilan kak dan tambahkan emot diakhir chat seta balas dalam 1 response saja.")
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

    protected function sendWebAppButton($client, $chatId)
    {
        $payload = [
            'chat_id' => $chatId,
            'text' => 'Open the Web App:',
            'reply_markup' => [
                'inline_keyboard' => [
                    [
                        [
                            'text' => 'Open Web App',
                            'web_app' => [
                                'url' => 'https://jacobshop.adiyatan.com/' // Replace with your web app URL
                            ]
                        ]
                    ]
                ]
            ]
        ];

        // Log the payload for debugging
        Log::info('Sending Web App button with payload:', $payload);

        try {
            $response = $client->post($this->telegramApiUrl . 'sendMessage', [
                'json' => $payload
            ]);

            Log::info('Web App button response:', json_decode($response->getBody(), true));
        } catch (\Exception $e) {
            Log::error("Failed to send Web App button: " . $e->getMessage());
        }
    }
}
