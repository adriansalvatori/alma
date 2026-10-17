<?php

use App\Domains\Ai\Abilities\Ability;
use App\Domains\Ai\AiServiceProvider;
use App\Domains\Ai\Hooks\RegisterAbilities;
use Tests\Support\WordPress\WordPress;

beforeEach(function () {
    do_action('wp_abilities_api_categories_init');
    do_action('wp_abilities_api_init');
});

it('loads the module when the Abilities API exists', function () {
    expect(app()->providerIsLoaded(AiServiceProvider::class))->toBeTrue();
});

it('registers every configured ability with schemas, callbacks and MCP exposure', function () {
    $registered = WordPress::$options['__abilities'];

    expect(array_keys($registered))->toHaveCount(count(config('ai.abilities')));

    foreach ($registered as $name => $args) {
        expect($name)->toStartWith('alma/')
            ->and($args['label'])->not->toBe('')
            ->and($args['description'])->not->toBe('')
            ->and(WordPress::$options['__ability_categories'])->toHaveKey($args['category'])
            ->and($args['input_schema']['type'])->toBe('object')
            ->and($args['execute_callback'])->toBeCallable()
            ->and($args['permission_callback'])->toBeCallable()
            ->and($args['meta']['show_in_rest'])->toBeTrue()
            ->and($args['meta']['mcp'])->toBe(['public' => true, 'type' => 'tool'])
            ->and($args['meta']['annotations'])->toHaveKeys(['readonly', 'destructive', 'idempotent']);
    }
});

it('adds the confirmation step to destructive abilities only', function () {
    foreach (WordPress::$options['__abilities'] as $name => $args) {
        $destructive = $args['meta']['annotations']['destructive'];
        $properties = (array) $args['input_schema']['properties'];

        expect(array_key_exists('confirmation_token', $properties))->toBe($destructive, $name);
    }

    expect(WordPress::$options['__abilities']['alma/delete-redirect']['meta']['annotations']['destructive'])->toBeTrue();
});

it('accepts a call with no input at all', function () {
    expect(ai_test_ability('alma/list-patterns')['input_schema']['default'])->toBe([]);
});

it('skips an ability that cannot be created instead of failing the rest', function () {
    config(['ai.abilities' => ['App\\Domains\\Ai\\Abilities\\DoesNotExist', ...config('ai.abilities')]]);

    $abilities = app(RegisterAbilities::class)->abilities();

    expect($abilities)->toHaveCount(count(config('ai.abilities')) - 1)
        ->and($abilities)->each->toBeInstanceOf(Ability::class);
});

it('leaves out an ability that is not available on this install', function () {
    $unavailable = new class extends Ability
    {
        public function name(): string
        {
            return 'alma/unavailable';
        }

        public function label(): string
        {
            return 'Unavailable';
        }

        public function description(): string
        {
            return 'Never registered.';
        }

        public function category(): string
        {
            return 'alma-site';
        }

        public function permission(array $input): bool
        {
            return true;
        }

        public function execute(array $input): mixed
        {
            return null;
        }

        public function available(): bool
        {
            return false;
        }
    };

    app()->instance($unavailable::class, $unavailable);
    config(['ai.abilities' => [$unavailable::class]]);

    expect(app(RegisterAbilities::class)->abilities())->toBe([]);
});
