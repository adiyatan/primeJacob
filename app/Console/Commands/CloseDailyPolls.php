<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use GuzzleHttp\Client;
use App\Models\DailyPoll;
use Carbon\Carbon;

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

        $today = Carbon::today()->toDateString();

        $polls = DailyPoll::where('tanggal', $today)->get();

        foreach ($polls as $poll) {
            $this->closePoll($client, $poll->chat_id, $poll->message_id);
        }

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

            return $response;
        } catch (\Exception $e) {
            $this->error('Error closing poll: ' . $e->getMessage());
            return null;
        }
    }
}
