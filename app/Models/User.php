<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\UserStatus;
use App\Notifications\QueuedResetPassword;
use App\Notifications\QueuedVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;
use Laravel\Sanctum\NewAccessToken;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    // api-starter:traits

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
        'status',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'password_updated_at' => 'datetime',
            'status' => UserStatus::class,
        ];
    }

    // ─── Relationships ──────────────────────────────────────────────────────

    // ─── Token ─────────────────────────────────────────────────────────────

    /**
     * Create a new personal access token for the user.
     * Respects the expiration configured in config/sanctum.php.
     *
     * @return NewAccessToken
     */
    public function createToken(string $name, array $abilities = ['*'], ?\DateTimeInterface $expiresAt = null)
    {
        $expiration = config('sanctum.expiration');

        $token = $this->tokens()->create([
            'name' => $name,
            'token' => hash('sha256', $plainTextToken = Str::random(150)),
            'abilities' => $abilities,
            'expires_at' => $expiresAt ?? ($expiration ? now()->addMinutes($expiration) : null),
        ]);

        return new NewAccessToken($token, $token->getKey().'|'.$plainTextToken);
    }

    // ─── Accessors ──────────────────────────────────────────────────────────

    // ─── Business Logic ─────────────────────────────────────────────────────

    public function determineAccountType(): string
    {
        return Gate::forUser($this)->allows('api.admin') ? 'admin' : 'user';
    }

    // ─── Notifications ──────────────────────────────────────────────────────

    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new QueuedVerifyEmail);
    }

    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new QueuedResetPassword($token));
    }
}
