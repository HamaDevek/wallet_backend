<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'wallet_balance'
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

    public function incrementBalance($amount)
    {
        $this->wallet_balance += $amount;
        $this->save();
        return $this;
    }
    public function deductBalance($amount)
    {
        if (!isset($this->wallet_balance)) {
            throw new \Exception('Balance field not found. Please add balance field to users table.');
        }

        if ($this->wallet_balance < $amount) {
            throw new \Exception('Insufficient balance.');
        }

        $this->decrement('wallet_balance', $amount);
        return $this;
    }
    public function hasSufficientBalance($amount)
    {
        return $this->wallet_balance >= $amount;
    }
    public function receivedTransactions()
    {
        return $this->hasMany(Transactions::class, 'receiver_id');
    }
    public function sentTransactions()
    {
        return $this->hasMany(Transactions::class, 'sender_id');
    }
}
