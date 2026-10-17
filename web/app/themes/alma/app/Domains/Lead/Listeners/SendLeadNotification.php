<?php

namespace App\Domains\Lead\Listeners;

use App\Domains\Lead\Events\LeadCaptured;
use App\Domains\Lead\Models\LeadActivity;
use App\Domains\Lead\Services\LeadActivityLogger;
use App\Domains\Lead\Services\LeadSettings;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Email the team about a new lead, through wp_mail (so whatever SMTP setup the site has applies).
 * Runs after the response. Records its outcome and never throws.
 */
class SendLeadNotification
{
    public const ACTOR = 'EmailNotification';

    public function __construct(
        private LeadSettings $settings,
        private LeadActivityLogger $activity,
    ) {}

    public function handle(LeadCaptured $event): void
    {
        $lead = $event->lead;
        $recipients = $this->settings->recipients();

        if ($recipients === []) {
            $this->activity->record($lead, 'LeadCaptured', self::ACTOR, LeadActivity::SKIPPED, ['reason' => 'no recipients']);

            return;
        }

        if (! $this->settings->mayDeliverTo('recipients')) {
            $this->activity->record($lead, 'LeadCaptured', self::ACTOR, LeadActivity::SKIPPED, [
                'reason' => 'not production: recipients from the database are ignored (set LEAD_NOTIFICATION_RECIPIENTS or LEAD_DELIVERY=always)',
            ]);

            return;
        }

        try {
            $sent = $this->send($recipients, $event);
        } catch (Throwable $e) {
            Log::error('Lead notification failed', ['lead' => $lead->id, 'error' => $e->getMessage()]);
            $sent = false;
        }

        $this->activity->record($lead, 'LeadCaptured', self::ACTOR, $sent ? LeadActivity::SUCCEEDED : LeadActivity::FAILED, [
            'recipients' => count($recipients),
        ]);
    }

    /**
     * @param  list<string>  $recipients
     */
    private function send(array $recipients, LeadCaptured $event): bool
    {
        $lead = $event->lead;
        $data = ['lead' => $lead, 'site' => get_bloginfo('name'), 'adminUrl' => admin_url('admin.php?page=alma-leads&lead='.$lead->id)];

        $html = view('emails.lead.notification', $data)->render();
        $text = view('emails.lead.notification-text', $data)->render();

        $subject = sprintf(
            /* translators: 1: site name, 2: lead name or email */
            __('[%1$s] New lead: %2$s', 'alma'),
            get_bloginfo('name'),
            $lead->name !== '' ? $lead->name : $lead->email,
        );

        $headers = ['Content-Type: text/html; charset=UTF-8'];

        if (is_email($lead->email)) {
            $headers[] = 'Reply-To: '.$this->mailbox($lead->name, $lead->email);
        }

        // A plain-text alternative for clients that do not render HTML. Scoped to this one send.
        $alternative = static function ($mailer) use ($text) {
            $mailer->AltBody = $text;
        };

        add_action('phpmailer_init', $alternative);

        try {
            return wp_mail($recipients, $subject, $html, $headers);
        } finally {
            remove_action('phpmailer_init', $alternative);
        }
    }

    private function mailbox(string $name, string $email): string
    {
        $name = trim(str_replace(['"', "\r", "\n"], '', $name));

        return $name === '' ? $email : sprintf('"%s" <%s>', $name, $email);
    }
}
