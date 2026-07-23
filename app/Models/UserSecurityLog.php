<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserSecurityLog extends Model
{
    protected $fillable = [
        'user_id',
        'event',
        'ip_address',
        'user_agent',
        'device',
        'meta',
    ];

      /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'meta' => 'array',
        ];
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
