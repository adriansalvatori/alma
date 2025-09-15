<?php

namespace App\Livewire;

use Livewire\Component;

class ContactForm extends Component
{
    public $name = '';
    public $email = '';
    public $subject = '';
    public $message = '';

    public $success = false;

    protected $rules = [
        'name' => 'required|string|max:255',
        'email' => 'required|email|max:255',
        'subject' => 'required|string|max:255',
        'message' => 'required|string',
    ];

    public function send()
    {
        $validated = $this->validate();
        // Here you can handle the form submission, e.g., send email or store in DB
        // Example: Mail::to('your@email.com')->send(new ContactMail($validated));
        // For now, just reset the form and optionally show a success message
        $this->reset(['name', 'email', 'subject', 'message']);
        $this->success = true;
    }

    public function resetSuccess()
    {
        $this->success = false;
    }

    public function render()
    {
        return view('livewire.contact-form');
    }
}
