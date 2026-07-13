<?php

namespace App\Livewire;

use App\Services\ChatbotService;
use Livewire\Component;
use Illuminate\Support\Facades\Log;

class ChatbotWidget extends Component
{
    public $messages = [];
    public $userMessage = '';
    public $isTyping = false;
    public $isOpen = false;

    public function mount()
    {
        if (session()->has('chatMessages')) {
            $this->messages = session('chatMessages');
        } else {
            $this->messages = [
                [
                    'id' => 1,
                    'role' => 'assistant',
                    'content' => "👋 Hey! I'm OTTO, your KeyFleet assistant. Ask me about your earnings, bookings, car performance, and more!"
                ]
            ];
        }
    }

    public function chatOpenedEvent()
    {
        $this->isOpen = true;
        $this->dispatch('chatOpened');
    }

    /**
     * Accept the string directly to ensure execution isn't 
     * affected by entangled synchronization latency.
     */
    public function sendMessage($customRawMessage = null)
    {
        $messageToSend = $customRawMessage ?? $this->userMessage;

        if (empty(trim($messageToSend))) {
            return;
        }

        // Verify if message isn't already present in history from Alpine's push
        $alreadyExists = collect($this->messages)->contains(function ($msg) use ($messageToSend) {
            return $msg['role'] === 'user' && $msg['content'] === $messageToSend;
        });

        if (!$alreadyExists) {
            $this->messages[] = [
                'id' => time(),
                'role' => 'user',
                'content' => $messageToSend
            ];
        }

        $this->userMessage = '';
        $this->isTyping = true;

        try {
            $chatbot = app(ChatbotService::class);
            $response = $chatbot->handleQuery($messageToSend);
            
            // Fire the typing animation to the frontend immediately
            $this->dispatch('startTypingAnimation', response: $response);
            
            // Cache safely to session state array
            $this->messages[] = [
                'id' => time() + 1,
                'role' => 'assistant',
                'content' => $response
            ];
        } catch (\Exception $e) {
            Log::error('Chatbot error: ' . $e->getMessage());
            $this->messages[] = [
                'id' => time() + 1,
                'role' => 'assistant',
                'content' => '❌ I\'m having trouble processing your request right now. Please check back shortly.'
            ];
        } finally {
            $this->isTyping = false;
            session()->put('chatMessages', $this->messages);
        }
    }

    public function render()
    {
        return view('livewire.chatbot-widget');
    }
}