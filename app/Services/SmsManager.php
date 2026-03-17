<?php

namespace App\Services;

use App\Services\Sms\SmsDriverInterface;
use App\Services\Sms\NullDriver;
use App\Services\Sms\SemaphoreDriver;
use App\Services\Sms\TextbeeDriver;
use Illuminate\Support\Facades\Log;

class SmsManager
{
    protected SmsDriverInterface $driver;

    public function __construct()
    {
        $this->driver = $this->resolveDriver();
    }

    protected function resolveDriver(): SmsDriverInterface
    {
        $driver = config('sms.driver', 'null');

        return match ($driver) {
            'semaphore' => new SemaphoreDriver(),
            'textbee' => new TextbeeDriver(),
            default => new NullDriver(),
        };
    }

    public function send(string $to, string $message): array
    {
        return $this->driver->send($to, $message);
    }

    public function sendBulk(array $numbers, string $message): array
    {
        return $this->driver->sendBulk($numbers, $message);
    }

    public function driver(): SmsDriverInterface
    {
        return $this->driver;
    }

    public function isEnabled(): bool
    {
        return config('sms.driver') !== 'null';
    }
}
