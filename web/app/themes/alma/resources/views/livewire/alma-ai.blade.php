<div class="box is-outlined is-transparent">
    <div class="chat-messages"  data-lenis-prevent style="max-height: 400px; overflow-y: scroll;" x-data="{ scrollToBottom() { this.$el.scrollTop = this.$el.scrollHeight; } }" x-init="scrollToBottom()" @scroll-down.window="scrollToBottom()">
        @foreach ($messages as $message)
            <div class="chat-message {{ $message['user'] === 'You' ? 'is-from-me' : 'is-from-ai' }}">
                <div class="chat-message-content">
                    <p>{!! nl2br(e($message['text'])) !!}</p>
                </div>
                <div class="chat-message-meta">
                    <span>{{ $message['user'] }}</span>
                    @if ($message['tools_used'] ?? false)
                        <span class="tag is-info">Tools: {{ implode(', ', $message['tools_used']) }}</span>
                    @endif
                </div>
            </div>
        @endforeach

        @if ($isTyping)
            <div class="chat-message is-from-ai">
                <div class="chat-message-content">
                    <p wire:stream="response" class="typing-indicator">
                        <span class="dot"></span><span class="dot"></span><span class="dot"></span>
                    </p>
                </div>
                <div class="chat-message-meta">
                    <span>AI</span>
                </div>
            </div>
        @endif
    </div>

    <form wire:submit.prevent="send" class="form">
        <div class="field is-grouped">
            <div class="control is-expanded">
                <input class="input" type="text" wire:model="input" placeholder="Ask something..." required />
            </div>
            <div class="control">
                <button class="button is-primary" type="submit">
                    Send
                </button>
            </div>
        </div>
    </form>
</div>

@push('css')
<style>
    .chat-message { margin: 10px; padding: 10px; border-radius: 8px; }
    .is-from-me { background: #ffffff; margin-left: 20%; color: black;}
    .is-from-ai { background: #121212; margin-right: 20%; }
    .typing-indicator .dot {
        display: inline-block; width: 8px; height: 8px; border-radius: 50%; background: #888; margin: 0 2px;
        animation: pulse 1.4s infinite ease-in-out;
    }
    .typing-indicator .dot:nth-child(2) { animation-delay: 0.2s; }
    .typing-indicator .dot:nth-child(3) { animation-delay: 0.4s; }
    @keyframes pulse { 0%, 80%, 100% { transform: scale(0.8); opacity: 0.5; } 40% { transform: scale(1.2); opacity: 1; } }
</style>
@endpush