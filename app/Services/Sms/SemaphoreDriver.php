<?php

namespace App\Services\Sms;

use App\Services\Sms\SmsDriverInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SemaphoreDriver implements SmsDriverInterface
{
    protected string $apiKey;
    protected string $senderName;
    protected string $baseUrl = 'https://api.semaphore.co/api/v4';

    public function __construct()
    {
        $this->apiKey = config('sms.drivers.semaphore.key');
        $this->senderName = config('sms.drivers.semaphore.sender', 'SEMAPHORE');
    }

    public function send(string $to, string $message): array
    {
        $to = $this->formatNumber($to);

        if (empty($this->apiKey)) {
            Log::warning('Semaphore API key not configured');
            return ['success' => false, 'error' => 'API key not configured'];
        }

        try {
            $response = Http::timeout(30)->post("{$this->baseUrl}/messages", [
                'apikey' => $this->apiKey,
                'number' => $to,
                'message' => $message,
                'sendername' => $this->senderName,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                Log::info("SMS sent successfully to {$to}", $data);
                return ['success' => true, 'data' => $data];
            }

            Log::error("SMS failed to send to {$to}", ['response' => $response->json()]);
            return ['success' => false, 'error' => $response->json()];

        } catch (\Exception $e) {
            Log::error("SMS exception: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function sendBulk(array $numbers, string $message): array
    {
        $numbers = array_map([$this, 'formatNumber'], $numbers);
        $numbers = implode(',', $numbers);

        if (empty($this->apiKey)) {
            Log::warning('Semaphore API key not configured');
            return ['success' => false, 'error' => 'API key not configured'];
        }

        try {
            $response = Http::timeout(30)->post("{$this->baseUrl}/messages", [
                'apikey' => $this->apiKey,
                'number' => $numbers,
                'message' => $message,
                'sendername' => $this->senderName,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                Log::info("Bulk SMS sent successfully", $data);
                return ['success' => true, 'data' => $data];
            }

            Log::error("Bulk SMS failed", ['response' => $response->json()]);
            return ['success' => false, 'error' => $response->json()];

        } catch (\Exception $e) {
            Log::error("Bulk SMS exception: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    protected function formatNumber(string $number): string
    {
        $number = preg_replace('/[^0-9]/', '', $number);

        if (strlen($number) === 10 && substr($number, 0, 1) === '9') {
            return '63' . $number;
        }

        if (strlen($number) === 11 && substr($number, 0, 2) === '09') {
            return '63' . substr($number, 1);
        }

        if (strlen($number) === 12 && substr($number, 0, 2) === '63') {
            return $number;
        }

        if (strlen($number) === 9) {
            return '63' . $number;
        }

        return $number;
    }
}
