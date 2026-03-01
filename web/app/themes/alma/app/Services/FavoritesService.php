<?php

namespace App\Services;

use Illuminate\Support\Facades\Cookie;

class FavoritesService
{
    const COOKIE_NAME = 'alma_guest_favorites';

    public static function getFavorites(): array
    {
        if (is_user_logged_in()) {
            $favorites = get_user_meta(get_current_user_id(), 'alma_favorites', true);
            return is_array($favorites) ? $favorites : [];
        }

        $favorites = Cookie::get(self::COOKIE_NAME);
        if ($favorites) {
            return json_decode($favorites, true) ?? [];
        }

        return [];
    }

    public static function addFavorite(int $productId): void
    {
        $favorites = self::getFavorites();

        if (!in_array($productId, $favorites)) {
            $favorites[] = $productId;
            self::saveFavorites($favorites);
        }
    }

    public static function removeFavorite(int $productId): void
    {
        $favorites = self::getFavorites();

        $favorites = array_filter($favorites, function ($id) use ($productId) {
            return $id != $productId;
        });

        self::saveFavorites(array_values($favorites));
    }

    public static function toggleFavorite(int $productId): bool
    {
        $favorites = self::getFavorites();

        if (in_array($productId, $favorites)) {
            self::removeFavorite($productId);
            return false;
        } else {
            self::addFavorite($productId);
            return true;
        }
    }

    public static function isFavorite(int $productId): bool
    {
        $favorites = self::getFavorites();
        return in_array($productId, $favorites);
    }

    private static function saveFavorites(array $favorites): void
    {
        if (is_user_logged_in()) {
            update_user_meta(get_current_user_id(), 'alma_favorites', $favorites);
        } else {
            Cookie::queue(self::COOKIE_NAME, json_encode($favorites), 60 * 24 * 30); // 30 days
        }
    }
}
