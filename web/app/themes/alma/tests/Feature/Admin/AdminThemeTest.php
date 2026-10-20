<?php

use App\Infrastructure\WordPress\Admin\AdminStyles;
use App\Infrastructure\WordPress\Admin\AdminTheme;
use App\Infrastructure\WordPress\Admin\Brand;
use Illuminate\Support\Facades\Vite;
use Tests\Support\WordPress\WordPress;

/**
 * Records what would be enqueued instead of reading the build, so these tests don't depend on
 * `npm run build` having run.
 */
function fakeAdminStyles(): object
{
    $styles = new class extends AdminStyles
    {
        /** @var array<string, array{entry: string, deps: list<string>}> */
        public array $enqueued = [];

        public function enqueue(string $handle, string $entry, array $dependencies = []): bool
        {
            $this->enqueued[$handle] = ['entry' => $entry, 'deps' => $dependencies];

            return true;
        }
    };

    app()->instance(AdminStyles::class, $styles);

    return $styles;
}

it('registers the skin, toolbar, login and footer hooks', function () {
    app(AdminTheme::class)->register();

    foreach (['admin_enqueue_scripts', 'login_enqueue_scripts', 'wp_enqueue_scripts', 'admin_bar_menu', 'admin_footer', 'wp_footer', 'login_headerurl', 'login_headertext', 'admin_footer_text', 'update_footer', 'acf/settings/show_admin'] as $hook) {
        expect(has_filter($hook))->toBeTrue("{$hook} is not hooked");
    }
});

it('registers nothing when ADMIN_THEME is false', function () {
    config(['admin.theme' => false]);
    $before = WordPress::$filters;

    app(AdminTheme::class)->register();

    expect(WordPress::$filters)->toBe($before);
});

it('enqueues each sheet after the core handles it overrides', function () {
    $styles = fakeAdminStyles();
    $theme = app(AdminTheme::class);

    $theme->enqueueSkin();
    $theme->enqueueLogin();

    expect($styles->enqueued['alma-admin-skin'])->toBe(['entry' => AdminTheme::SKIN, 'deps' => ['wp-admin', 'colors']])
        ->and($styles->enqueued['alma-admin-login'])->toBe(['entry' => AdminTheme::LOGIN, 'deps' => ['login']]);
});

it('styles the front-end toolbar only for people who see it', function () {
    $styles = fakeAdminStyles();

    app(AdminTheme::class)->enqueueToolbar();
    expect($styles->enqueued)->toBe([]);

    WordPress::$conditionals['is_admin_bar_showing'] = true;
    app(AdminTheme::class)->enqueueToolbar();
    expect($styles->enqueued['alma-admin-toolbar']['deps'])->toBe(['admin-bar']);
});

it('fails soft without a build: no stylesheet, no error', function () {
    Vite::useBuildDirectory('no-such-build-'.uniqid());

    expect(app(AdminStyles::class)->url(AdminTheme::SKIN))->toBeNull()
        ->and(app(AdminStyles::class)->enqueue('alma-admin-skin', AdminTheme::SKIN, ['wp-admin']))->toBeFalse();

    app(AdminTheme::class)->enqueueSkin();
});

it('resolves a built sheet from the manifest', function () {
    $public = sys_get_temp_dir().'/alma-admin-public-'.uniqid();
    @mkdir("{$public}/build", 0755, true);
    file_put_contents("{$public}/build/manifest.json", json_encode([
        AdminTheme::SKIN => ['file' => 'assets/skin-abc123.css', 'src' => AdminTheme::SKIN, 'isEntry' => true],
    ]));
    app()->usePublicPath($public);

    expect(app(AdminStyles::class)->url(AdminTheme::SKIN))->toEndWith('/build/assets/skin-abc123.css');
});

it('links the login logo home and shows the brand as its text', function () {
    WordPress::$options['blogname'] = 'Acme & Co';
    $theme = app(AdminTheme::class);

    expect($theme->loginUrl())->toBe('https://example.test/')
        ->and($theme->loginText())->toContain('Acme &amp; Co')
        ->and($theme->loginText())->toContain('class="alma-brand-mark"')
        ->and($theme->loginText())->not->toContain('Acme & Co');
});

