<?php

namespace App\Models;

use Brick\Money\Money;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * @mixin IdeHelperUser
 */
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public Money $balance;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->balance = Money::zero('EUR');
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
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
        ];
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class, 'owner_id');
    }

    public function paymentsIssued()
    {
        return $this->hasMany(Payment::class, 'from_user_id');
    }

    public function paymentsReceived()
    {
        return $this->hasMany(Payment::class, 'to_user_id');
    }
}
