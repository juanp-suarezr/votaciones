<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class VotoCreado implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $voto;

    public function __construct($voto)
    {
        $this->voto = $voto;
    }

    public function broadcastOn()
    {
        return false;
    }
}
