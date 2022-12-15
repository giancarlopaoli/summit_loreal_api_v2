<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\ExchangeRate;

class DatatecExchangeRate implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $compra, $venta;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct()
    {
        $exchange_rate = ExchangeRate::latest()->first();
        $this->compra = $exchange_rate->compra;
        $this->venta = $exchange_rate->venta;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return ['base-exchange-rate'];
    }

    public function broadcastAs()
    {
      return 'get-base-exchangerate';
    }
}
