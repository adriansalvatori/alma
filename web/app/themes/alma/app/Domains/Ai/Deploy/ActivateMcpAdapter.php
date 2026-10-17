<?php

namespace App\Domains\Ai\Deploy;

use App\Infrastructure\Deploy\DeployTask;
use RuntimeException;

/**
 * Activate the MCP adapter plugin when Composer installed it but nobody switched it on, so a fresh
 * environment serves the MCP endpoint without a trip to the Plugins screen. Leaves it alone when
 * it is missing or already active.
 *
 * It refuses to activate an adapter that cannot load. Installed by Composer, the plugin's classes
 * come from the project's autoloader, and the plugin only uses that one when WP_MCP_AUTOLOAD is
 * defined as false. Without the constant it looks for its own vendor/ folder, does not find it,
 * and raises a "called incorrectly" notice on every request, which Acorn turns into a 500 in
 * development. Better a failed (non-fatal) deploy step that says why than a broken site.
 */
class ActivateMcpAdapter implements DeployTask
{
    public const PLUGIN = 'mcp-adapter/mcp-adapter.php';

    public function label(): string
    {
        return 'Activate the MCP adapter plugin';
    }

    public function priority(): int
    {
        return 55;
    }

    public function fatal(): bool
    {
        return false;
    }

    public function run(): void
    {
        if (! function_exists('activate_plugin') && is_file(ABSPATH.'wp-admin/includes/plugin.php')) {
            require_once ABSPATH.'wp-admin/includes/plugin.php';
        }

        if (! is_file(WP_PLUGIN_DIR.'/'.self::PLUGIN) || is_plugin_active(self::PLUGIN)) {
            return;
        }

        if (! self::loadable()) {
            throw new RuntimeException('Not activated: define WP_MCP_AUTOLOAD as false in config/application.php (Composer autoloads the adapter), then deploy again.');
        }

        $result = activate_plugin(self::PLUGIN);

        if (is_wp_error($result)) {
            throw new RuntimeException($result->get_error_message());
        }
    }

    /**
     * Whether the adapter's classes will be found once it is active: through the project's
     * autoloader (WP_MCP_AUTOLOAD false), or through a vendor folder bundled with the plugin.
     */
    public static function loadable(): bool
    {
        if (defined('WP_MCP_AUTOLOAD') && constant('WP_MCP_AUTOLOAD') === false) {
            return class_exists('WP\MCP\Core\McpAdapter');
        }

        return is_file(WP_PLUGIN_DIR.'/'.dirname(self::PLUGIN).'/vendor/autoload_packages.php');
    }
}
