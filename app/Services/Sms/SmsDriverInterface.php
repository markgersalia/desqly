<?php

namespace App\Services\Sms;

interface SmsDriverInterface
{
    public function send(string $to, string $message): array;

    public function sendBulk(array $numbers, string $message): array;
}
