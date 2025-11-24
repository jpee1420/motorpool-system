<?php

declare(strict_types=1);

namespace App\Services\Notifications;

interface SmsGateway
{
    public function send(string $recipient, string $message): void;
}
