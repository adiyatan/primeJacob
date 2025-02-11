<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use GeminiAPI\Client;
use GeminiAPI\Resources\Parts\TextPart;
use App\Models\PollData;
use Illuminate\Support\Facades\DB;

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
        $this->geminiClient = new Client($this->geminiApiKey);
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

        if (isset($update['poll'])) {
            $this->logPollData($update['poll']);
        } elseif (isset($update['poll_answer'])) {
            $this->logPollAnswer($update['poll_answer']);
        }

        if (isset($update['message'])) {
            $this->handleMessage($update['message']);
        } elseif (isset($update['callback_query'])) {
            $this->handleCallbackQuery($update['callback_query']);
        }

        return response()->json(['status' => 'success']);
    }

    protected function logPollData($poll)
    {
        $exists = DB::table('poll_data')->where('poll_id', $poll['id'])->exists();
        if (!$exists) {
            DB::table('poll_data')->insert([
                'poll_id' => $poll['id'],
                'options' => json_encode($poll['options']),
                'total_voter_count' => $poll['total_voter_count'],
                'date' => now(),
                'chat_id' => $this->getChatIdByPollId($poll['id']),
            ]);
        } else {
            Log::info("Poll data with ID {$poll['id']} already exists.");
        }
    }

    protected function logPollAnswer($pollAnswer)
    {
        $pollId = $pollAnswer['poll_id'];
        $firstName = $pollAnswer['user']['first_name'];

        $existingPollData = PollData::where('poll_id', $pollId)->where('first_name', $firstName)->first();
        $currentHour = now()->hour;

        if ($currentHour <= 2) {
            if ($existingPollData) {
                $existingPollData->update([
                    'date' => now(),
                    'options' => json_encode($pollAnswer['option_ids']),
                ]);
            } else {
                PollData::create([
                    'poll_id' => $pollId,
                    'first_name' => $firstName,
                    'date' => now(),
                    'options' => json_encode($pollAnswer['option_ids']),
                ]);
            }
        }
    }

    protected function getChatIdByPollId($pollId)
    {
        return DB::table('poll_data')->where('poll_id', $pollId)->whereNotNull('chat_id')->value('chat_id');
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
            }
        } else {
            Log::info('Received message without text:', $message);
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
}
