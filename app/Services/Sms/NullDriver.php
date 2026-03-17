<?php

namespace App\Services\Sms;

use App\Services\Sms\SmsDriverInterface;
use Illuminate\Support\Facades\Log;

class NullDriver implements SmsDriverInterface
{
    public function send(string $to, string $message): array
    {
        Log::info("[NULL SMS] To: {$to}, Message: {$message}");
        return ['success' => true, 'data' => ['mock' => true, 'to' => $to, 'message' => $message]];
    }

    public function sendBulk(array $numbers, string $message): array
    {
        Log::info("[NULL SMS] Bulk to: " . implode(', ', $numbers) . ", Message: {$message}");
        return ['success' => true, 'data' => ['mock' => true, 'numbers' => $numbers, 'message' => $message]];
    }
}
