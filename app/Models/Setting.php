<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * Simple key/value store for editable platform settings (e.g. commission rate).
 */
final class Setting extends Model
{
    protected $fillable = [
        'key',
        'value',
    ];

    /**
     * Read a setting value (cached), falling back to $default.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $value = Cache::rememberForever("setting:{$key}", static function () use ($key) {
            return self::query()->where('key', $key)->value('value');
        });

        return $value ?? $default;
    }

    /**
     * Persist a setting value and refresh the cache.
     */
    public static function set(string $key, mixed $value): void
    {
        self::query()->updateOrCreate(['key' => $key], ['value' => (string) $value]);
        Cache::forget("setting:{$key}");
        Cache::rememberForever("setting:{$key}", static fn () => (string) $value);
    }
}
