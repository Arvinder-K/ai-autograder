<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Configuration extends Model
{
    protected $fillable = ['group', 'key', 'value', 'type', 'label', 'description', 'is_public'];

    protected function casts(): array
    {
        return ['is_public' => 'boolean'];
    }

    public static function getValue(string $group, string $key, mixed $default = null): mixed
    {
        $config = static::where('group', $group)->where('key', $key)->first();
        if (!$config) {
            return $default;
        }

        return match ($config->type) {
            'boolean' => (bool) $config->value,
            'integer' => (int) $config->value,
            'json' => json_decode($config->value, true),
            default => $config->value,
        };
    }
}
