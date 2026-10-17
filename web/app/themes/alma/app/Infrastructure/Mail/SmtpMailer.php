<?php

namespace App\Infrastructure\Mail;

use Illuminate\Support\Facades\Log;

/**
 * Send wp_mail() through SMTP when MAIL_HOST is set (config/mail.php).
 *
 * Shared hosting's PHP mail() is the reason contact-form emails "never arrive": no SPF/DKIM
 * alignment, shared IP reputation. An SMTP provider fixes that; this applies it to every email
 * WordPress, plugins and the theme send.
 */
class SmtpMailer
{
    public function register(): void
    {
        add_action('phpmailer_init', [$this, 'configure']);
        add_filter('wp_mail_from', [$this, 'fromAddress']);
        add_filter('wp_mail_from_name', [$this, 'fromName']);
        add_action('wp_mail_failed', [$this, 'logFailure']);
    }

    public static function configured(): bool
    {
        return filled(config('mail.wordpress.host'));
    }

    /**
     * @param  object  $mailer  PHPMailer\PHPMailer\PHPMailer (any object with its public properties)
     */
    public function configure(object $mailer): void
    {
        if (! self::configured()) {
            return;
        }

        $config = (array) config('mail.wordpress');
        $host = (string) $config['host'];

        $mailer->isSMTP();
        $mailer->Host = $host;
        $mailer->Port = (int) ($config['port'] ?? 587);
        $mailer->Timeout = (int) ($config['timeout'] ?? 10);
        $mailer->SMTPSecure = $this->encryption($host, (string) ($config['encryption'] ?? 'tls'));
        $mailer->SMTPAutoTLS = $mailer->SMTPSecure !== '';

        if (filled($config['username'] ?? null)) {
            $mailer->SMTPAuth = true;
            $mailer->Username = (string) $config['username'];
            $mailer->Password = (string) ($config['password'] ?? '');
        }
    }

    /**
     * "none" is only honoured for local mail catchers; anywhere else credentials would cross the
     * network in clear text, so it is upgraded to STARTTLS.
     */
    public function encryption(string $host, string $requested): string
    {
        $requested = strtolower(trim($requested));

        if (in_array($requested, ['ssl', 'tls'], true)) {
            return $requested;
        }

        return $this->isLocal($host) ? '' : 'tls';
    }

    public function fromAddress(mixed $address): string
    {
        return filled(config('mail.from.address')) ? (string) config('mail.from.address') : (string) $address;
    }

    public function fromName(mixed $name): string
    {
        return filled(config('mail.from.name')) ? (string) config('mail.from.name') : (string) $name;
    }

    /**
     * Recipients and body are left out of the log on purpose: they are personal data.
     */
    public function logFailure(mixed $error): void
    {
        $message = $error instanceof \WP_Error ? $error->get_error_message() : 'Unknown error';

        Log::error('wp_mail failed: '.$message, ['smtp' => self::configured()]);
    }

    private function isLocal(string $host): bool
    {
        $host = strtolower($host);

        return in_array($host, (array) config('mail.wordpress.local_hosts', []), true) || str_ends_with($host, '.test');
    }
}
