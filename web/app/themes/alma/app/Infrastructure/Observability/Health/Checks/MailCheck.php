<?php

namespace App\Infrastructure\Observability\Health\Checks;

use App\Infrastructure\Mail\SmtpMailer;
use App\Infrastructure\Observability\Health\CheckResult;
use App\Infrastructure\Observability\Health\HealthCheck;

class MailCheck implements HealthCheck
{
    public function name(): string
    {
        return 'mail';
    }

    public function label(): string
    {
        return __('Outgoing email', 'alma');
    }

    public function run(): CheckResult
    {
        return SmtpMailer::configured()
            ? CheckResult::ok('Email is sent through SMTP.', ['host' => config('mail.wordpress.host')])
            : CheckResult::warning('Email is sent with PHP mail(), which shared hosts deliver poorly. Set MAIL_HOST to use SMTP.');
    }
}
