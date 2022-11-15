<?php

namespace App\Http\Controllers;

use App\Events\MessageSentEvent;
use App\Http\Requests\MessageFormRequest;
use App\Models\Message;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function store(MessageFormRequest $request)
    {
        $message = $request->user()->messages()->create($request->validated());

        MessageSentEvent::dispatch($message);

        return true;
    }

    public function fetch(Request $request)
    {
        $messages = Message::query()
            ->where(function ($query) use ($request) {
                $query->where('sender_id', $request->user()->id)
                    ->where('receiver_id', $request->receiver_id);
            })->orWhere(function ($query) use ($request) {
                $query->where('sender_id', $request->receiver_id)
                    ->where('receiver_id', $request->user()->id);
            })->orderBy('id', 'ASC')
            ->with('sender:id,name,profile_picture_url')
            ->get();


        return response($messages);
    }
}