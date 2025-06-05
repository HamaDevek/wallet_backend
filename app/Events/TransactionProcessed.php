<?php

namespace App\Events;

use App\Models\Transactions;
use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TransactionProcessed
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    public $transaction;
    public $user;
    public $type; // 'sent', 'received', 'recharge'
    /**
     * Create a new event instance.
     */
    public function __construct(Transactions $transaction, User $user, string $type)
    {
        $this->transaction = $transaction;
        $this->user = $user;
        $this->type = $type;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('channel-name'),
        ];
    }
}
