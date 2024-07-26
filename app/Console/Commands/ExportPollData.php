<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PollData;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Illuminate\Support\Facades\Storage;
use GuzzleHttp\Client;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class ExportPollData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'poll:export-excel';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Export poll data to Excel and send via Telegram';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // Retrieve distinct first names and options from poll_data table
        $pollData = PollData::select('first_name', 'options')->get();

        // Path to the template file
        $templatePath = storage_path('app/public/poll_data.xlsx');

        // Load the template file
        $spreadsheet = IOFactory::load($templatePath);
        $sheet = $spreadsheet->getActiveSheet();

        // Start filling the first names and options from cell B6
        $startRow = 6;
        foreach ($pollData as $index => $data) {
            $row = $startRow + $index;
            $sheet->setCellValue("B$row", $data->first_name);

            // Check if options column is not null and mark the appropriate cell based on the options value
            if ($data->options !== null) {
                switch ($data->options) {
                    case "[0]":
                        $sheet->setCellValue("Q$row", '✔');
                        break;
                    case "[1]":
                        $sheet->setCellValue("R$row", '✔');
                        break;
                    case "[2]":
                        $sheet->setCellValue("S$row", '✔');
                        break;
                }
            }
        }

        // Save the modified spreadsheet to a new file
        $tanggal = date('Y-m-d'); // Atau format tanggal yang Anda inginkan
        $filePath = storage_path("app/public/laporan absensi Cimahi Bandung $tanggal.xlsx");
        $writer = new Xlsx($spreadsheet);
        $writer->save($filePath);

        // Send the file to Telegram
        $this->sendFileToTelegram($filePath);

        $this->info('Poll data exported and sent to Telegram successfully.');

        return 0;
    }

    private function sendFileToTelegram($filePath)
    {
        $apiToken = env('TELEGRAM_API_URL');
        $apiUrl = "https://api.telegram.org/bot$apiToken/";
        $client = new Client(['base_uri' => $apiUrl]);
        $chatId = "-1001309342664";
        $this->sendMessage($client, $chatId, "Berikut Adalah Rekaman Mingguan Polling Absensi Kantor Cimahi");

        try {
            $client->post('sendDocument', [
                'multipart' => [
                    [
                        'name'     => 'chat_id',
                        'contents' => $chatId
                    ],
                    [
                        'name'     => 'document',
                        'contents' => fopen($filePath, 'r')
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            $this->error('Error sending file to Telegram: ' . $e->getMessage());
        }
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
