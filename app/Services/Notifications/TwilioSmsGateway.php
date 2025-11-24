<?php

declare(strict_types=1);

namespace App\Services\Notifications;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class TwilioSmsGateway implements SmsGateway
{
    public function send(string $recipient, string $message): void
    {
        $sid = (string) config('services.twilio.sid', '');
        $token = (string) config('services.twilio.token', '');
        $from = (string) config('services.twilio.from', '');

        if ($sid === '' || $token === '' || $from === '') {
            throw new RuntimeException('Twilio credentials are not configured.');
        }

        $response = Http::withBasicAuth($sid, $token)
            ->asForm()
            ->post("https://api.twilio.com/2010-04-01/Accounts/{$sid}/Messages.json", [
                'From' => $from,
                'To' => $recipient,
                'Body' => $message,
            ]);

        if ($response->failed()) {
            throw new RuntimeException('Twilio SMS failed with status '.$response->status());
        }
    }
}
