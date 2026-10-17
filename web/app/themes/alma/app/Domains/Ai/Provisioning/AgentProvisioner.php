<?php

namespace App\Domains\Ai\Provisioning;

use RuntimeException;
use WP_Application_Passwords;
use WP_Error;
use WP_User;

/**
 * The WordPress user Claude acts as, kept in line with config/ai.php in both directions.
 *
 * ensure() creates the user if missing, makes it an editor, then grants every capability group
 * whose flag is on and explicitly denies (add_cap($cap, false)) every group whose flag is off.
 * The explicit deny matters: an editor already has publish_pages from its role, so merely not
 * granting it would leave the agent able to publish. Because the deploy runs ensure() on every
 * release, turning a flag off revokes access on the next deploy instead of leaving a stale grant.
 *
 * ensure() never issues a credential. Deploy output is logged, and a password printed there is a
 * password leaked; credentials come from issue() (the command or Settings → AI Access), shown once.
 */
class AgentProvisioner
{
    public function login(): string
    {
        return (string) config('ai.agent.login', 'ai-agent');
    }

    public function user(): ?WP_User
    {
        return get_user_by('login', $this->login()) ?: null;
    }

    /**
     * @return array{user_id: int, created: bool, granted: list<string>, denied: list<string>}
     */
    public function ensure(): array
    {
        $user = $this->user();
        $created = false;

        if (! $user) {
            $id = wp_insert_user([
                'user_login' => $this->login(),
                'user_email' => $this->email(),
                'user_pass' => wp_generate_password(64, true, true),
                'display_name' => __('AI agent', 'alma'),
                'role' => (string) config('ai.agent.role', 'editor'),
            ]);

            if ($id instanceof WP_Error) {
                throw new RuntimeException('Could not create the AI agent user: '.$id->get_error_message());
            }

            $user = get_userdata((int) $id);
            $created = true;
        }

        if (! $user) {
            throw new RuntimeException('The AI agent user could not be loaded.');
        }

        $role = (string) config('ai.agent.role', 'editor');

        if ($user->roles !== [$role]) {
            $user->set_role($role);
        }

        $granted = [];
        $denied = [];

        foreach ($this->grants() as $group => $grant) {
            foreach ($grant['capabilities'] as $capability) {
                $user->add_cap($capability, $grant['enabled']);
            }

            $grant['enabled'] ? $granted[] = $group : $denied[] = $group;
        }

        return ['user_id' => $user->ID, 'created' => $created, 'granted' => $granted, 'denied' => $denied];
    }

    /**
     * @return array<string, array{enabled: bool, capabilities: list<string>}>
     */
    public function grants(): array
    {
        $grants = [];

        foreach ((array) config('ai.agent.grants', []) as $group => $grant) {
            $grants[(string) $group] = [
                'enabled' => (bool) ($grant['enabled'] ?? false),
                'capabilities' => array_values(array_map('strval', (array) ($grant['capabilities'] ?? []))),
            ];
        }

        return $grants;
    }

    /**
     * Mint a named Application Password for the agent. The plaintext exists only in the return
     * value: WordPress stores a hash.
     *
     * @return array{password: string, uuid: string, name: string}
     */
    public function issue(string $label): array
    {
        $user = $this->user() ?? throw new RuntimeException('Run ensure() first: the AI agent user does not exist.');
        $label = trim(sanitize_text_field($label));

        if ($label === '') {
            throw new RuntimeException('Give the credential a name (whose it is, e.g. "Ada, Claude Desktop").');
        }

        $created = WP_Application_Passwords::create_new_application_password($user->ID, ['name' => $label]);

        if ($created instanceof WP_Error) {
            throw new RuntimeException($created->get_error_message());
        }

        [$password, $item] = $created;

        return ['password' => (string) $password, 'uuid' => (string) $item['uuid'], 'name' => (string) $item['name']];
    }

    public function revoke(string $uuid): bool
    {
        $user = $this->user();

        if (! $user) {
            return false;
        }

        return WP_Application_Passwords::delete_application_password($user->ID, $uuid) === true;
    }

    /**
     * @return list<array{uuid: string, name: string, created: int|null, last_used: int|null, last_ip: string|null}>
     */
    public function credentials(): array
    {
        $user = $this->user();

        if (! $user) {
            return [];
        }

        return array_values(array_map(fn (array $item) => [
            'uuid' => (string) $item['uuid'],
            'name' => (string) $item['name'],
            'created' => isset($item['created']) ? (int) $item['created'] : null,
            'last_used' => isset($item['last_used']) ? (int) $item['last_used'] : null,
            'last_ip' => $item['last_ip'] ?? null,
        ], (array) WP_Application_Passwords::get_user_application_passwords($user->ID)));
    }

    private function email(): string
    {
        $configured = (string) config('ai.agent.email', '');

        if ($configured !== '' && is_email($configured)) {
            return $configured;
        }

        $host = (string) parse_url(home_url(), PHP_URL_HOST);

        // A placeholder address on the site's own domain; the agent never receives mail.
        return $this->login().'@'.($host !== '' && str_contains($host, '.') ? $host : 'example.invalid');
    }
}
