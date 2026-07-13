<div>
    <!-- Overlay -->
    <div x-show="isOpen" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-black/50 dark:bg-black/70 backdrop-blur-sm z-40"
         @click="isOpen = false"
         style="display: none;"
    ></div>

    <!-- Sliding Panel -->
    <div x-data="{
        isOpen: @entangle('isOpen'),
        isTyping: @entangle('isTyping'),
        messages: @entangle('messages'),
        userMessage: @entangle('userMessage'),
        typingText: '',
        currentMessage: '',
        isTypingComplete: false,
        thinkingText: '',
        showSuggestions: false,
        
        init() {
            this.$watch('messages', () => {
                this.$nextTick(() => this.scrollToBottom());
            });
            
            Livewire.on('startTypingAnimation', (data) => {
                this.animateTyping(data.response);
            });
            
            Livewire.on('chatOpened', () => {
                this.$nextTick(() => this.scrollToBottom());
            });
            
            // Listen for open chatbot from navbar
            window.addEventListener('open-chatbot', () => {
                this.isOpen = true;
                @this.chatOpenedEvent();
                this.$nextTick(() => this.scrollToBottom());
            });
        },
        
        scrollToBottom() {
            const container = this.$refs.chatMessages;
            if (container) {
                container.scrollTo({
                    top: container.scrollHeight,
                    behavior: 'smooth'
                });
            }
        },
        
        formatMessage(content) {
            if (!content) return '';
            
            // If content is HTML, process it for responsiveness
            if (content.includes('<') && content.includes('>')) {
                let html = content;
                
                // Wrap tables in responsive wrapper
                html = html.replace(/<table/g, '<div class=\'table-wrapper\'><table');
                html = html.replace(/<\/table>/g, '</table></div>');
                
                // Add break-long class to long text cells (50+ chars)
                html = html.replace(/<td>([^<]{50,})<\/td>/g, '<td class=\'break-long\'>$1</td>');
                
                return html;
            }
            
            // Plain text to HTML
            let html = content;
            html = html.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
            html = html.replace(/\*(.*?)\*/g, '<em>$1</em>');
            html = html.replace(/\n/g, '<br>');
            return html;
        },
        
        animateTyping(response) {
            this.isTypingComplete = false;
            this.currentMessage = response;
            this.typingText = '';
            this.isTyping = true;
            
            const formattedResponse = this.formatMessage(response);
            
            if (response.includes('<') && response.includes('>')) {
                this.typingText = formattedResponse;
                this.isTyping = false;
                this.isTypingComplete = true;
                this.$nextTick(() => this.scrollToBottom());
                return;
            }
            
            let index = 0;
            const interval = setInterval(() => {
                if (index < formattedResponse.length) {
                    this.typingText += formattedResponse.charAt(index);
                    index++;
                    this.scrollToBottom();
                } else {
                    clearInterval(interval);
                    this.isTyping = false;
                    this.isTypingComplete = true;
                    this.$nextTick(() => this.scrollToBottom());
                }
            }, 10);
        },
        
        toggleChat() { 
            this.isOpen = !this.isOpen;
            if (this.isOpen) {
                @this.chatOpenedEvent();
                this.$nextTick(() => this.scrollToBottom());
            }
        },
        
        submitMessage() { 
            const rawMessage = this.userMessage.trim();
            if (!rawMessage || this.isTyping) return;
            
            this.messages.push({
                id: Date.now(),
                role: 'user',
                content: rawMessage
            });
            
            this.thinkingText = 'Thinking...';
            this.isTyping = true;
            this.typingText = '';
            
            this.$nextTick(() => this.scrollToBottom());
            
            @this.sendMessage(rawMessage);
            this.userMessage = '';
        },
        
        sendQuickMessage(message) {
            this.userMessage = message;
            this.submitMessage();
        }
    }" 
    x-show="isOpen"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="translate-x-full opacity-0"
    x-transition:enter-end="translate-x-0 opacity-100"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="translate-x-0 opacity-100"
    x-transition:leave-end="translate-x-full opacity-0"
    class="fixed top-0 right-0 h-full w-[420px] max-w-[95vw] bg-white dark:bg-gray-900 shadow-2xl z-50 flex flex-col"
    style="display: none;"
    @open-chatbot.window="isOpen = true; @this.chatOpenedEvent(); $el.style.display = 'flex'; $nextTick(() => scrollToBottom())"
    @close-chatbot.window="isOpen = false; setTimeout(() => { $el.style.display = 'none' }, 300)"
    x-init="$el.style.display = 'none'"
