<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WhatsAppConnected implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $sessionId;

    public function __construct($sessionId)
    {
        $this->sessionId = $sessionId;
    }

    public function broadcastOn()
    {
        return new Channel('whatsapp.' . $this->sessionId);
    }

    public function broadcastAs()
    {
        return 'connected';
    }
}