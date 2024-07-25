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
        $dayOfWeek = Carbon::now()->dayOfWeek;

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
        $remainingBudget = $this->calculateRemainingBudget($totalAmount);

        BudgetDaily::create([
            'tanggal' => $today,
            'total_voters' => $totalVoters,
            'total_amount' => $totalAmount,
            'remaining_budget' => $remainingBudget,
        ]);

        $message = "Total voters for lunch: $totalVoters\nTotal amount: $totalAmount\nRemaining budget: $remainingBudget";
        $this->sendMessage($client, $chatId, $message);
        $this->sendMessage($client, $chatId, "generate spread budget");

        if ($dayOfWeek == Carbon::FRIDAY) {
            $this->calculateWeeklyBudget($client, $chatId);
        }

        $this->generateAndSendExcel($client, $chatId);

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

    private function calculateRemainingBudget($totalAmount)
    {
        $lastBudget = BudgetDaily::orderBy('tanggal', 'desc')->first();
        $remainingBudget = $lastBudget ? $lastBudget->remaining_budget - $totalAmount : 0;

        return $remainingBudget;
    }

    private function calculateWeeklyBudget($client, $chatId)
    {
        $startDate = Carbon::now()->startOfWeek()->toDateString();
        $endDate = Carbon::now()->endOfWeek()->toDateString();

        $dailyBudgets = BudgetDaily::whereBetween('tanggal', [$startDate, $endDate])->get();

        $totalBudget = 0;
        $actualCost = 0;

        foreach ($dailyBudgets as $dailyBudget) {
            $totalBudget += $dailyBudget->total_amount;
            $actualCost += $dailyBudget->total_amount;
        }

        $remainingBudget = $totalBudget - $actualCost;

        BudgetWeekly::create([
            'start_date' => $startDate,
            'end_date' => $endDate,
            'total_budget' => $totalBudget,
            'remaining_budget' => $remainingBudget,
        ]);

        $message = "Weekly total budget: $totalBudget\nWeekly actual cost: $actualCost\nWeekly remaining budget: $remainingBudget";
        $this->sendMessage($client, $chatId, $message);
    }

    private function generateAndSendExcel($client, $chatId)
    {
        $filePath = storage_path('app/budget_daily.xlsx');
        $this->generateExcel($filePath);

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
            $this->error('Error sending Excel file: ' . $e->getMessage());
        }
    }

    private function generateExcel($filePath)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Set the headings
        $sheet->setCellValue('A1', 'ID');
        $sheet->setCellValue('B1', 'Tanggal');
        $sheet->setCellValue('C1', 'Total Voters');
        $sheet->setCellValue('D1', 'Total Amount');
        $sheet->setCellValue('E1', 'Remaining Budget');
        $sheet->setCellValue('F1', 'Created At');
        $sheet->setCellValue('G1', 'Updated At');
        
        // Fetch the data
        $budgets = BudgetDaily::all();
        $row = 2;
        
        foreach ($budgets as $budget) {
            $sheet->setCellValue('A' . $row, $budget->id);
            $sheet->setCellValue('B' . $row, $budget->tanggal);
            $sheet->setCellValue('C' . $row, $budget->total_voters);
            $sheet->setCellValue('D' . $row, $budget->total_amount);
            $sheet->setCellValue('E' . $row, $budget->remaining_budget);
            $sheet->setCellValue('F' . $row, $budget->created_at);
            $sheet->setCellValue('G' . $row, $budget->updated_at);
            $row++;
        }
        
        $writer = new Xlsx($spreadsheet);
        $writer->save($filePath);
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
