<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ApiToken extends Model
{
    protected $fillable = ['name', 'token_hash', 'abilities', 'last_used_at', 'expires_at', 'is_active'];

    protected $casts = [
        'abilities' => 'array',
        'last_used_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public static function issue(string $name, array $abilities = ['*'], ?\DateTimeInterface $expiresAt = null): array
    {
        $plain = 'jv_' . Str::random(48);

        $token = static::create([
            'name' => $name,
            'token_hash' => hash('sha256', $plain),
            'abilities' => $abilities,
            'expires_at' => $expiresAt,
            'is_active' => true,
        ]);

        return [$token, $plain];
    }

    public function allows(string $ability): bool
    {
        $abilities = $this->abilities ?: [];

        return in_array('*', $abilities, true) || in_array($ability, $abilities, true);
    }
}
