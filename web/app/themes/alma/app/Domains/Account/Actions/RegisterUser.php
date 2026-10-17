<?php

namespace App\Domains\Account\Actions;

use App\Domains\Account\Data\RegistrationData;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use WP_User;

/**
 * Create a WordPress user from the registration form.
 */
class RegisterUser
{
    public function execute(RegistrationData $data): WP_User
    {
        if (! get_option('users_can_register')) {
            throw new RuntimeException('Registration is disabled (Settings → General → Membership).');
        }

        if (email_exists($data->email)) {
            // Same wording as a validation failure on any field: never confirm an address is taken.
            throw ValidationException::withMessages([
                'email' => __('We could not create an account with these details.', 'alma'),
            ]);
        }

        $id = wp_insert_user([
            'user_login' => $this->uniqueLogin($data->email),
            'user_email' => $data->email,
            'user_pass' => $data->password,
            'display_name' => $data->name,
            'first_name' => $data->name,
            'role' => get_option('default_role', 'subscriber') ?: 'subscriber',
        ]);

        if (is_wp_error($id)) {
            throw ValidationException::withMessages(['email' => $id->get_error_message()]);
        }

        // Core's own registration hook: sends the admin (and user) notification emails.
        do_action('register_new_user', $id);

        return get_userdata($id);
    }

    /**
     * A login name from the email's local part: "Jane.Doe+news@x.com" becomes "jane.doe", then
     * "jane.doe-2" and so on if it is taken.
     */
    protected function uniqueLogin(string $email): string
    {
        $local = preg_replace('/\+.*$/', '', strstr($email, '@', true) ?: $email);
        $base = trim(sanitize_user(strtolower($local), true), '.-_') ?: 'user';
        $login = $base;

        for ($i = 2; username_exists($login); $i++) {
            $login = "{$base}-{$i}";
        }

        return $login;
    }
}
