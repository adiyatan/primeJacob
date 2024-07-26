<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use GuzzleHttp\Client;
use App\Models\DailyPoll;
use App\Models\BudgetDaily;
use App\Models\BudgetWeekly;
use App\Models\PollData;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Illuminate\Support\Facades\Storage;

class CloseDailyPolls extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'poll:close-daily';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command to close daily polls with today\'s date';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $apiToken = env('TELEGRAM_API_URL');
        $apiUrl = "https://api.telegram.org/bot$apiToken/";
        $client = new Client(['base_uri' => $apiUrl]);
        $chatId = "-1001309342664";

        $today = Carbon::today()->toDateString();
        $yesterday = Carbon::yesterday()->toDateString();

        $polls = PollData::where('date', $today)->get();

        $totalVoters = 0;

        foreach ($polls as $poll) {
            $response = $this->closePoll($client, $poll->chat_id, $poll->message_id);

            if ($response && isset($response['result']['options'])) {
                foreach ($response['result']['options'] as $option) {
                    if (in_array($option['text'], ['Hadir ke Kantor & Makan Siang', 'iya'])) {
                        $totalVoters += $option['voter_count'];
                    }
                }
            }
        }

        $totalAmount = $totalVoters * 20000;
        $remainingBudget = $this->calculateRemainingBudget($totalAmount, $yesterday);

        BudgetDaily::create([
            'tanggal' => $today,
            'total_voters' => $totalVoters,
            'total_amount' => $totalAmount,
            'remaining_budget' => $remainingBudget,
        ]);

        $formattedTotalAmount = number_format($totalAmount, 0, ',', '.');
        $formattedRemainingBudget = number_format($remainingBudget, 0, ',', '.');

        $message = "Total voters for lunch: $totalVoters\nTotal amount: Rp $formattedTotalAmount\nRemaining budget: Rp $formattedRemainingBudget";
        $this->sendMessage($client, $chatId, $message);

        return 0;
    }

    private function closePoll($client, $chatId, $messageId)
    {
        try {
            $response = $client->post('stopPoll', [
                'json' => [
                    'chat_id' => $chatId,
                    'message_id' => $messageId,
                ]
            ]);

            return json_decode($response->getBody(), true);
        } catch (\Exception $e) {
            $this->error('Error closing poll: ' . $e->getMessage());
            return null;
        }
    }

    private function calculateRemainingBudget($totalAmount, $yesterday)
    {
        $lastBudget = BudgetDaily::where('tanggal', $yesterday)->orderBy('tanggal', 'desc')->first();
        $remainingBudget = $lastBudget ? $lastBudget->remaining_budget - $totalAmount : 0;

        return $remainingBudget;
    }

    private function sendMessage($client, $chatId, $message)
    {
        try {
            $client->post('sendMessage', [
                'json' => [
                    'chat_id' => $chatId,
                    'text' => $message,
                ]
            ]);
        } catch (\Exception $e) {
            $this->error('Error sending message: ' . $e->getMessage());
        }
    }
}