>
    <!-- Chat Header with Otto -->
    <div class="flex-shrink-0 p-4 bg-gradient-to-r from-blue-600 to-indigo-700 dark:from-blue-700 dark:to-indigo-900 text-white flex justify-between items-center shadow-sm">
        <div class="flex items-center space-x-3">
            <div class="relative flex items-center justify-center">
                <span class="absolute inline-flex h-3 w-3 rounded-full bg-green-400 opacity-75 animate-ping"></span>
                <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-green-500"></span>
            </div>
            <div>
                <span class="font-semibold text-sm tracking-wide block">OTTO</span>
                <span class="text-[11px] text-blue-200 block font-light">Your KeyFleet Assistant</span>
            </div>
        </div>
        <button @click="isOpen = false" class="text-white/80 hover:text-white transition-colors p-1.5 rounded-xl hover:bg-white/10 focus:outline-none">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>

    <!-- Messages -->
    <div x-ref="chatMessages" class="flex-1 overflow-y-auto p-4 space-y-4 bg-gray-50/50 dark:bg-gray-950/40">
        <template x-for="(message, index) in messages" :key="'msg-' + index">
            <div :class="message.role === 'user' ? 'flex justify-end' : 'flex justify-start'">
                <div :class="message.role === 'user' 
                    ? 'bg-blue-600 text-white rounded-2xl rounded-br-none px-4 py-2.5 max-w-[85%] shadow-sm text-sm' 
                    : 'bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-100 rounded-2xl rounded-tl-none px-4 py-2.5 max-w-[85%] shadow-sm border border-gray-100 dark:border-gray-800 prose prose-sm dark:prose-invert max-w-none'">
                    <div x-html="formatMessage(message.content)"></div>
                </div>
            </div>
        </template>
        
        <!-- Thinking Indicator -->
        <div x-show="isTyping && !typingText" class="flex justify-start">
            <div class="bg-white dark:bg-gray-800 rounded-2xl rounded-tl-none px-4 py-3 shadow-sm border border-gray-100 dark:border-gray-800 max-w-[85%]">
                <div class="flex items-center space-x-2.5">
                    <svg class="animate-spin h-4 w-4 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span class="text-xs font-medium text-gray-500 dark:text-gray-400" x-text="thinkingText"></span>
                </div>
            </div>
        </div>
        
        <!-- Typing Animation -->
        <div x-show="isTyping && typingText" class="flex justify-start">
            <div class="bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-100 rounded-2xl rounded-tl-none px-4 py-2.5 max-w-[85%] shadow-sm border border-gray-100 dark:border-gray-800 prose prose-sm dark:prose-invert max-w-none">
                <div class="inline" x-html="typingText"></div>
                <span class="inline-block w-1.5 h-4 ml-0.5 bg-blue-600 dark:bg-blue-400 animate-blink align-middle"></span>
            </div>
        </div>
    </div>

    <!-- Quick Suggestions - Dropdown Style (UPDATED WITH CAR AVAILABILITY QUESTIONS) -->
    <div class="flex-shrink-0 px-3 py-2 bg-white dark:bg-gray-900 border-t border-gray-100 dark:border-gray-800/60">
        <div class="flex items-center gap-1.5">
            <button @click="showSuggestions = !showSuggestions" 
                    class="flex items-center gap-1 text-xs px-2.5 py-1 bg-blue-50 dark:bg-blue-950/40 text-blue-600 dark:text-blue-400 rounded-full hover:bg-blue-100 dark:hover:bg-blue-900/40 transition-all duration-150 font-medium">
                <span>💡</span>
                <span x-text="showSuggestions ? 'Hide' : 'Quick Actions'"></span>
                <svg class="w-3 h-3 transition-transform duration-200" :class="{'rotate-180': showSuggestions}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
            
            <span x-show="!showSuggestions" class="text-[10px] text-gray-400 dark:text-gray-500">
                click to show quick actions
            </span>
        </div>
        
        <div x-show="showSuggestions" 
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 -translate-y-2"
            x-transition:enter-end="opacity-100 translate-y-0"
            class="flex flex-wrap gap-1.5 mt-2 pt-2 border-t border-gray-100 dark:border-gray-800">
            
            <!-- === CAR AVAILABILITY (NEW SECTION - TOP PRIORITY) === -->
            <button @click="sendQuickMessage('What cars are available today?')" 
                    class="text-xs px-2.5 py-1 bg-green-50 dark:bg-green-950/40 text-green-700 dark:text-green-300 rounded-full hover:bg-green-100 dark:hover:bg-green-900/50 transition-all duration-150 border border-green-200/40 dark:border-green-800/50 font-medium">
                🚗 Available Today
            </button>
            <button @click="sendQuickMessage('What cars are available tomorrow?')" 
                    class="text-xs px-2.5 py-1 bg-green-50 dark:bg-green-950/40 text-green-700 dark:text-green-300 rounded-full hover:bg-green-100 dark:hover:bg-green-900/50 transition-all duration-150 border border-green-200/40 dark:border-green-800/50">
                📅 Available Tomorrow
            </button>
            <button @click="sendQuickMessage('What cars are available this weekend?')" 
                    class="text-xs px-2.5 py-1 bg-green-50 dark:bg-green-950/40 text-green-700 dark:text-green-300 rounded-full hover:bg-green-100 dark:hover:bg-green-900/50 transition-all duration-150 border border-green-200/40 dark:border-green-800/50">
                🏖️ This Weekend
            </button>
            <button @click="sendQuickMessage('What cars are available from 2026-07-10 to 2026-07-15?')" 
                    class="text-xs px-2.5 py-1 bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 rounded-full hover:bg-emerald-100 dark:hover:bg-emerald-900/50 transition-all duration-150 border border-emerald-200/40 dark:border-emerald-800/50 font-medium">
                📆 Specific Dates
            </button>
            <button @click="sendQuickMessage('Show me all available cars')" 
                    class="text-xs px-2.5 py-1 bg-green-50 dark:bg-green-950/40 text-green-700 dark:text-green-300 rounded-full hover:bg-green-100 dark:hover:bg-green-900/50 transition-all duration-150 border border-green-200/40 dark:border-green-800/50">
                📋 All Available
            </button>
            
            <!-- === EARNINGS & FINANCIAL === -->
            <button @click="sendQuickMessage('How much did I earn this month?')" 
                    class="text-xs px-2.5 py-1 bg-blue-50 dark:bg-blue-950/40 text-blue-700 dark:text-blue-300 rounded-full hover:bg-blue-100 dark:hover:bg-blue-900/50 transition-all duration-150 border border-blue-200/40 dark:border-blue-800/50">
                💰 Earnings
            </button>
            <button @click="sendQuickMessage('Show my earnings breakdown')" 
                    class="text-xs px-2.5 py-1 bg-gray-50 dark:bg-gray-800 text-gray-600 dark:text-gray-300 rounded-full hover:bg-blue-50 dark:hover:bg-blue-950/40 hover:text-blue-600 dark:hover:text-blue-400 transition-all duration-150 border border-gray-200/40 dark:border-gray-700/50">
                📊 Breakdown
            </button>
            <button @click="sendQuickMessage('What is my net revenue?')" 
                    class="text-xs px-2.5 py-1 bg-purple-50 dark:bg-purple-950/40 text-purple-700 dark:text-purple-300 rounded-full hover:bg-purple-100 dark:hover:bg-purple-900/50 transition-all duration-150 border border-purple-200/40 dark:border-purple-800/50">
                📈 Net Revenue
            </button>
            <button @click="sendQuickMessage('What is my profit margin?')" 
                    class="text-xs px-2.5 py-1 bg-green-50 dark:bg-green-950/40 text-green-700 dark:text-green-300 rounded-full hover:bg-green-100 dark:hover:bg-green-900/50 transition-all duration-150 border border-green-200/40 dark:border-green-800/50">
                💹 Profit Margin
            </button>
            
            <!-- === BOOKINGS === -->
            <button @click="sendQuickMessage('Show me today\'s bookings')" 
                    class="text-xs px-2.5 py-1 bg-gray-50 dark:bg-gray-800 text-gray-600 dark:text-gray-300 rounded-full hover:bg-blue-50 dark:hover:bg-blue-950/40 hover:text-blue-600 dark:hover:text-blue-400 transition-all duration-150 border border-gray-200/40 dark:border-gray-700/50">
                📅 Today
            </button>
            <button @click="sendQuickMessage('Show me my bookings')" 
                    class="text-xs px-2.5 py-1 bg-gray-50 dark:bg-gray-800 text-gray-600 dark:text-gray-300 rounded-full hover:bg-blue-50 dark:hover:bg-blue-950/40 hover:text-blue-600 dark:hover:text-blue-400 transition-all duration-150 border border-gray-200/40 dark:border-gray-700/50">
                📋 All Bookings
            </button>
            <button @click="sendQuickMessage('Show upcoming bookings')" 
                    class="text-xs px-2.5 py-1 bg-gray-50 dark:bg-gray-800 text-gray-600 dark:text-gray-300 rounded-full hover:bg-blue-50 dark:hover:bg-blue-950/40 hover:text-blue-600 dark:hover:text-blue-400 transition-all duration-150 border border-gray-200/40 dark:border-gray-700/50">
                📅 Upcoming
            </button>
            <button @click="sendQuickMessage('Show me cancelled bookings this month')" 
                    class="text-xs px-2.5 py-1 bg-red-50 dark:bg-red-950/40 text-red-700 dark:text-red-300 rounded-full hover:bg-red-100 dark:hover:bg-red-900/50 transition-all duration-150 border border-red-200/40 dark:border-red-800/50">
                ❌ Cancelled
            </button>
            
            <!-- === CARS & FLEET === -->
            <button @click="sendQuickMessage('How many cars are available?')" 
                    class="text-xs px-2.5 py-1 bg-gray-50 dark:bg-gray-800 text-gray-600 dark:text-gray-300 rounded-full hover:bg-blue-50 dark:hover:bg-blue-950/40 hover:text-blue-600 dark:hover:text-blue-400 transition-all duration-150 border border-gray-200/40 dark:border-gray-700/50">
                🚙 Available Count
            </button>
            <button @click="sendQuickMessage('Which car is performing best?')" 
                    class="text-xs px-2.5 py-1 bg-gray-50 dark:bg-gray-800 text-gray-600 dark:text-gray-300 rounded-full hover:bg-blue-50 dark:hover:bg-blue-950/40 hover:text-blue-600 dark:hover:text-blue-400 transition-all duration-150 border border-gray-200/40 dark:border-gray-700/50">
                🚗 Top Car
            </button>
            <button @click="sendQuickMessage('Show me car performance ranking')" 
                    class="text-xs px-2.5 py-1 bg-yellow-50 dark:bg-yellow-950/40 text-yellow-700 dark:text-yellow-300 rounded-full hover:bg-yellow-100 dark:hover:bg-yellow-900/50 transition-all duration-150 border border-yellow-200/40 dark:border-yellow-800/50">
                🏆 Car Ranking
            </button>
            <button @click="sendQuickMessage('How is Reddy doing?')" 
                    class="text-xs px-2.5 py-1 bg-indigo-50 dark:bg-indigo-950/40 text-indigo-700 dark:text-indigo-300 rounded-full hover:bg-indigo-100 dark:hover:bg-indigo-900/50 transition-all duration-150 border border-indigo-200/40 dark:border-indigo-800/50">
                🚘 Specific Car
            </button>
            <button @click="sendQuickMessage('Show me fleet status')" 
                    class="text-xs px-2.5 py-1 bg-gray-50 dark:bg-gray-800 text-gray-600 dark:text-gray-300 rounded-full hover:bg-blue-50 dark:hover:bg-blue-950/40 hover:text-blue-600 dark:hover:text-blue-400 transition-all duration-150 border border-gray-200/40 dark:border-gray-700/50">
                🏢 Fleet Status
            </button>
            
            <!-- === CUSTOMERS === -->
            <button @click="sendQuickMessage('Who are my top customers?')" 
                    class="text-xs px-2.5 py-1 bg-gray-50 dark:bg-gray-800 text-gray-600 dark:text-gray-300 rounded-full hover:bg-blue-50 dark:hover:bg-blue-950/40 hover:text-blue-600 dark:hover:text-blue-400 transition-all duration-150 border border-gray-200/40 dark:border-gray-700/50">
                👤 Top Customers
            </button>
            <button @click="sendQuickMessage('Show me VIP customers')" 
                    class="text-xs px-2.5 py-1 bg-pink-50 dark:bg-pink-950/40 text-pink-700 dark:text-pink-300 rounded-full hover:bg-pink-100 dark:hover:bg-pink-900/50 transition-all duration-150 border border-pink-200/40 dark:border-pink-800/50">
                👑 VIP Customers
            </button>
            <button @click="sendQuickMessage('Which customers have outstanding balances?')" 
                    class="text-xs px-2.5 py-1 bg-orange-50 dark:bg-orange-950/40 text-orange-700 dark:text-orange-300 rounded-full hover:bg-orange-100 dark:hover:bg-orange-900/50 transition-all duration-150 border border-orange-200/40 dark:border-orange-800/50">
                💳 Balances
            </button>
            <button @click="sendQuickMessage('What is my customer retention rate?')" 
                    class="text-xs px-2.5 py-1 bg-teal-50 dark:bg-teal-950/40 text-teal-700 dark:text-teal-300 rounded-full hover:bg-teal-100 dark:hover:bg-teal-900/50 transition-all duration-150 border border-teal-200/40 dark:border-teal-800/50">
                🔄 Retention
            </button>
            
            <!-- === PAYMENTS === -->
            <button @click="sendQuickMessage('Show me my payment summary')" 
                    class="text-xs px-2.5 py-1 bg-gray-50 dark:bg-gray-800 text-gray-600 dark:text-gray-300 rounded-full hover:bg-blue-50 dark:hover:bg-blue-950/40 hover:text-blue-600 dark:hover:text-blue-400 transition-all duration-150 border border-gray-200/40 dark:border-gray-700/50">
                💳 Payments
            </button>
            <button @click="sendQuickMessage('Where do bookings come from?')" 
                    class="text-xs px-2.5 py-1 bg-cyan-50 dark:bg-cyan-950/40 text-cyan-700 dark:text-cyan-300 rounded-full hover:bg-cyan-100 dark:hover:bg-cyan-900/50 transition-all duration-150 border border-cyan-200/40 dark:border-cyan-800/50">
                📍 Sources
            </button>
            
            <!-- === PARTNERS === -->
            <button @click="sendQuickMessage('Who are my partners?')" 
                    class="text-xs px-2.5 py-1 bg-gray-50 dark:bg-gray-800 text-gray-600 dark:text-gray-300 rounded-full hover:bg-blue-50 dark:hover:bg-blue-950/40 hover:text-blue-600 dark:hover:text-blue-400 transition-all duration-150 border border-gray-200/40 dark:border-gray-700/50">
                🤝 Partners
            </button>
            <button @click="sendQuickMessage('Show me partner performance')" 
                    class="text-xs px-2.5 py-1 bg-gray-50 dark:bg-gray-800 text-gray-600 dark:text-gray-300 rounded-full hover:bg-blue-50 dark:hover:bg-blue-950/40 hover:text-blue-600 dark:hover:text-blue-400 transition-all duration-150 border border-gray-200/40 dark:border-gray-700/50">
                📊 Partner Performance
            </button>
            
            <!-- === EXPENSES === -->
            <button @click="sendQuickMessage('Show me my expenses this year')" 
                    class="text-xs px-2.5 py-1 bg-gray-50 dark:bg-gray-800 text-gray-600 dark:text-gray-300 rounded-full hover:bg-blue-50 dark:hover:bg-blue-950/40 hover:text-blue-600 dark:hover:text-blue-400 transition-all duration-150 border border-gray-200/40 dark:border-gray-700/50">
                💸 Expenses
            </button>
            
            <!-- === BUSINESS SUMMARY === -->
            <button @click="sendQuickMessage('Show me business summary')" 
                    class="text-xs px-2.5 py-1 bg-blue-600 dark:bg-blue-700 text-white rounded-full hover:bg-blue-700 dark:hover:bg-blue-800 transition-all duration-150 border border-blue-700/40 dark:border-blue-800/50 shadow-sm font-medium">
                📊 Business Summary
            </button>
            <button @click="sendQuickMessage('Compare this month to last month')" 
                    class="text-xs px-2.5 py-1 bg-blue-600 dark:bg-blue-700 text-white rounded-full hover:bg-blue-700 dark:hover:bg-blue-800 transition-all duration-150 border border-blue-700/40 dark:border-blue-800/50 shadow-sm font-medium">
                📈 Compare Periods
            </button>
            <button @click="sendQuickMessage('Show me daily report')" 
                    class="text-xs px-2.5 py-1 bg-blue-600 dark:bg-blue-700 text-white rounded-full hover:bg-blue-700 dark:hover:bg-blue-800 transition-all duration-150 border border-blue-700/40 dark:border-blue-800/50 shadow-sm font-medium">
                📋 Daily Report
            </button>
            
            <!-- === FUNDS === -->
            <button @click="sendQuickMessage('Show me my fund balances')" 
                    class="text-xs px-2.5 py-1 bg-gray-50 dark:bg-gray-800 text-gray-600 dark:text-gray-300 rounded-full hover:bg-blue-50 dark:hover:bg-blue-950/40 hover:text-blue-600 dark:hover:text-blue-400 transition-all duration-150 border border-gray-200/40 dark:border-gray-700/50">
                💰 Funds
            </button>
        </div>
    </div>

    <!-- Input -->
    <div class="flex-shrink-0 p-3 border-t border-gray-100 dark:border-gray-800/80 bg-white dark:bg-gray-900">
        <div class="flex items-center space-x-2 bg-gray-50 dark:bg-gray-800/50 rounded-xl p-1 border border-gray-200/60 dark:border-gray-700/60 focus-within:ring-2 focus-within:ring-blue-500/40 dark:focus-within:ring-blue-400/30 focus-within:border-blue-500 transition-all duration-200">
            <input type="text" 
                   x-model="userMessage"
                   @keydown.enter.prevent="submitMessage()"
                   placeholder="Ask Otto anything..."
                   :disabled="isTyping"
                   class="flex-1 bg-transparent border-0 outline-none focus:ring-0 text-sm py-2 px-3 text-gray-700 dark:text-gray-200 placeholder-gray-400 dark:placeholder-gray-500 disabled:opacity-60">
            
            <button @click="submitMessage()" 
                    :disabled="isTyping || !userMessage.trim()"
                    class="p-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-all duration-200 text-sm disabled:bg-gray-200 dark:disabled:bg-gray-800 disabled:text-gray-400 dark:disabled:text-gray-600 disabled:cursor-not-allowed">
                <svg x-show="!isTyping" class="w-4 h-4 transform rotate-90" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                </svg>
                <svg x-show="isTyping" class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </button>
        </div>
    </div>
