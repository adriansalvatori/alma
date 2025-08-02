<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\On;
use Prism\Prism\Prism;

class AlmaAI extends Component
{
    public $input = '';
    public $messages = [];
    public $streamedResponse = '';
    public $isTyping = false;

    public function send()
    {
        $prompt = $this->input;

        // Add user message to messages array immediately
        $this->messages[] = ['user' => 'You', 'text' => $prompt];
        $this->input = '';

        // Reset streamed response and set typing state
        $this->streamedResponse = '';
        $this->isTyping = true;

        // Dispatch UI updates first
        $this->dispatch('scroll-down');

        // Dispatch the event to trigger the AI response
        $this->dispatch('getAiResponse', $prompt);
    }

    #[On('getAiResponse')]
    public function getAIResponse($prompt)
    {
        $this->streamResponse($prompt);
    }

    public function streamResponse($prompt)
    {
        $stream = Prism::text()
            ->using('gemini', 'gemini')
            ->withPrompt($prompt)
            ->asStream();

        foreach ($stream as $chunk) {
            // Stream only new chunk text
            $this->stream('response', $chunk->text);

            // Accumulate full response for storage
            $this->streamedResponse .= $chunk->text;
        }

        // Add completed response to messages array
        $this->messages[] = ['user' => 'AI', 'text' => $this->streamedResponse];

        // Reset streaming state
        $this->streamedResponse = '';
        $this->isTyping = false;

        $this->dispatch('scroll-down');
    }

    public function render()
    {
        return view('livewire.chat-component');
    }
}