<?php

namespace App\Livewire\Chat;

use App\Models\Message;
use Livewire\Component;

class ChatBox extends Component
{
    public $selectedConversation;
    public $body = "";
    public $loadedMessages;
    public $iteration = 0;

    public function loadMessages()
    {
        $this->loadedMessages = Message::where('conversation_id', $this->selectedConversation->id)->get();
    }

    public function render()
    {
        return view('livewire.chat.chat-box');
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
        $this->iteration++;

        #scroll to bottom
        $this->dispatch("scroll-bottom");

        #push message to loaded chat list
        $this->loadedMessages->push($createdMessage);

        #update conversation model
        $this->selectedConversation->updated_at = now();
        $this->selectedConversation->save();

        #update chatlist with current new chat
        $this->dispatch('chat.chat-list', 'refresh');
    }

    // init
    public function mount()
    {
        logger('ChatBox component loaded');

        $this->loadMessages();
    }
}
