<div class="columns is-flex is-flex-direction-column is-fullheight has-background-grey-dark has-text-grey-lighter py-5">
    <div class="column is-flex-grow-1 is-flex is-flex-direction-column is-overflow-auto px-4" id="messages">
        @foreach($messages as $message)
            <div class="is-flex {{ $message['user'] === 'You' ? 'is-justify-content-flex-end' : 'is-justify-content-flex-start' }}">
                <div class="box is-shadowless is-radiusless">
                    <div class="{{ $message['user'] === 'You' ? 'has-background-link' : 'has-background-grey-darker' }} box is-shadowless p-3">
                        <p class="is-size-7 is-word-break">
                            {{ $message['text'] }}
                        </p>
                    </div>
                    <span class="is-block is-size-7 mt-1 has-text-grey-light {{ $message['user'] === 'You' ? 'has-text-right' : 'has-text-left' }}">
                        {{ $message['user'] }}
                    </span>
                </div>
            </div>
        @endforeach

        @if($isTyping)
            <div class="is-flex is-justify-content-flex-start">
                <div class="box is-shadowless is-radiusless">
                    <div class="has-background-grey-darker box is-shadowless p-3">
                        <p class="is-size-7 is-word-break" wire:stream="response">
                            <!-- Streamed content will appear here -->
                        </p>
                    </div>
                    <span class="is-block is-size-7 mt-1 has-text-grey-light has-text-left">
                        AI
                    </span>
                </div>
            </div>
        @endif
    </div>

    <div class="column px-4 pt-4 has-border-top has-border-grey-darker">
        <form wire:submit.prevent="send" class="is-flex is-flex-direction-row is-justify-content-space-between">
            <input type="text"
                   wire:model.defer="input"
                   placeholder="Ask something..."
                   class="input is-fullwidth is-rounded is-borderless has-background-grey-darker has-text-grey-lighter placeholder-grey-light p-3 shadow focus:ring-link focus:border-link"
                   required />
            <button type="submit"
                    class="button is-link is-rounded px-5 shadow is-uppercase"
            >
                Send
            </button>
        </form>
    </div>
</div>
