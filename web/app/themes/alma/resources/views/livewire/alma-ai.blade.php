<div class="columns is-desktop">
    <!-- Sidebar for Recent Conversations -->
    <div class="column is-3">
        <div class="box is-outlined is-transparent">
            <h3 class="title is-5 has-text-white">Recent Conversations</h3>
            <div class="field">
                <div class="control">
                    <input class="input is-small" type="text" wire:model.live.debounce.500ms="searchQuery" placeholder="Search conversations..." />
                </div>
            </div>
            <div class="menu" style="max-height: 400px; overflow-y: auto;" data-lenis-prevent="">
                <ul class="menu-list">
                    @forelse ($recentConversations as $conversation)
                        <li>
                            <div class="box is-outlined is-transparent level p-1 mb-2 is-shadowless">
                                <a 
                                    wire:click="loadConversation('{{ $conversation['thread_id'] }}')"
                                    class="is-block {{ $threadId === $conversation['thread_id'] ? 'is-active' : '' }}"
                                    style="flex-grow: 1;"
                                >
                                    <strong class="is-size-7">{{ $conversation['preview'] }}</strong>
                                    <span class="is-size-7">{{ \Carbon\Carbon::parse($conversation['updated_at'])->diffForHumans() }}</span>
                                </a>
                                <button 
                                    class="button is-danger is-hidden is-outlined" style="aspect-ratio: 1/1; height: 10px; width: 20px;" 
                                    wire:click="deleteConversation('{{ $conversation['thread_id'] }}')"
                                    wire:confirm="Are you sure you want to delete this conversation?"
                                >
                                    <span class="icon">
                                        <i class="fas fa-trash"></i>
                                    </span>
                                </button>
                            </div>
                        </li>
                    @empty
                        <li><p class="has-text-grey">No conversations found.</p></li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>

    <!-- Main Chat Area -->
    <div class="column is-9">
        <div class="container is-max-desktop" style="height: 100%">
            <div class="box is-outlined is-transparent" style="height: 100%; display: flex; flex-direction: column; justify-content: space-between">
                <div class="control">
                    <select class="select is-small has-text-white is-transparent mb-3" wire:model="selectedAgent" wire:change="loadAgent">
                        @php
                            $agents = config('ai_agents', []);
                        @endphp
                        @foreach ($agents as $agentId => $agent)
                            <option value="{{ $agentId }}">{{ $agent['name'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="chat-messages" data-lenis-prevent style="max-height: 400px; overflow-y: scroll;"
                    x-data="{ scrollToBottom() { this.$el.scrollTop = this.$el.scrollHeight; } }" x-init="scrollToBottom()" @scroll-down.window="scrollToBottom()">
                    @foreach ($messages as $message)
                        <div class="chat-message {{ $message['user'] === 'You' ? 'is-from-me' : 'is-from-ai is-from-ai-' . $selectedAgent }}">
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
                        <div class="chat-message is-from-ai is-from-ai-{{ $selectedAgent }}">
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
                <form wire:submit.prevent="send" class="form" style="position: sticky; bottom: 20px;">
                    <div class="field is-grouped">
                        <div class="control is-expanded">
                            <input class="input" type="text" wire:model="input" placeholder="Ask {{ $agentConfig['name'] ?? 'AI' }} something..." required />
                        </div>
                        <div class="control">
                            <button class="button is-primary" type="submit">Send</button>
                            <button class="button is-primary is-outlined" wire:click="resetConversation">Reset</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('css')
    <style>
        .chat-message { margin: 10px; padding: 10px; border-radius: 8px; }
        .is-from-me { background: #ffffff; margin-left: 20%; color: black; }
        .is-from-ai-galena { background: #121212; margin-right: 20%; color: #fff; }
        .is-from-ai-mathbot { background: #1e3a8a; margin-right: 20%; color: #fff; }
        .typing-indicator .dot { display: inline-block; width: 8px; height: 8px; border-radius: 50%; background: #888; margin: 0 2px; animation: pulse 1.4s infinite ease-in-out; }
        .typing-indicator .dot:nth-child(2) { animation-delay: 0.2s; }
        .typing-indicator .dot:nth-child(3) { animation-delay: 0.4s; }
        @keyframes pulse {
            0%, 80%, 100% { transform: scale(0.8); opacity: 0.5; }
            40% { transform: scale(1.2); opacity: 1; }
        }
        .menu-list a.is-active { background-color: var(--primary); color: var(--primary-invert); }
        .menu-list a:hover { background-color: var(--primary-80); color: var(--primary-invert); }
    </style>
@endpush