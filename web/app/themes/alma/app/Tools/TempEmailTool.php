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
                    return "Error: Action must be 'generate', 'fetch', or 'send'.";
                }

                // Validate token for fetch and send actions
                if (in_array($action, ['fetch', 'send']) && empty($token)) {
                    Log::debug("Missing token for $action action");
                    return "Error: A valid token is required for $action action.";
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
                        return "Temporary email: <strong>$email</strong><br>Token: <strong>$token</strong> (Use this token to fetch or send emails. Inbox expires after 1 hour.)";
                    }

                    Log::debug("Error generating temporary email: " . ($response->failed() ? $response->status() : 'No data'));
                    return "Error generating temporary email: " . ($response->failed() ? $response->status() : 'No data');
                }

                if ($action === 'fetch') {
                    // Fetch emails for the provided token
                    $response = Http::get('https://api.tempmail.lol/v2/inbox', ['token' => $token]);
                    $data = $response->json();
                    Log::debug("TempMail.lol fetch response for token $token: " . json_encode($data));

                    if ($response->successful() && isset($data['emails']) && !empty($data['emails'])) {
                        $output = "<table border='1'>";
                        $output .= "<tr><th>Sender</th><th>Subject</th><th>Date</th></tr>";
                        foreach ($data['emails'] as $emailItem) {
                            $sender = htmlspecialchars($emailItem['from'] ?? 'Unknown');
                            $subject = htmlspecialchars($emailItem['subject'] ?? 'No subject');
                            $date = htmlspecialchars($emailItem['date'] ?? 'N/A');
                            $output .= "<tr><td>$sender</td><td>$subject</td><td>$date</td></tr>";
                        }
                        $output .= "</table>";
                        Log::debug("Fetched emails for token $token: $output");
                        return $output;
                    }

                    Log::debug("No emails found or error fetching for token $token: " . ($response->failed() ? $response->status() : 'No data or inbox expired'));
                    return "No emails found or error fetching for token $token: " . ($response->failed() ? $response->status() : 'No data or inbox may have expired');
                }

                if ($action === 'send') {
                    // Validate send parameters
                    if (!filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
                        Log::debug("Invalid recipient email: $recipient");
                        return "Error: Invalid recipient email address.";
                    }

                    if (empty(trim($subject))) {
                        Log::debug("Subject cannot be empty for send action");
                        return "Error: Subject cannot be empty.";
                    }

                    if (empty(trim($body))) {
                        Log::debug("Body cannot be empty for send action");
                        return "Error: Body cannot be empty.";
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
                        return "Email sent to <strong>$recipient</strong> with subject '<strong>$subject</strong>' using token <strong>$token</strong>.";
                    }

                    Log::debug("Error sending email to $recipient with token $token: " . ($response->failed() ? $response->status() : ($data['error'] ?? 'No success response')));
                    return "Error sending email to $recipient: " . ($response->failed() ? $response->status() : ($data['error'] ?? 'No success response or invalid token'));
                }
            });
    }
}