</div>

<!-- Styles -->
<style>
    .chip-btn {
        @apply text-xs px-3 py-1.5 bg-gray-50 dark:bg-gray-800 text-gray-600 dark:text-gray-300 rounded-full hover:bg-blue-50 dark:hover:bg-blue-950/40 hover:text-blue-600 dark:hover:text-blue-400 transition-all duration-150 border border-gray-200/40 dark:border-gray-700/50 shadow-sm font-medium inline-flex items-center outline-none;
    }
    .no-scrollbar::-webkit-scrollbar {
        display: none;
    }
    .no-scrollbar {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }
    @keyframes blink { 0%, 100% { opacity: 1; } 50% { opacity: 0; } }
    .animate-blink { animation: blink 0.9s infinite; }
    
    /* Responsive Table Styles */
    .prose {
        max-width: 100%;
    }
    
    .prose .table-wrapper {
        overflow-x: auto;
        margin: 0.5rem -0.25rem;
        max-width: 100%;
    }
    
    @media (min-width: 640px) {
        .prose .table-wrapper {
            margin: 0.5rem 0;
        }
    }
    
    .prose table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.75rem;
        text-align: left;
        min-width: 300px;
    }
    
    @media (min-width: 640px) {
        .prose table {
            font-size: 0.875rem;
        }
    }
    
    .prose table th,
    .prose table td {
        padding: 0.5rem;
        border: 1px solid #e5e7eb;
        white-space: nowrap;
    }
    
    .dark .prose table th,
    .dark .prose table td {
        border-color: #374151;
    }
    
    .prose table td.break-long {
        white-space: normal;
        word-break: break-word;
        max-width: 150px;
    }
    
    @media (min-width: 640px) {
        .prose table td.break-long {
            max-width: none;
        }
    }
    
    .prose table th {
        background-color: #f3f4f6;
        font-weight: 600;
        color: #374151;
    }
    
    .dark .prose table th {
        background-color: #1f2937;
        color: #d1d5db;
    }
    
    /* Mobile-friendly table scroll hint */
    .prose .table-wrapper::after {
        content: '← scroll →';
        font-size: 10px;
        color: #9ca3af;
        display: block;
        text-align: center;
        margin-top: 0.25rem;
    }
    
    .dark .prose .table-wrapper::after {
        color: #6b7280;
    }
    
    @media (min-width: 640px) {
        .prose .table-wrapper::after {
            display: none;
        }
    }
    
    .prose ul {
        list-style-type: disc;
        padding-left: 1.25rem;
        margin: 0.25rem 0;
    }
    
    .prose p {
        margin: 0.25rem 0;
        line-height: 1.625;
    }
    
    .animate-spin { animation: spin 1s linear infinite; }
    @keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
    
    /* Custom scrollbar for chat */
    .overflow-y-auto::-webkit-scrollbar {
        width: 4px;
    }
    .overflow-y-auto::-webkit-scrollbar-track {
        background: transparent;
    }
    .overflow-y-auto::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 2px;
    }
    .dark .overflow-y-auto::-webkit-scrollbar-thumb {
        background: #475569;
    }
</style>
</div>