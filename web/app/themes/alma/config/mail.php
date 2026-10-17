<?php

/*
|--------------------------------------------------------------------------
| Mail
|--------------------------------------------------------------------------
|
| WordPress sends every email (wp_mail) through PHPMailer. On shared hosting
| PHP's mail() is unreliable: messages land in spam or vanish. Set MAIL_HOST
| to send through an SMTP provider instead; nothing changes until you do.
|
| App\Infrastructure\Mail\SmtpMailer applies "wordpress" on phpmailer_init.
| Credentials never travel in clear text: encryption falls back to STARTTLS
| for any host that is not a local mail catcher (Mailpit, 127.0.0.1).
|
| Acorn merges this file over its own mail config per top-level key, so
| "from" also serves Laravel's mailer if a project ever uses it.
|
*/

return [

    'wordpress' => [
        'host' => env('MAIL_HOST'),
        'port' => (int) env('MAIL_PORT', 587),
        'username' => env('MAIL_USERNAME'),
        'password' => env('MAIL_PASSWORD'),
        // tls (STARTTLS, usually port 587), ssl (implicit TLS, port 465), or none (local catchers only).
        'encryption' => env('MAIL_ENCRYPTION', 'tls'),
        'timeout' => 10,
        'local_hosts' => ['127.0.0.1', 'localhost', '::1', 'mailpit', 'mailhog'],
    ],

    'from' => [
        'address' => env('MAIL_FROM_ADDRESS'),
        'name' => env('MAIL_FROM_NAME'),
    ],

];