it('puts the brand on the toolbar and drops the WordPress logo menu', function () {
    $toolbar = new WP_Admin_Bar;
    $toolbar->add_node(['id' => 'wp-logo', 'title' => 'WordPress']);
    $toolbar->add_node(['id' => 'search', 'title' => 'Search']);
    $toolbar->add_node(['id' => 'site-name', 'title' => 'Alma', 'href' => 'https://example.test/wp/wp-admin/']);

    app(AdminTheme::class)->brandToolbar($toolbar);

    expect($toolbar->get_node('wp-logo'))->toBeNull()
        ->and($toolbar->get_node('search'))->toBeNull()
        ->and($toolbar->get_node('site-name')->title)->toContain('alma-brand-mark')
        ->and($toolbar->get_node('site-name')->href)->toBe('https://example.test/wp/wp-admin/');
});

it('keeps one brand menu and the notifications bell, without core\'s menus that repeat the sidebar', function () {
    $toolbar = new WP_Admin_Bar;

    foreach (['comments', 'view-site', 'dashboard', 'themes', 'customize', 'site-name'] as $node) {
        $toolbar->add_node(['id' => $node, 'title' => $node]);
    }

    app(AdminTheme::class)->brandToolbar($toolbar);

    expect(array_keys($toolbar->nodes))->toEqualCanonicalizing(['site-name', 'alma-sub-dashboard', 'alma-sub-site', 'alma-notifications'])
        ->and($toolbar->get_node('alma-sub-site')->parent)->toBe('site-name')
        ->and($toolbar->get_node('alma-notifications')->parent)->toBe('top-secondary')
        ->and($toolbar->get_node('alma-notifications')->title)->toContain('id="alma-notif-badge"');
});

it('prints the notifications drawer in wp-admin, and on the front end only under the toolbar', function () {
    $theme = app(AdminTheme::class);

    ob_start();
    $theme->renderNotifications();
    $admin = (string) ob_get_clean();

    ob_start();
    $theme->renderSiteNotifications();
    $front = (string) ob_get_clean();

    expect($admin)->toContain('id="alma-notif-drawer"')
        ->and($admin)->toContain('alma_dismissed_notices_v1')
        ->and($admin)->toContain('"dismiss":"Dismiss notification"')
        ->and($front)->toBe('');
});

it('initialises the toolbar on Acorn-routed pages that skipped template_redirect', function () {
    unset($GLOBALS['wp_admin_bar']);
    WordPress::$conditionals['is_admin_bar_showing'] = true;

    app(AdminTheme::class)->ensureToolbarInitialized();

    expect($GLOBALS['wp_admin_bar'] ?? null)->toBeInstanceOf(WP_Admin_Bar::class);
    unset($GLOBALS['wp_admin_bar']);
});

it('shows the release in the footer', function () {
    config(['alma.version' => 'v-20261018-v1']);

    expect(app(AdminTheme::class)->footerVersion())->toContain('Release v-20261018-v1');
});

it('hides ACF\'s field-group screens in production unless told otherwise', function (string $environment, ?bool $setting, bool $shown) {
    WordPress::$environment = $environment;
    config(['admin.acf_ui' => $setting]);

    expect(app(AdminTheme::class)->showAcfAdmin())->toBe($shown);
})->with([
    'production default' => ['production', null, false],
    'local default' => ['local', null, true],
    'forced on' => ['production', true, true],
    'forced off' => ['local', false, false],
]);

describe('the brand', function () {
    it('uses the site name and its initial when no mark file exists', function () {
        WordPress::$options['blogname'] = 'nimbus';
        config(['admin.brand.mark' => 'resources/images/brand/does-not-exist.svg']);

        $brand = app(Brand::class);

        expect($brand->name())->toBe('nimbus')
            ->and($brand->initial())->toBe('N')
            ->and($brand->markUrl())->toBeNull()
            ->and($brand->markHtml())->toContain('>N</span>');
    });

    it('prefers the configured name', function () {
        config(['admin.brand.name' => 'Nimbus Studio']);

        expect(app(Brand::class)->name())->toBe('Nimbus Studio');
    });

    it('uses a mark file inside the theme, never one outside it', function () {
        config(['admin.brand.mark' => 'style.css']);
        expect(app(Brand::class)->markUrl())->toBe('https://example.test/app/themes/alma/style.css')
            ->and(app(Brand::class)->markHtml())->toContain('has-image');

        config(['admin.brand.mark' => '../../../wp-config.php']);
        expect(app(Brand::class)->markUrl())->toBeNull();
    });
});
