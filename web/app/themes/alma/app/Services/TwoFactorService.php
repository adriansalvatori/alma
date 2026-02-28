<?php

namespace App\Services;

use PragmaRX\Google2FA\Google2FA;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use App\Services\AuthService;
use WP_User;

class TwoFactorService
{
    protected Google2FA $google2fa;
    protected AuthService $authService;

    public function __construct(Google2FA $google2fa, AuthService $authService)
    {
        $this->google2fa = $google2fa;
        $this->authService = $authService;
    }

    /**
     * Generate a new 2FA secret.
     *
     * @return string
     */
    public function generateSecret(): string
    {
        return $this->google2fa->generateSecretKey();
    }

    /**
     * Get the SVG QR Code for the given secret.
     *
     * @param string $secret
     * @return string
     */
    public function getQrCode(string $secret): string
    {
        $user = wp_get_current_user();
        if (!$user->exists()) {
            return '';
        }

        $appName = get_bloginfo('name');
        $company = !empty($appName) ? $appName : 'Alma App';
        $email = $user->user_email;

        $g2faUrl = $this->google2fa->getQRCodeUrl(
            $company,
            $email,
            $secret
        );

        $renderer = new ImageRenderer(
            new RendererStyle(250),
            new SvgImageBackEnd()
        );

        $writer = new Writer($renderer);
        return $writer->writeString($g2faUrl);
    }

    /**
     * Verify a 2FA code against a secret.
     *
     * @param string $secret
     * @param string $code
     * @return bool
     */
    public function verify(string $secret, string $code): bool
    {
        return $this->google2fa->verifyKey($secret, $code);
    }

    /**
     * Enable 2FA for the current user.
     *
     * @param string $secret
     * @return void
     */
    public function enable(string $secret): void
    {
        $user = wp_get_current_user();
        if ($user->exists()) {
            update_user_meta($user->ID, 'two_factor_secret', encrypt($secret));
            update_user_meta($user->ID, 'two_factor_enabled', true);
        }
    }

    /**
     * Disable 2FA for the current user.
     *
     * @return void
     */
    public function disable(): void
    {
        $user = wp_get_current_user();
        if ($user->exists()) {
            delete_user_meta($user->ID, 'two_factor_secret');
            update_user_meta($user->ID, 'two_factor_enabled', false);
        }
    }
}
