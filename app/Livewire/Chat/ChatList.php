<?php

namespace App\Livewire\Chat;

use Livewire\Attributes\On;
use Livewire\Component;

class ChatList extends Component
{
    public $selectedConversation;
    public $query;

    protected $listeners = ['refresh' => '$refresh'];

    public function render()
    {
        $user = auth()->user();
        return view('livewire.chat.chat-list', [
            'conversations' => $user->conversations()->latest('updated_at')->get()
        ]);
    }

    public function mount()
    {
        logger('ChatList component mounted!');
    }
}
