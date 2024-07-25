<?php
namespace App\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use GuzzleHttp\Client;
use App\Models\PollData;

class MakanSiangCimahi extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'makan:siang-cimahi';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command untuk mengirim polling makan siang di Cimahi';

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
        $tanggalHariIni = date('Y-m-d');
        $question = "Cek {$tanggalHariIni}";
        // $options = $this->getHariIniOptions();
        $options = ["iya", "jgn isi disini"];

        if ($this->shouldSendPollToday()) {
            $response = $this->sendPoll($client, $chatId, $question, $options);

            if ($response && isset($response['result']['message_id'])) {
                $this->savePollData($chatId, $response, $tanggalHariIni);
            }
        }

        $client->post('setWebhook', [
            'json' => ['url' => 'https://jacob.adiyatan.com/api/setWebHook']
        ]);

        return 0;
    }

    private function sendPoll($client, $chatId, $question, $options)
    {
        try {
            $response = $client->post('sendPoll', [
                'form_params' => [
                    'chat_id' => $chatId,
                    'question' => $question,
                    'options' => json_encode($options),
                    'is_anonymous' => false
                ]
            ]);

            return json_decode($response->getBody(), true);
        } catch (Exception $e) {
            echo 'Error sending poll: ' . $e->getMessage();
            return null;
        }
    }

    private function getHariIniOptions()
    {
        $hariIni = date('l'); // Nama hari dalam bahasa Inggris
        switch ($hariIni) {
            case 'Monday':
            case 'Thursday':
                return ["Hadir ke Kantor & Makan Siang", "Hadir ke Kantor tapi tidak makan siang", "Tidak hadir ke kantor", "puasa"];
            case 'Tuesday':
            case 'Wednesday':
            case 'Friday':
                return ["iya", "tidak", "tidak tapi ke kantor"];
            default:
                return ["Hadir ke Kantor & Makan Siang", "Hadir ke Kantor tapi tidak makan siang", "Tidak hadir ke kantor"];
        }
    }

    private function shouldSendPollToday()
    {
        $hariIni = date('l'); // Nama hari dalam bahasa Inggris
        return !in_array($hariIni, ['Sunday', 'Saturday']);
    }

    private function savePollData($chatId, $response, $tanggal)
    {
        try {
            PollData::create([
                'chat_id' => $chatId,
                'message_id' => $response['result']['message_id'],
                'poll_id' => $response['result']['poll']['id'],
                'options' => json_encode($response['result']['poll']['options']),
                'total_voter_count' => $response['result']['poll']['total_voter_count'],
                'date' => $tanggal,
            ]);
            echo 'Poll data saved successfully.';
        } catch (Exception $e) {
            echo 'Error saving poll data: ' . $e->getMessage();
        }
    }
}
