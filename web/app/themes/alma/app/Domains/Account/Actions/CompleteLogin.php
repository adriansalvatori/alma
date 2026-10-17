<?php

namespace App\Domains\Account\Actions;

use WP_User;

/**
 * Issue the WordPress auth cookie. The only place in the module that logs anyone in, and it is
 * only reached after every factor the user has enabled has been checked.
 */
class CompleteLogin
{
    public function execute(WP_User $user, bool $remember = false): void
    {
        wp_set_auth_cookie($user->ID, $remember, is_ssl());
        wp_set_current_user($user->ID);

        do_action('wp_login', $user->user_login, $user);
    }
}
