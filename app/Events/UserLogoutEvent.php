<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserLogoutEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public User $user)
    {
        $this->user->update([
            'connected' => false,
        ]);
    }


    public function broadcastOn()
    {
        return new Channel('userLogout');
    }

    public function broadcastAs()
    {
        return 'user.logout';
    }

    public function broadcastWith()
    {
        return [
            'data' => $this->user->first(['id', 'name', 'latitude', 'longitude']),
        ];
    }
}