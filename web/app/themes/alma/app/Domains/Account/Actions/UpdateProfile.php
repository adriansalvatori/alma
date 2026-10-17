<?php

namespace App\Domains\Account\Actions;

use Illuminate\Validation\ValidationException;
use WP_User;

/**
 * Change the current user's display name and email.
 *
 * A new email address needs the current password: the email is where password resets go, so
 * changing it from a hijacked session would hand the account over for good.
 */
class UpdateProfile
{
    public function execute(WP_User $user, string $name, string $email, #[\SensitiveParameter] string $currentPassword = ''): void
    {
        $email = strtolower(trim($email));
        $changes = ['ID' => $user->ID, 'display_name' => trim($name), 'first_name' => trim($name)];

        if ($email !== strtolower($user->user_email)) {
            if (! wp_check_password($currentPassword, $user->user_pass, $user->ID)) {
                throw ValidationException::withMessages([
                    'currentPassword' => __('Your current password is required to change your email.', 'alma'),
                ]);
            }

            $owner = email_exists($email);

            if ($owner && $owner !== $user->ID) {
                throw ValidationException::withMessages([
                    'email' => __('That email address cannot be used.', 'alma'),
                ]);
            }

            $changes['user_email'] = $email;
        }

        $result = wp_update_user($changes);

        if (is_wp_error($result)) {
            throw ValidationException::withMessages(['email' => $result->get_error_message()]);
        }
    }
}
