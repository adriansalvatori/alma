<?php

namespace App\Domains\Account\Services;

use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Contracts\Encryption\DecryptException;
use PragmaRX\Google2FA\Google2FA;
use WP_User;

/**
 * Time-based one-time passwords (TOTP), per user.
 *
 * Secrets are encrypted with the app key (APP_KEY), so a database dump alone does not hand out
 * second factors. Each accepted code records its timestep, and a code from that timestep or an
 * earlier one is refused: a code read over someone's shoulder cannot be used a second time.
 */
class TwoFactorService
{
    public const META_SECRET = 'alma_2fa_secret';

    public const META_PENDING_SECRET = 'alma_2fa_pending_secret';

    public const META_ENABLED = 'alma_2fa_enabled';

    public const META_LAST_TIMESTEP = 'alma_2fa_last_timestep';

    public function __construct(protected Google2FA $google2fa) {}

    public function isEnabled(int $userId): bool
    {
        return (bool) get_user_meta($userId, self::META_ENABLED, true)
            && $this->secret($userId, self::META_SECRET) !== null;
    }

    /**
     * Start setup: generate a secret and hold it until the user proves their app has it.
     */
    public function beginSetup(int $userId): string
    {
        $secret = $this->google2fa->generateSecretKey(32);

        update_user_meta($userId, self::META_PENDING_SECRET, encrypt($secret));

        return $secret;
    }

    public function pendingSecret(int $userId): ?string
    {
        return $this->secret($userId, self::META_PENDING_SECRET);
    }

    public function cancelSetup(int $userId): void
    {
        delete_user_meta($userId, self::META_PENDING_SECRET);
    }

    /**
     * Finish setup with a code from the user's app. Only now is 2FA switched on.
     */
    public function confirmSetup(int $userId, string $code): bool
    {
        $secret = $this->pendingSecret($userId);

        if ($secret === null || ($timestep = $this->check($secret, $code, null)) === false) {
            return false;
        }

        update_user_meta($userId, self::META_SECRET, encrypt($secret));
        update_user_meta($userId, self::META_LAST_TIMESTEP, $timestep);
        update_user_meta($userId, self::META_ENABLED, true);
        delete_user_meta($userId, self::META_PENDING_SECRET);

        return true;
    }

    /**
     * Check a code against the user's secret, refusing any code at or before the last accepted one.
     */
    public function verify(int $userId, string $code): bool
    {
        $secret = $this->secret($userId, self::META_SECRET);

        if ($secret === null) {
            return false;
        }

        $last = get_user_meta($userId, self::META_LAST_TIMESTEP, true);
        $timestep = $this->check($secret, $code, is_numeric($last) ? (int) $last : null);

        if ($timestep === false) {
            return false;
        }

        update_user_meta($userId, self::META_LAST_TIMESTEP, $timestep);

        return true;
    }

    public function disable(int $userId): void
    {
        foreach ([self::META_SECRET, self::META_PENDING_SECRET, self::META_ENABLED, self::META_LAST_TIMESTEP] as $key) {
            delete_user_meta($userId, $key);
        }
    }

    /**
     * An SVG QR code for an authenticator app, labelled with the site name and the user's email.
     */
    public function qrCodeSvg(WP_User $user, string $secret): string
    {
        $url = $this->google2fa->getQRCodeUrl(
            get_bloginfo('name') ?: 'WordPress',
            $user->user_email,
            $secret,
        );

        return (new Writer(new ImageRenderer(new RendererStyle(192, 1), new SvgImageBackEnd)))->writeString($url);
    }

    /**
     * The accepted timestep, or false. One step of drift either side is allowed for clock skew.
     */
    protected function check(string $secret, string $code, ?int $after): int|false
    {
        $code = preg_replace('/\s+/', '', $code);

        if (! preg_match('/^\d{6}$/', $code)) {
            return false;
        }

        // A null "after" makes google2fa return true instead of the timestep, so start from 0.
        $result = $this->google2fa->verifyKeyNewer($secret, $code, $after ?? 0, 1);

        return is_int($result) ? $result : false;
    }

    protected function secret(int $userId, string $key): ?string
    {
        $stored = get_user_meta($userId, $key, true);

        if (! is_string($stored) || $stored === '') {
            return null;
        }

        try {
            return decrypt($stored);
        } catch (DecryptException) {
            // APP_KEY changed since the secret was stored. Treat it as missing rather than locking
            // the user out with an exception; an admin can reset 2FA for them.
            return null;
        }
    }
}
