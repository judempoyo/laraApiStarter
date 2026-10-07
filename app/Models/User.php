<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;
use Laravel\Sanctum\NewAccessToken;
use Spatie\Permission\Traits\HasRoles;

/**
 * @property string|null $two_factor_secret
 * @property \Illuminate\Support\Carbon|null $two_factor_confirmed_at
 *
 * To switch to Passport, replace `Laravel\Sanctum\HasApiTokens` with
 * `Laravel\Passport\HasApiTokens` and set AUTH_DRIVER=passport in .env.
 */
class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens, HasRoles;

    protected $guard_name = 'api';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'email_verified_at',
        'password',
        'password_updated_at',
        'partner_status',
        'status',
        'google_id',
        'provider',
        'provider_id',
        'avatar',
        'two_factor_secret',
        'two_factor_confirmed_at',
        'two_factor_recovery_codes',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at'       => 'datetime',
            'password'                => 'hashed',
            'password_updated_at'     => 'datetime',
            'status'                  => UserStatus::class,
            'two_factor_confirmed_at' => 'datetime',
        ];
    }

    // ─── Relationships ──────────────────────────────────────────────────────

    public function securityLogs(): HasMany
    {
        return $this->hasMany(UserSecurityLog::class);
    }

    public function apiKeys(): HasMany
    {
        return $this->hasMany(ApiKey::class);
    }

    public function preferences(): HasMany
    {
        return $this->hasMany(UserPreference::class);
    }

    public function webhooks(): HasMany
    {
        return $this->hasMany(\App\Models\Webhook::class);
    }

    public function media(): HasMany
    {
        return $this->hasMany(\App\Models\Media::class);
    }

    public function exports(): HasMany
    {
        return $this->hasMany(\App\Models\Export::class);
    }

    public function imports(): HasMany
    {
        return $this->hasMany(\App\Models\Import::class);
    }

    // ─── Token ─────────────────────────────────────────────────────────────

    /**
     * Create a new personal access token for the user.
     * Respects the expiration configured in config/sanctum.php.
     *
     * @param  string  $name
     * @param  array   $abilities
     * @param  \DateTimeInterface|null  $expiresAt
     * @return \Laravel\Sanctum\NewAccessToken
     */
    public function createToken(string $name, array $abilities = ['*'], ?\DateTimeInterface $expiresAt = null)
    {
        $expiration = config('sanctum.expiration');

        $token = $this->tokens()->create([
            'name'       => $name,
            'token'      => hash('sha256', $plainTextToken = Str::random(150)),
            'abilities'  => $abilities,
            'expires_at' => $expiresAt ?? ($expiration ? now()->addMinutes($expiration) : null),
        ]);

        return new NewAccessToken($token, $token->getKey() . '|' . $plainTextToken);
    }

    // ─── Accessors ──────────────────────────────────────────────────────────

    public function getAvatarUrlAttribute(): ?string
    {
        if (! $this->avatar) {
            return null;
        }

        if (str_starts_with($this->avatar, 'http')) {
            return $this->avatar;
        }

        return \Illuminate\Support\Facades\Storage::url($this->avatar);
    }

    public function hasTwoFactorEnabled(): bool
    {
        return $this->two_factor_confirmed_at !== null;
    }

    public function getRecoveryCodes(): array
    {
        if (empty($this->two_factor_recovery_codes)) {
            return [];
        }

        try {
            $decrypted = decrypt($this->two_factor_recovery_codes);

            return is_array($decrypted) ? $decrypted : (json_decode($decrypted, true) ?? []);
        } catch (\Throwable) {
            return [];
        }
    }

    public function replaceRecoveryCodes(array $codes): void
    {
        $this->update([
            'two_factor_recovery_codes' => encrypt($codes),
        ]);
    }

    public function useRecoveryCode(string $code): bool
    {
        $codes = $this->getRecoveryCodes();
        $cleanedCode = strtoupper(trim(str_replace([' ', '-'], '', $code)));

        foreach ($codes as $index => $storedCode) {
            $cleanedStored = strtoupper(trim(str_replace([' ', '-'], '', $storedCode)));
            if (hash_equals($cleanedStored, $cleanedCode)) {
                unset($codes[$index]);
                $this->replaceRecoveryCodes(array_values($codes));

                return true;
            }
        }

        return false;
    }

    public function resetTwoFactor(): void
    {
        $this->update([
            'two_factor_secret'         => null,
            'two_factor_confirmed_at'   => null,
            'two_factor_recovery_codes' => null,
        ]);
    }

    // ─── Business Logic ─────────────────────────────────────────────────────

    public function determineAccountType(): string
    {
        if ($this->hasRole('admin')) {
            return UserRole::ADMIN->value;
        }

        return UserRole::USER->value;
    }

    // ─── Notifications ──────────────────────────────────────────────────────

    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new \App\Notifications\QueuedVerifyEmail());
    }

    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new \App\Notifications\QueuedResetPassword($token));
    }
}
