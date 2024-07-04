<?php
namespace App\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use GuzzleHttp\Client;
use PDO;
use PDOException;
use Illuminate\Support\Facades\DB;

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

        $host = env('DB_HOST');
        $db = env('DB_DATABASE');
        $user = env('DB_USERNAME');
        $pass = env('DB_PASSWORD');
        $charset = 'utf8mb4';
        $dsn = "mysql:host=$host;dbname=$db;charset=$charset";

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        $pdo = new PDO($dsn, $user, $pass, $options);

        $chatId = "-4267585793";
        $tanggalHariIni = date('Y-m-d');
        $question = "Absen Makan {$tanggalHariIni}";
        $options = $this->getHariIniOptions();

        if ($this->shouldSendPollToday()) {
            $response = $this->sendPoll($client, $chatId, $question, $options);
            if ($response && isset($response['result']['poll']['id'])) {
                $pollId = $response['result']['poll']['id'];
                $optionsJson = json_encode($options);

                echo "Poll ID: $pollId\n";
                echo "Options JSON: $optionsJson\n";

                try {
                    $stmt = $pdo->prepare("INSERT INTO poll_data (poll_id, options, date, chat_id) VALUES (?, ?, ?, ?)");
                    $stmt->execute([$pollId, $optionsJson, $tanggalHariIni, $chatId]);

                    $stmt = $pdo->prepare("SELECT * FROM check_add WHERE chat_id = ?");
                    $stmt->execute([$chatId]);
                    $result = $stmt->fetch();

                    if (!$result) {
                        $stmt = $pdo->prepare("INSERT INTO check_add (chat_id, isAddMember) VALUES (?, false)");
                        $stmt->execute([$chatId]);
                    } else {
                        $stmt = $pdo->prepare("UPDATE check_add SET isAddMember = false WHERE chat_id = ?");
                        $stmt->execute([$chatId]);
                    }

                    $member = $pdo->prepare("SELECT username FROM members_bandung");
                    $member->execute();

                    $this->sendMessage($client, $chatId, "listening and sending polling data to webhook api.adiyatan.com");
                    $usernames = [];
                    while ($row = $member->fetch()) {
                        $username = $row['username'];
                        $usernames[] = "@$username";
                    }
                    $message = implode(", ", $usernames) . " \n dear all, please fill in the poll above. Thank you.";
                    $this->sendMessage($client, $chatId, $message);
                    $this->sendMessage($client, $chatId, "Powered by: Adiyatan.com");
                } catch (PDOException $e) {
                    echo "Error: " . $e->getMessage();
                }
            } else {
                echo "Failed to send poll or retrieve poll ID.";
            }
        }

        $client->post('https://api.telegram.org/bot7463413042:AAFnDLUPOTF-pqp6nRpEsCcOtwTAqacqlBA/setWebhook', [
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

    private function sendMessage($client, $chatId, $text)
    {
        try {
            $response = $client->post('sendMessage', [
                'form_params' => [
                    'chat_id' => $chatId,
                    'text' => $text
                ]
            ]);
            return json_decode($response->getBody(), true);
        } catch (Exception $e) {
            echo 'Error sending message: ' . $e->getMessage();
            return null;
        }
    }

    private function getHariIniOptions()
    {
        $hariIni = date('l'); // Nama hari dalam bahasa Inggris
        switch ($hariIni) {
            case 'Monday':
            case 'Thursday':
                return ["iya", "tidak", "tidak tapi ke kantor", "puasa"];
            case 'Tuesday':
            case 'Wednesday':
            case 'Friday':
                return ["iya", "tidak", "tidak tapi ke kantor"];
            default:
                return ["aku", "tidak"];
        }
    }

    private function shouldSendPollToday()
    {
        $hariIni = date('l'); // Nama hari dalam bahasa Inggris
        return !in_array($hariIni, ['Sunday', 'Saturday']);
    }
}
