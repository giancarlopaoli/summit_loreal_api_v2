<?php

namespace App\Events;

use App\Models\ExchangeRate;
use App\Models\Range;
use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewExchangeRate implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public ExchangeRate $exchange_rate;
    private User $user;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(User $user)
    {
        $this->user = $user;
        $exchange_rate = ExchangeRate::latest()->first();
        $min_amount = Range::minimun_amount();
        $this->exchange_rate = $exchange_rate->for_user($this->user, $min_amount);
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        //return new PrivateChannel('exchange-rate.'.$this->user->id);
        return ['exchange-rate.'.$this->user->id];
    }

    public function broadcastAs()
    {
      return 'get-exchangerate';
    }
}
