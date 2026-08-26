<?php

namespace App\Services;

use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;

class FirebaseService
{
    protected $messaging;

    public function __construct()
    {
        $factory = (new Factory)
    ->withServiceAccount(config('firebase.credentials'));


        $this->messaging = $factory->createMessaging();
    }

    public function sendToToken($token, $title, $body)
    {
        $message = CloudMessage::withTarget('token', $token)
            ->withNotification(Notification::create($title, $body));

        return $this->messaging->send($message);
    }

    public function sendToAll(array $tokens, $title, $body)
    {
        $messages = array_map(function ($token) use ($title, $body) {
            return CloudMessage::withTarget('token', $token)
                ->withNotification(Notification::create($title, $body));
        }, $tokens);

        return $this->messaging->sendAll($messages);
    }
}