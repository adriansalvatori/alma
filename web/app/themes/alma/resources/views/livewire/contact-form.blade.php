<div class="container is-max-tablet">
    @if ($success)
        <div class="notification is-success">
            <button class="delete" wire:click="resetSuccess"></button>
            {{ $success }}
        </div>
    @endif
    <form wire:submit.prevent="send">
        <div class="field">
            <label class="label" for="name">Name</label>
            <div class="control">
                <input class="input" type="text" wire:model="name" required />
            </div>
            @error('name')
                <p class="help is-danger">{{ $message }}</p>
            @enderror
        </div>

        <div class="field">
            <label class="label" for="email">Email</label>
            <div class="control">
                <input class="input" type="email" wire:model="email" required />
            </div>
            @error('email')
                <p class="help is-danger">{{ $message }}</p>
            @enderror
        </div>

        <div class="field">
            <label class="label" for="subject">Subject</label>
            <div class="control">
                <input class="input" type="text" wire:model="subject" required />
            </div>
            @error('subject')
                <p class="help is-danger">{{ $message }}</p>
            @enderror
        </div>

        <div class="field">
            <label class="label" for="message">Message</label>
            <div class="control">
                <textarea class="textarea" wire:model="message" required></textarea>
            </div>
            @error('message')
                <p class="help is-danger">{{ $message }}</p>
            @enderror
        </div>

        <div class="field is-grouped">
            <div class="control">
                <button class="button is-link" type="submit">Send</button>
            </div>
        </div>
    </form>
</div>
