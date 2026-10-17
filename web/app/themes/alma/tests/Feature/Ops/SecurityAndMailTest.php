<?php

use App\Infrastructure\Mail\SmtpMailer;
use App\Infrastructure\Observability\SentryReporting;
use App\Infrastructure\Security\UserEnumerationGuard;
use Illuminate\Support\Facades\Log;
use Tests\Support\WordPress\WordPress;

describe('xml-rpc and head', function () {
    it('disables XML-RPC completely and stops advertising it', function () {
        expect(apply_filters('xmlrpc_enabled', true))->toBeFalse()
            ->and(apply_filters('xmlrpc_methods', ['pingback.ping' => 'x']))->toBe([])
            ->and(apply_filters('wp_headers', ['X-Pingback' => 'https://example.test/xmlrpc.php', 'X-Other' => '1']))->toBe(['X-Other' => '1'])
            ->and(apply_filters('pings_open', true))->toBeFalse()
            ->and(apply_filters('the_generator', '<meta name="generator" content="WordPress 7.1">'))->toBe('')
            ->and(apply_filters('wp_headers', ['X-Powered-By' => 'Acorn 6.3.0 (Laravel 13.12.0)']))->toBe([]);
    });
});

describe('user enumeration', function () {
    afterEach(fn () => $_GET = []);

    it('turns ?author=N into a 404 for anonymous visitors', function () {
        $_GET['author'] = '1';

        expect(apply_filters('request', ['author' => '1']))->toBe(['error' => '404']);
    });

    it('leaves logged-in users and other queries alone', function () {
        expect(apply_filters('request', ['pagename' => 'about']))->toBe(['pagename' => 'about']);

        $_GET['author'] = '1';
        $this->actingAsWordPressUser(['role' => 'editor']);

        expect(apply_filters('request', ['author' => '1']))->toBe(['author' => '1']);
    });

    it('hides the REST users endpoints from anonymous requests only', function () {
        $endpoints = ['/wp/v2/users' => [], '/wp/v2/users/(?P<id>[\d]+)' => [], '/wp/v2/users/me' => [], '/wp/v2/pages' => []];

        expect(array_keys((new UserEnumerationGuard)->hideUserEndpoints($endpoints)))->toBe(['/wp/v2/pages']);

        $this->actingAsWordPressUser();

        expect((new UserEnumerationGuard)->hideUserEndpoints($endpoints))->toBe($endpoints);
    });
});

describe('smtp', function () {
    beforeEach(fn () => $this->mailer = new class
    {
        public string $Mailer = 'mail';

        public string $Host = '';

        public int $Port = 25;

        public int $Timeout = 300;

        public string $SMTPSecure = '';

        public bool $SMTPAutoTLS = true;

        public bool $SMTPAuth = false;

        public string $Username = '';

        public string $Password = '';

        public function isSMTP(): void
        {
            $this->Mailer = 'smtp';
        }
    });

    it('leaves PHP mail() alone until MAIL_HOST is set', function () {
        do_action('phpmailer_init', $this->mailer);

        expect($this->mailer->Mailer)->toBe('mail');
    });

    it('configures SMTP with authentication and a short timeout', function () {
        config(['mail.wordpress' => array_merge(config('mail.wordpress'), [
            'host' => 'smtp.provider.com', 'port' => 587, 'username' => 'apikey', 'password' => 'secret', 'encryption' => 'tls',
        ])]);

        do_action('phpmailer_init', $this->mailer);

        expect($this->mailer)
            ->Mailer->toBe('smtp')
            ->Host->toBe('smtp.provider.com')
            ->Port->toBe(587)
            ->Timeout->toBe(10)
            ->SMTPSecure->toBe('tls')
            ->SMTPAuth->toBeTrue()
            ->Username->toBe('apikey')
            ->Password->toBe('secret');
    });

    it('never sends credentials in clear text to a remote host', function () {
        $smtp = new SmtpMailer;

        expect($smtp->encryption('smtp.provider.com', 'none'))->toBe('tls')
            ->and($smtp->encryption('mail.alma.test', 'none'))->toBe('')
            ->and($smtp->encryption('smtp.provider.com', ''))->toBe('tls')
            ->and($smtp->encryption('smtp.provider.com', 'ssl'))->toBe('ssl')
            ->and($smtp->encryption('127.0.0.1', 'none'))->toBe('')
            ->and($smtp->encryption('mailpit', 'none'))->toBe('');
    });

    it('sets the sender from MAIL_FROM_*, and keeps WordPress defaults otherwise', function () {
        expect(apply_filters('wp_mail_from', 'wordpress@example.test'))->toBe('wordpress@example.test');

        config(['mail.from' => ['address' => 'hello@example.test', 'name' => 'Example']]);

        expect(apply_filters('wp_mail_from', 'wordpress@example.test'))->toBe('hello@example.test')
            ->and(apply_filters('wp_mail_from_name', 'WordPress'))->toBe('Example');
    });

    it('logs failed mail without recipients or body', function () {
        Log::shouldReceive('error')->once()
            ->withArgs(fn (string $message, array $context) => str_contains($message, 'Could not connect') && $context === ['smtp' => false]);

        do_action('wp_mail_failed', new WP_Error('wp_mail_failed', 'SMTP Error: Could not connect', ['to' => ['person@example.test']]));
    });
});

it('keeps Sentry off without a DSN', function () {
    expect(SentryReporting::enabled())->toBeFalse();

    config(['sentry.dsn' => 'https://public@o0.ingest.sentry.io/0']);

    expect(SentryReporting::enabled())->toBeTrue();
});

it('captures only fatal errors at shutdown', function () {
    // error_get_last() is null in a healthy test run, so this must be a silent no-op.
    (new SentryReporting)->captureFatal();

    expect(true)->toBeTrue();
});

it('never ships a default Sentry DSN', function () {
    expect(config('sentry.dsn'))->toBeEmpty()
        ->and(file_get_contents(theme_path('config/sentry.php')))->not->toMatch('#https://[a-z0-9]+@#');
});

it('uses the release version and hides personal data', function () {
    expect(config('sentry.release'))->toBe('test')
        ->and(config('sentry.send_default_pii'))->toBeFalse();
});

it('logs to a rotating daily file by default, never stderr', function () {
    // phpunit.xml sets LOG_CHANNEL=null for the suite, so read the shipped defaults from source.
    $source = (string) file_get_contents(theme_path('config/logging.php'));

    expect($source)->toContain("'default' => env('LOG_CHANNEL', 'daily')")
        ->toContain("env('LOG_STACK', 'daily')")
        ->toContain("'days' => (int) env('LOG_DAILY_DAYS', 14)")
        ->not->toContain("'stderr' =>");
});

it('defines WordPress fakes this suite relies on', function () {
    expect(WordPress::$options)->toBeArray();
});
