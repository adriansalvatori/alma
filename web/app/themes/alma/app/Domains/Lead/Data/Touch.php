<?php

namespace App\Domains\Lead\Data;

/**
 * One marketing touch: where a visit came from. Values are untrusted (they come from the URL
 * and the Referer header), so they are stripped of tags and length-capped on the way in.
 */
readonly class Touch
{
    /** Query parameters that make a visit a touch. */
    public const PARAMETERS = [
        'utm_source' => 'source',
        'utm_medium' => 'medium',
        'utm_campaign' => 'campaign',
        'utm_term' => 'term',
        'utm_content' => 'content',
        'gclid' => 'gclid',
        'fbclid' => 'fbclid',
        'msclkid' => 'msclkid',
    ];

    private const MAX = 255;

    private const MAX_URL = 500;

    public function __construct(
        public ?string $source = null,
        public ?string $medium = null,
        public ?string $campaign = null,
        public ?string $term = null,
        public ?string $content = null,
        public ?string $gclid = null,
        public ?string $fbclid = null,
        public ?string $msclkid = null,
        public ?string $referrer = null,
        public ?string $landingPage = null,
        public ?string $capturedAt = null,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            source: self::clean($data['source'] ?? null),
            medium: self::clean($data['medium'] ?? null),
            campaign: self::clean($data['campaign'] ?? null),
            term: self::clean($data['term'] ?? null),
            content: self::clean($data['content'] ?? null),
            gclid: self::clean($data['gclid'] ?? null),
            fbclid: self::clean($data['fbclid'] ?? null),
            msclkid: self::clean($data['msclkid'] ?? null),
            referrer: self::clean($data['referrer'] ?? null, self::MAX_URL),
            landingPage: self::clean($data['landing_page'] ?? null, self::MAX_URL),
            capturedAt: self::clean($data['captured_at'] ?? null),
        );
    }

    /**
     * @return array<string, string|null>
     */
    public function toArray(): array
    {
        return [
            'source' => $this->source,
            'medium' => $this->medium,
            'campaign' => $this->campaign,
            'term' => $this->term,
            'content' => $this->content,
            'gclid' => $this->gclid,
            'fbclid' => $this->fbclid,
            'msclkid' => $this->msclkid,
            'referrer' => $this->referrer,
            'landing_page' => $this->landingPage,
            'captured_at' => $this->capturedAt,
        ];
    }

    public static function clean(mixed $value, int $max = self::MAX): ?string
    {
        if (! is_scalar($value)) {
            return null;
        }

        $value = trim(preg_replace('/[\x00-\x1F\x7F]+/', '', strip_tags((string) $value)) ?? '');

        return $value === '' ? null : mb_substr($value, 0, $max);
    }
}
