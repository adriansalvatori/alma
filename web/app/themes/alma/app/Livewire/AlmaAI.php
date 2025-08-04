<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\On;
use Prism\Prism\Prism;
use Prism\Prism\Enums\Provider;
use Prism\Prism\ValueObjects\Messages\AssistantMessage;
use Prism\Prism\ValueObjects\Messages\UserMessage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use App\Models\AiConversation;
use App\Services\AiToolsService; // Import the service

class AlmaAI extends Component
{
    public $input = '';
    public $messages = [];
    public $streamedResponse = '';
    public $isTyping = false;
    public $threadId;
    public $agentConfig;
    public $selectedAgent;
    private $isProcessing = false;

    public function mount($agent = 'galena')
    {
        $availableAgents = array_keys(config('ai_agents', []));
        $this->selectedAgent = in_array($agent, $availableAgents) ? $agent : 'galena';
        $this->agentConfig = config('ai_agents.' . $this->selectedAgent) ?: config('ai_agents.galena');
        $this->threadId = session('thread_id_' . $this->selectedAgent, Str::uuid()->toString());
        $this->messages = $this->loadMessages();
        session(['thread_id_' . $this->selectedAgent => $this->threadId]);
    }

    protected function loadMessages()
    {
        $agentName = $this->agentConfig['name'] ?? 'galena';
        $conversation = AiConversation::byThreadAndAgent($this->threadId, $agentName)->first();
        return $conversation ? (json_decode($conversation->meta->messages ?? '[]', true) ?: []) : [];
    }

    protected function saveMessages()
    {
        $current_user = wp_get_current_user();
        $user_id = $current_user->exists() ? $current_user->ID : 0;

        $args = [
            'post_type' => 'ai_conversation',
            'meta_query' => [
                [
                    'key' => 'thread_id',
                    'value' => $this->threadId,
                ],
            ],
            'posts_per_page' => 1,
        ];
        $existing_post = get_posts($args);

        $post_data = [
            'post_title' => 'Conversation ' . $this->threadId,
            'post_type' => 'ai_conversation',
            'post_status' => 'publish',
            'post_author' => $user_id,
        ];

        $post_id = $existing_post ? wp_update_post(array_merge(['ID' => $existing_post[0]->ID], $post_data)) : wp_insert_post($post_data, true);
        if (is_wp_error($post_id)) {
            Log::error('WP Post Error', ['error' => $post_id->get_error_message()]);
            return;
        }

        // Log::debug('Post Saved', ['post_id' => $post_id, 'threadId' => $this->threadId]);
        $conversation = AiConversation::find($post_id);
        if (!$conversation) {
            $conversation = new AiConversation();
            $conversation->ID = $post_id;
        }

        $conversation->meta()->updateOrCreate(['meta_key' => 'thread_id'], ['meta_value' => $this->threadId]);
        $conversation->meta()->updateOrCreate(['meta_key' => 'agent_id'], ['meta_value' => $this->agentConfig['name']]);
        $conversation->meta()->updateOrCreate(['meta_key' => 'user_id'], ['meta_value' => $user_id]);
        $conversation->meta()->updateOrCreate(['meta_key' => 'messages'], ['meta_value' => json_encode($this->messages)]);
        session(['chat_history_' . $this->agentConfig['name'] => $this->messages]);
    }

    public function send()
    {
        if ($this->isProcessing || empty(trim($this->input))) return;

        $this->isProcessing = true;
        $prompt = sanitize_text_field(trim($this->input));

        $current_user = wp_get_current_user();
        $user_identifier = $current_user->exists() ? $current_user->ID : request()->ip();
        $cacheKey = 'ai_chat_rate_' . md5($user_identifier . $this->threadId);
        if (Cache::store()->has($cacheKey)) {
            $this->messages[] = ['user' => 'AI', 'text' => 'Slow down! Too many requests.'];
            $this->saveMessages();
            $this->isProcessing = false;
            return;
        }

        Cache::store()->put($cacheKey, true, 10);
        $this->messages[] = ['user' => 'You', 'text' => $prompt];
        $this->input = '';
        $this->isTyping = true;
        $this->saveMessages();
        $this->dispatch('scroll-down');
        $this->dispatch('getAiResponse', ['prompt' => $prompt, 'threadId' => $this->threadId]);
        $this->isProcessing = false;
    }

    #[On('getAiResponse')]
    public function getAIResponse($data)
    {
        if ($this->isProcessing) return;
        $this->isProcessing = true;
        $this->streamResponse($data['prompt'], $data['threadId']);
        $this->isProcessing = false;
    }

    protected function streamResponse($prompt, $threadId)
    {
        $cacheKey = 'ai_response_' . md5($prompt . ($this->agentConfig['name'] ?? 'galena') . $threadId);
        $cached = Cache::store()->remember($cacheKey, now()->addDay(), function () use ($prompt) {
            $messages = [];
            foreach (array_slice($this->messages, -10) as $msg) {
                $messages[] = $msg['user'] === 'You' ? new UserMessage($msg['text']) : new AssistantMessage($msg['text']);
            }

            try {
                $stream = Prism::text()
                    ->using($this->agentConfig['provider'] ?? Provider::Gemini, $this->agentConfig['model'] ?? 'gemini-2.5-flash')
                    ->withSystemPrompt($this->agentConfig['system_prompt'] ?? 'You are a default AI assistant.')
                    ->withMessages($messages)
                    ->withTools((new AiToolsService())->getTools()) // Use the service
                    ->withMaxSteps(3)
                    ->asStream();

                $toolsUsed = [];
                $fullResponse = '';

                foreach ($stream as $chunk) {
                    if (!empty($chunk->text)) {
                        $this->streamedResponse .= wp_kses_post($chunk->text);
                        $this->stream('response', content: $this->streamedResponse);
                        $fullResponse .= $chunk->text;
                    }
                    if (!empty($chunk->toolCalls)) {
                        foreach ($chunk->toolCalls as $toolCall) {
                            $toolsUsed[] = $toolCall->name;
                        }
                    }
                }

                return ['text' => $fullResponse, 'tools_used' => array_unique($toolsUsed)];
            } catch (\Exception $e) {
                Log::error('Stream error', ['error' => $e->getMessage()]);
                return ['text' => 'Oops, something went wrong! Try again.', 'tools_used' => []];
            }
        });

        if ($cached['text'] && $cached['text'] !== 'Oops, something went wrong! Try again.') {
            if (!in_array(['user' => 'AI', 'text' => $cached['text'], 'tools_used' => $cached['tools_used']], $this->messages)) {
                $this->messages[] = ['user' => 'AI', 'text' => $cached['text'], 'tools_used' => $cached['tools_used']];
                $this->saveMessages();
            }
        }

        $this->isTyping = false;
        $this->streamedResponse = '';
        $this->dispatch('scroll-down');
    }

    public function resetConversation()
    {
        $this->messages = [];
        $this->threadId = Str::uuid()->toString();
        session(['thread_id_' . $this->agentConfig['name'] => $this->threadId, 'chat_history_' . $this->agentConfig['name'] => []]);
        $this->saveMessages();
    }

    public function loadAgent()
    {
        $this->mount($this->selectedAgent);
        $this->dispatch('scroll-down');
    }

    public function render()
    {
        return view('livewire.alma-ai');
    }
}