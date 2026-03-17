<?php

namespace App\Services\Sms;

use App\Services\Sms\SmsDriverInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TextbeeDriver implements SmsDriverInterface
{
    protected string $apiKey;
    protected string $deviceId;
    protected string $baseUrl = 'https://api.textbee.dev/api/v1/gateway/devices';

    public function __construct()
    {
        $this->apiKey = config('sms.drivers.textbee.key');
        $this->deviceId = config('sms.drivers.textbee.device_id');
    }

    public function send(string $to, string $message): array
    {
        $to = $this->formatNumber($to);

        if (empty($this->apiKey) || empty($this->deviceId)) {
            Log::warning('TextBee API key or Device ID not configured');
            return ['success' => false, 'error' => 'API key or Device ID not configured'];
        }

        try {
            $response = Http::timeout(30)->withHeaders([
                'x-api-key' => $this->apiKey,
            ])->post("{$this->baseUrl}/{$this->deviceId}/send-sms", [
                'recipients' => [$to],
                'message' => $message,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                Log::info("TextBee SMS sent successfully to {$to}", $data);
                return ['success' => true, 'data' => $data];
            }

            Log::error("TextBee SMS failed to send to {$to}", ['response' => $response->json()]);
            return ['success' => false, 'error' => $response->json()];

        } catch (\Exception $e) {
            Log::error("TextBee SMS exception: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function sendBulk(array $numbers, string $message): array
    {
        $numbers = array_map([$this, 'formatNumber'], $numbers);

        if (empty($this->apiKey) || empty($this->deviceId)) {
            Log::warning('TextBee API key or Device ID not configured');
            return ['success' => false, 'error' => 'API key or Device ID not configured'];
        }

        try {
            $response = Http::timeout(30)->withHeaders([
                'x-api-key' => $this->apiKey,
            ])->post("{$this->baseUrl}/{$this->deviceId}/send-bulk-sms", [
                'recipients' => $numbers,
                'message' => $message,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                Log::info("TextBee bulk SMS sent successfully", $data);
                return ['success' => true, 'data' => $data];
            }

            Log::error("TextBee bulk SMS failed", ['response' => $response->json()]);
            return ['success' => false, 'error' => $response->json()];

        } catch (\Exception $e) {
            Log::error("TextBee bulk SMS exception: " . $e->getMessage());
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
