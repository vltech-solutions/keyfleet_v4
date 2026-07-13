<?php

namespace App\Filament\Pages;

use App\Services\ChatbotService;
use Filament\Pages\Page;

class Chatbot extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';
    protected static string $view = 'filament.pages.chatbot';
    protected static ?string $navigationGroup = 'Support';
    protected static ?int $navigationSort = 10;
    
    public $messages = [];
    public $userMessage = '';
    public $isTyping = false;

    public function mount()
    {
        $this->messages = [
            [
                'role' => 'assistant',
                'content' => '👋 Hello! I\'m your KeyFleet assistant. How can I help you today?'
            ]
        ];
    }

    public function sendMessage()
    {
        if (empty(trim($this->userMessage))) {
            return;
        }

        // Add user message
        $this->messages[] = [
            'role' => 'user',
            'content' => $this->userMessage
        ];

        $userMessage = $this->userMessage;
        $this->userMessage = '';
        $this->isTyping = true;

        // Process with AI
        try {
            $chatbot = app(ChatbotService::class);
            $response = $chatbot->handleQuery($userMessage);
            
            $this->messages[] = [
                'role' => 'assistant',
                'content' => $response
            ];
        } catch (\Exception $e) {
            \Log::error('Chatbot error: ' . $e->getMessage());
            $this->messages[] = [
                'role' => 'assistant',
                'content' => "I'm having trouble processing your request right now. Please try again or contact support if the issue persists."
            ];
        }

        $this->isTyping = false;
    }
}