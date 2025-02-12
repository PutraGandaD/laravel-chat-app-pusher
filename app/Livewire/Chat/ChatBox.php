<?php

namespace App\Livewire\Chat;

use App\Models\Message;
use App\Notifications\MessageRead;
use App\Notifications\MessageSent;
use Livewire\Component;

class ChatBox extends Component
{
    public $selectedConversation;
    public $body = "";
    public $loadedMessages = [];
    public $paginate_var = 10;

    protected $listeners = [
        'loadMoreMessages'
    ];

    public function getListeners()
    {
        $auth_id = auth()->user()->id;

        return [
            'loadMoreMessages',
            "echo-private:users.{$auth_id},.Illuminate\\Notifications\\Events\\BroadcastNotificationCreated" => 'broadcastedNotifications'
        ];
    }

    public function broadcastedNotifications($event)
    {
        if ($event['type'] == MessageSent::class) {
            if ($event['conversation_id'] == $this->selectedConversation->id) {
                $this->dispatchBrowserEvent('scroll-bottom');

                $newMessage = Message::find($event['message_id']);

                // Append message without causing Livewire re-render
                $this->loadedMessages[] = $newMessage;

                // Mark as read
                $newMessage->read_at = now();
                $newMessage->save();

                // Notify receiver about read status
                $this->selectedConversation->getReceiver()
                    ->notify(new MessageRead($this->selectedConversation->id));
            }
        }
    }

    public function loadMoreMessages(): void
    {
        $count = Message::where('conversation_id', $this->selectedConversation->id)->count();
        $skip = max(0, $count - ($this->paginate_var + 10));

        $newMessages = Message::where('conversation_id', $this->selectedConversation->id)
            ->skip($skip)
            ->take(10)
            ->get();

        // Append new messages instead of replacing
        $this->loadedMessages = $newMessages->merge($this->loadedMessages);

        $this->paginate_var += 10;

        $this->dispatchBrowserEvent('update-chat-height');
    }

    public function sendMessage()
    {
        $this->validate([
            'body' => 'required|string'
        ]);

        $createdMessage = Message::create([
            'conversation_id' => $this->selectedConversation->id,
            'sender_id' => auth()->id(),
            'receiver_id' => $this->selectedConversation->getReceiver()->id,
            'body' => $this->body
        ]);

        $this->body = "";

        // Append message without causing full re-render
        $this->loadedMessages[] = $createdMessage;

        $this->dispatchBrowserEvent("scroll-bottom");

        // Update conversation timestamp
        $this->selectedConversation->updated_at = now();
        $this->selectedConversation->save();

        // Refresh chat list
        $this->emitTo('chat.chat-list', 'refresh');

        // Notify receiver
        $this->selectedConversation->getReceiver()
            ->notify(new MessageSent(
                auth()->user(),
                $createdMessage,
                $this->selectedConversation,
                $this->selectedConversation->getReceiver()->id
            ));
    }

    public function mount()
    {
        $this->loadMessages();
    }

    public function loadMessages()
    {
        $count = Message::where('conversation_id', $this->selectedConversation->id)->count();
        $skip = max(0, $count - $this->paginate_var);

        $this->loadedMessages = Message::where('conversation_id', $this->selectedConversation->id)
            ->skip($skip)
            ->take($this->paginate_var)
            ->get();
    }

    public function render()
    {
        return view('livewire.chat.chat-box');
    }
}
