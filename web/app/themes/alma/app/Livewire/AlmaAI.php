<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\On;
use Prism\Prism\Prism;
use Prism\Prism\Enums\Provider;
use Prism\Prism\Providers\Gemini\Gemini;
use Prism\Prism\ValueObjects\ProviderTool;
use Prism\Prism\ValueObjects\Messages\AssistantMessage;
use Prism\Prism\ValueObjects\Messages\UserMessage;
use Prism\Prism\Facades\Tool;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class AlmaAI extends Component
{
    public $input = '';
    public $messages = [];
    public $streamedResponse = '';
    public $isTyping = false;
    public $threadId;

    public $systemPrompt = 'You are Galena, a charming and witty AI assistant created by Alma. Use tools when needed and provide clear, concise answers with a touch of bitter humor.';

    public function mount()
    {
        // Load or initialize thread ID and conversation history from session
        $this->threadId = session('thread_id', Str::uuid()->toString());
        $this->messages = session('chat_history', []);
        session(['thread_id' => $this->threadId]);
    }

    public function send()
    {
        $prompt = trim($this->input);
        if (empty($prompt)) return;

        // Add user message immediately
        $this->messages[] = ['user' => 'You', 'text' => $prompt];
        $this->input = '';
        $this->isTyping = true;
        $this->streamedResponse = '';

        // Save to session
        session(['chat_history' => $this->messages]);

        $this->dispatch('scroll-down');
        $this->dispatch('getAiResponse', $prompt);
    }

    #[On('getAiResponse')]
    public function getAIResponse($prompt)
    {
        $this->streamResponse($prompt);
        Log::debug('messages', $this->messages);
    }

    public function streamResponse($prompt)
    {
        Log::debug('Starting streamResponse', ['thread_id' => $this->threadId, 'prompt' => $prompt]);

        // Define a custom calculator tool
        $calculatorTool = Tool::as('calculate')
            ->for('Perform basic arithmetic calculations')
            ->withNumberParameter('a', 'First number')
            ->withNumberParameter('b', 'Second number')
            ->withStringParameter('operation', 'The operation to perform: add, subtract, multiply, divide')
            ->using(function (float $a, float $b, string $operation): string {
                Log::debug('Calculator tool called', ['a' => $a, 'b' => $b, 'operation' => $operation]);
                switch ($operation) {
                    case 'add': return (string) ($a + $b);
                    case 'subtract': return (string) ($a - $b);
                    case 'multiply': return (string) ($a * $b);
                    case 'divide': return $b != 0 ? (string) ($a / $b) : 'Error: Division by zero';
                    default: return 'Error: Invalid operation';
                }
            });

        // Add previous messages for context
        foreach ($this->messages as $msg) {
            $messages[] = $msg['user'] === 'You' ? new UserMessage($msg['text']) : new AssistantMessage($msg['text']);
        }

        try {
            $stream = Prism::text()
                ->using(Provider::Gemini, 'gemini-2.5-flash')
                ->withSystemPrompt($this->systemPrompt)
                ->withMessages($messages)
                ->withTools([$calculatorTool])
                ->withProviderTools([new ProviderTool('google_search')])
                ->withMaxSteps(3) // Allow multi-step reasoning
                ->asStream();

            $toolsUsed = [];
            $fullResponse = '';

            foreach ($stream as $chunk) {
                if (!empty($chunk->text)) {
                    $this->stream('response', content: $chunk->text);
                    $fullResponse .= $chunk->text;
                }

                // Track tool usage
                if (!empty($chunk->toolCalls)) {
                    foreach ($chunk->toolCalls as $toolCall) {
                        $toolsUsed[] = $toolCall->name;
                        Log::debug('Tool call', ['tool' => $toolCall->name, 'args' => $toolCall->arguments()]);
                    }
                }
            }

            // Store the final response with tool usage
            $this->messages[] = [
                'user' => 'AI',
                'text' => $fullResponse,
                'tools_used' => array_unique($toolsUsed),
            ];
            session(['chat_history' => $this->messages]);

            Log::debug('Response completed', ['response' => $fullResponse, 'tools_used' => $toolsUsed]);
        } catch (\Exception $e) {
            Log::error('Stream error', ['error' => $e->getMessage()]);
            $this->messages[] = ['user' => 'AI', 'text' => 'Oops, something went wrong! Try again.'];
            session(['chat_history' => $this->messages]);
        }

        $this->isTyping = false;
        $this->streamedResponse = '';
        $this->dispatch('scroll-down');
    }

    public function render()
    {
        return view('livewire.alma-ai');
    }
}