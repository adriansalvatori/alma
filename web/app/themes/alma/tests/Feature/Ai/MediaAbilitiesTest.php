<?php

use Tests\Support\WordPress\WordPress;

// A real 1x1 PNG, so type detection reads actual contents.
const AI_TEST_PNG = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg==';

beforeEach(function () {
    $this->actingAsWordPressUser(['role' => 'editor']);
});

it('uploads base64 data with alt text', function () {
    $result = ai_test_run_ability('alma/upload-media', ['data' => AI_TEST_PNG, 'filename' => 'pixel.png', 'alt' => 'A single pixel']);

    expect($result['mime'])->toBe('image/png')
        ->and($result['id'])->toBeInt()
        ->and(get_post($result['id'])->post_type)->toBe('attachment')
        ->and(get_post_meta($result['id'], '_wp_attachment_image_alt', true))->toBe('A single pixel');
});

it('accepts a data: URI prefix', function () {
    expect(ai_test_run_ability('alma/upload-media', ['data' => 'data:image/png;base64,'.AI_TEST_PNG, 'filename' => 'pixel.png']))
        ->toHaveKey('id');
});

it('fetches from a URL', function () {
    WordPress::$options['__downloads']['https://cdn.example.com/pixel.png'] = base64_decode(AI_TEST_PNG);

    expect(ai_test_run_ability('alma/upload-media', ['url' => 'https://cdn.example.com/pixel.png'])['mime'])->toBe('image/png');
});

it('refuses a file whose contents do not match an allowed type', function (string $filename, string $contents) {
    $result = ai_test_run_ability('alma/upload-media', ['data' => base64_encode($contents), 'filename' => $filename]);

    expect($result)->toBeInstanceOf(WP_Error::class)
        ->and(get_posts(['post_type' => 'attachment', 'post_status' => 'any']))->toBe([]);
})->with([
    'svg' => ['logo.svg', '<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script></svg>'],
    'php renamed to png' => ['shell.png', '<?php echo "hi";'],
]);

it('refuses a file over the size limit', function () {
    config(['ai.media.max_bytes' => 10]);

    expect(ai_test_run_ability('alma/upload-media', ['data' => AI_TEST_PNG, 'filename' => 'pixel.png']))->toBeInstanceOf(WP_Error::class);
});

it('needs exactly one source', function (array $input) {
    expect(ai_test_run_ability('alma/upload-media', $input))->toBeInstanceOf(WP_Error::class);
})->with([
    'neither' => [[]],
    'both' => [['url' => 'https://example.com/a.png', 'data' => AI_TEST_PNG, 'filename' => 'a.png']],
    'not http' => [['url' => 'file:///etc/passwd']],
    'data without filename' => [['data' => AI_TEST_PNG]],
    'not base64' => [['data' => '***', 'filename' => 'a.png']],
]);

it('lists media, newest first, filtered by type', function () {
    ai_test_run_ability('alma/upload-media', ['data' => AI_TEST_PNG, 'filename' => 'one.png']);
    ai_test_run_ability('alma/upload-media', ['data' => AI_TEST_PNG, 'filename' => 'two.png', 'alt' => 'Two']);

    $images = ai_test_run_ability('alma/list-media', ['type' => 'image']);
    $pdfs = ai_test_run_ability('alma/list-media', ['type' => 'application/pdf']);

    expect(array_column($images, 'title'))->toBe(['two', 'one'])
        ->and($images[0]['alt'])->toBe('Two')
        ->and($pdfs)->toBe([]);
});
