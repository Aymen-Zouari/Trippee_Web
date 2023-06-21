<?php
namespace App\Service;

use Twilio\Rest\Client;

class TwilioService
{
    private $client;

    public function __construct(string $accountSid, string $authToken)
    {
        $this->client = new Client($accountSid, $authToken);
    }

    public function sendSms(string $from, string $to, string $body)
    {
        try {
            $message = $this->client->messages->create(
                $to,
                [
                    'from' => $from,
                    'body' => $body
                ]
            );
            return $message->sid;
        } catch (\Exception $e) {
            // Handle error here
            return null;
        }
    }
}
