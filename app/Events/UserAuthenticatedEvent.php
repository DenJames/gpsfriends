<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserAuthenticatedEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public User $user)
    {
        $this->user->update([
            'connected' => true,
        ]);
    }

    public function broadcastOn()
    {
        return new Channel('userAuthenticated');
    }

    public function broadcastAs()
    {
        return 'user.authenticated';
    }

    public function broadcastWith()
    {
        return [
            'data' => $this->user->first(['name', 'latitude', 'longitude']),
        ];
    }
}