<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Enums;
use App\Models\Operation;
use App\Models\OperationStatus;

class AvailableOperations implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $data, $success;
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct()
    {

        $this->data = 'Hubo un cambio en las operaciones disponibles';
        $this->success = true;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return ['avaliable-operations'];
    }

    public function broadcastAs()
    {
      return 'get-avaliable-operations';
    }
}
