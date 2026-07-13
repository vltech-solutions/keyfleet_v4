<div class="fixed bottom-4 right-4 z-50 w-96 bg-white dark:bg-gray-800 rounded-lg shadow-xl border border-gray-200 dark:border-gray-700">
    <!-- Chat Header -->
    <div class="p-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
        <div class="flex items-center space-x-2">
            <div class="w-3 h-3 bg-green-500 rounded-full"></div>
            <h3 class="font-semibold text-gray-900 dark:text-white">KeyFleet Assistant</h3>
        </div>
        <button wire:click="$emit('closeChat')" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>

    <!-- Messages -->
    <div class="h-96 overflow-y-auto p-4 space-y-4 bg-gray-50 dark:bg-gray-900">
        @foreach($messages as $message)
            <div class="flex {{ $message['role'] === 'user' ? 'justify-end' : 'justify-start' }}">
                <div class="max-w-[80%] p-3 rounded-lg {{ $message['role'] === 'user' 
                    ? 'bg-blue-600 text-white' 
                    : 'bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow' }}">
                    {{ $message['content'] }}
                </div>
            </div>
        @endforeach

        @if($isTyping)
            <div class="flex justify-start">
                <div class="bg-white dark:bg-gray-700 p-3 rounded-lg shadow">
                    <div class="flex space-x-1">
                        <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce"></div>
                        <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0.2s"></div>
                        <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0.4s"></div>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <!-- Input -->
    <div class="p-4 border-t border-gray-200 dark:border-gray-700">
        <div class="flex space-x-2">
            <input type="text" 
                wire:model="userMessage" 
                wire:keydown.enter="sendMessage"
                placeholder="Ask me anything..."
                class="flex-1 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-blue-500 focus:border-blue-500">
            <button wire:click="sendMessage" 
                    class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors">
                Send
            </button>
        </div>
        <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
            Ask about bookings, earnings, car performance, and more
        </p>
    </div>
</div>