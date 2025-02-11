<?php

namespace App\Livewire\Chat;

use App\Models\Message;
use App\Notifications\MessageSent;
use Livewire\Component;

class ChatBox extends Component
{
    public $selectedConversation;
    public $body = "";
    public $loadedMessages;
    public $paginate_var = 10;

    protected $listeners = [
        'loadMoreMessages'
    ];

    public function getListeners()
    {
        $auth_id = auth()->user()->id;

        return [
            'loadMore',
            "echo-private:users.{$auth_id},.Illuminate\\Notifications\\Events\\BroadcastNotificationCreated" => 'broadcastedNotifications'
        ];
    }

    public function broadcastedNotifications($event)
    {
        // dd($event);

        if ($event['type'] == MessageSent::class) {
            if ($event['conversation_id'] == $this->selectedConversation->id) {
                $this->dispatchBrowserEvent('scroll-bottom');

                $newMessage = Message::find($event['message_id']);

                // push the message
                $this->loadedMessages->push($newMessage);
            }
        }
    }

    public function loadMoreMessages(): void
    {
        // dd('detected');

        // Increment the pagination limit
        $this->paginate_var += 10;

        // Reload messages
        $this->loadMessages();

        // Update the chat height
        $this->dispatchBrowserEvent('update-chat-height');
    }

    public function loadMessages()
    {
        // Get the total count of messages in the conversation
        $count = Message::where('conversation_id', $this->selectedConversation->id)->count();

        // Calculate the number of messages to skip
        $skip = max(0, $count - $this->paginate_var);

        // Load the messages
        $this->loadedMessages = Message::where('conversation_id', $this->selectedConversation->id)
            ->skip($skip)
            ->take($this->paginate_var)
            ->get();
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

        // Scroll to bottom
        $this->dispatchBrowserEvent("scroll-bottom");

        // Push message to loaded chat list
        $this->loadedMessages->push($createdMessage);

        // Update conversation model
        $this->selectedConversation->updated_at = now();
        $this->selectedConversation->save();

        // Update chat list with current new chat
        $this->emitTo('chat.chat-list', 'refresh');

        // broadcast
        $this->selectedConversation->getReceiver()
            ->notify(new MessageSent(
                Auth()->User(),
                $createdMessage,
                $this->selectedConversation,
                $this->selectedConversation->getReceiver()->id,
            ));
    }

    // Initialize component
    public function mount()
    {
        $this->loadMessages();
    }

    public function render()
    {
        return view('livewire.chat.chat-box');
    }
}
