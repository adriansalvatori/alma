<?php

namespace App\Domains\Commerce\Services;

use App\Domains\Commerce\Contracts\CookieJar;

/**
 * A shopper's favorite products.
 *
 * Logged-in users keep the list in user meta. Guests keep it in a cookie of the form
 * "12,40,7.<hmac>": the signature is checked before a single ID is trusted, IDs must be positive
 * integers, and the list is capped, so a hand-edited cookie reads as an empty list rather than
 * as data. When a guest logs in, their cookie list is merged into their account on next read.
 */
class FavoritesService
{
    public const META_KEY = 'alma_favorites';

    /** @var list<int>|null */
    private ?array $guestFavorites = null;

    public function __construct(private readonly CookieJar $cookies) {}

    /**
     * @return list<int>
     */
    public function all(): array
    {
        $userId = get_current_user_id();

        if ($userId === 0) {
            return $this->guestFavorites();
        }

        $saved = $this->normalize((array) get_user_meta($userId, self::META_KEY, true));
        $guest = $this->guestFavorites();

        if ($guest === []) {
            return $saved;
        }

        // First read after logging in: move the guest list onto the account.
        $merged = $this->normalize([...$saved, ...$guest]);
        update_user_meta($userId, self::META_KEY, $merged);
        $this->cookies->forget($this->cookieName());
        $this->guestFavorites = [];

        return $merged;
    }

    public function has(int $productId): bool
    {
        return in_array($productId, $this->all(), true);
    }

    public function add(int $productId): void
    {
        if ($productId > 0 && ! $this->has($productId)) {
            $this->save([...$this->all(), $productId]);
        }
    }

    public function remove(int $productId): void
    {
        $this->save(array_values(array_filter($this->all(), fn (int $id) => $id !== $productId)));
    }

    /**
     * @return bool Whether the product is a favorite afterwards.
     */
    public function toggle(int $productId): bool
    {
        if ($this->has($productId)) {
            $this->remove($productId);

            return false;
        }

        $this->add($productId);

        return $this->has($productId);
    }

    /**
     * @param  array<int|string, mixed>  $ids
     */
    private function save(array $ids): void
    {
        $ids = $this->normalize($ids);
        $userId = get_current_user_id();

        if ($userId !== 0) {
            update_user_meta($userId, self::META_KEY, $ids);

            return;
        }

        $this->guestFavorites = $ids;

        if ($ids === []) {
            $this->cookies->forget($this->cookieName());

            return;
        }

        $payload = implode(',', $ids);
        $this->cookies->set(
            $this->cookieName(),
            $payload.'.'.$this->sign($payload),
            (int) config('commerce.favorites.lifetime_days', 30) * 24 * 60,
        );
    }

    /**
     * @return list<int>
     */
    private function guestFavorites(): array
    {
        if ($this->guestFavorites !== null) {
            return $this->guestFavorites;
        }

        $raw = (string) $this->cookies->get($this->cookieName());
        $dot = strrpos($raw, '.');

        if ($raw === '' || $dot === false || strlen($raw) > 2048) {
            return $this->guestFavorites = [];
        }

        $payload = substr($raw, 0, $dot);

        if (! hash_equals($this->sign($payload), substr($raw, $dot + 1)) || ! preg_match('/^\d+(,\d+)*$/', $payload)) {
            return $this->guestFavorites = [];
        }

        return $this->guestFavorites = $this->normalize(explode(',', $payload));
    }

    /**
     * Positive, unique integers, oldest first, capped at the configured maximum.
     *
     * @param  array<int|string, mixed>  $ids
     * @return list<int>
     */
    private function normalize(array $ids): array
    {
        $ids = array_values(array_unique(array_filter(
            array_map(fn ($id) => is_numeric($id) ? (int) $id : 0, $ids),
            fn (int $id) => $id > 0,
        )));

        return array_slice($ids, -max(1, (int) config('commerce.favorites.max_items', 50)));
    }

    private function sign(string $payload): string
    {
        return hash_hmac('sha256', $payload, 'alma-favorites|'.config('app.key'));
    }

    private function cookieName(): string
    {
        return (string) config('commerce.favorites.cookie', 'alma_favorites');
    }
}
