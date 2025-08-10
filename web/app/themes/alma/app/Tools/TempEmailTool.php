<?php

namespace App\Tools;

use Illuminate\Support\Facades\Log;
use Prism\Prism\Facades\Tool;
use Illuminate\Support\Facades\Http;

class TempEmailTool
{
    public function definition()
    {
        return Tool::as('tempemail')
            ->for('Generate, fetch, or send emails using a temporary email address')
            ->withStringParameter('action', 'The action to perform (generate, fetch, or send)', true)
            ->withStringParameter('token', 'The token for the temporary email inbox (required for fetch or send)', false)
            ->withStringParameter('recipient', 'The recipient email address (required for send)', false)
            ->withStringParameter('subject', 'The subject of the email (required for send)', false)
            ->withStringParameter('body', 'The body of the email (required for send)', false)
            ->using(function (string $action = 'generate', ?string $token = null, ?string $recipient = null, ?string $subject = null, ?string $body = null, ?string $domain = null, ?string $prefix = null) {
                // Validate action
                if (!in_array($action, ['generate', 'fetch', 'send'])) {
                    Log::debug("Invalid action provided: $action");
                    return json_encode(['type' => 'error', 'data' => ['message' => "Action must be 'generate', 'fetch', or 'send'."], 'view' => 'livewire.tools.error']);
                }

                // Validate token for fetch and send actions
                if (in_array($action, ['fetch', 'send']) && empty($token)) {
                    Log::debug("Missing token for $action action");
                    return json_encode(['type' => 'error', 'data' => ['message' => "A valid token is required for $action action."], 'view' => 'livewire.tools.error']);
                }

                if ($action === 'generate') {
                    // Generate a new temporary email
                    $payload = array_filter([
                        'domain' => $domain,
                        'prefix' => $prefix,
                    ]);

                    $response = Http::withHeaders(['Content-Type' => 'application/json'])
                        ->post('https://api.tempmail.lol/v2/inbox/create', $payload);
                    $data = $response->json();
                    Log::debug("TempMail.lol generate response: " . json_encode($data));

                    if ($response->successful() && isset($data['address'], $data['token'])) {
                        $email = $data['address'];
                        $token = $data['token'];
                        Log::debug("Generated temporary email: $email, token: $token");
                        return json_encode([
                            'type' => 'tempemail',
                            'data' => [
                                'action' => 'generate',
                                'email' => $email,
                                'token' => $token,
                            ],
                            'view' => 'livewire.tools.tempemail',
                        ]);
                    }

                    Log::debug("Error generating temporary email: " . ($response->failed() ? $response->status() : 'No data'));
                    return json_encode(['type' => 'error', 'data' => ['message' => "Error generating temporary email: " . ($response->failed() ? $response->status() : 'No data')], 'view' => 'livewire.tools.error']);
                }

                if ($action === 'fetch') {
                    // Fetch emails for the provided token
                    $response = Http::get('https://api.tempmail.lol/v2/inbox', ['token' => $token]);
                    $data = $response->json();
                    Log::debug("TempMail.lol fetch response for token $token: " . json_encode($data));

                    if ($response->successful() && isset($data['emails']) && !empty($data['emails'])) {
                        return json_encode([
                            'type' => 'tempemail',
                            'data' => [
                                'action' => 'fetch',
                                'emails' => $data['emails'],
                            ],
                            'view' => 'livewire.tools.tempemail',
                        ]);
                    }

                    Log::debug("No emails found or error fetching for token $token: " . ($response->failed() ? $response->status() : 'No data or inbox expired'));
                    return json_encode(['type' => 'error', 'data' => ['message' => "No emails found or error fetching for token $token: " . ($response->failed() ? $response->status() : 'No data or inbox may have expired')], 'view' => 'livewire.tools.error']);
                }

                if ($action === 'send') {
                    // Validate send parameters
                    if (!filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
                        Log::debug("Invalid recipient email: $recipient");
                        return json_encode(['type' => 'error', 'data' => ['message' => 'Invalid recipient email address.'], 'view' => 'livewire.tools.error']);
                    }

                    if (empty(trim($subject))) {
                        Log::debug("Subject cannot be empty for send action");
                        return json_encode(['type' => 'error', 'data' => ['message' => 'Subject cannot be empty.'], 'view' => 'livewire.tools.error']);
                    }

                    if (empty(trim($body))) {
                        Log::debug("Body cannot be empty for send action");
                        return json_encode(['type' => 'error', 'data' => ['message' => 'Body cannot be empty.'], 'view' => 'livewire.tools.error']);
                    }

                    // Construct payload for sending email
                    $payload = [
                        'to' => $recipient,
                        'subject' => $subject,
                        'body' => $body,
                    ];

                    // Send email with token in Authorization header
                    $response = Http::withHeaders([
                        'Content-Type' => 'application/json',
                        'Authorization' => "Bearer $token",
                    ])->post('https://api.tempmail.lol/v2/send', $payload);
                    $data = $response->json();
                    Log::debug("TempMail.lol send response for token $token to $recipient: " . json_encode($data));

                    if ($response->successful() && isset($data['success']) && $data['success']) {
                        Log::debug("Email sent successfully to $recipient with token $token");
                        return json_encode([
                            'type' => 'tempemail',
                            'data' => [
                                'action' => 'send',
                                'recipient' => $recipient,
                                'subject' => $subject,
                            ],
                            'view' => 'livewire.tools.tempemail',
                        ]);
                    }

                    Log::debug("Error sending email to $recipient with token $token: " . ($response->failed() ? $response->status() : ($data['error'] ?? 'No success response')));
                    return json_encode(['type' => 'error', 'data' => ['message' => "Error sending email to $recipient: " . ($response->failed() ? $response->status() : ($data['error'] ?? 'No success response or invalid token'))], 'view' => 'livewire.tools.error']);
                }
            });
    }
}