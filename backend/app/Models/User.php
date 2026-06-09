<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
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
        'phone',
        'role', // super_admin, store_admin, customer
        'store_id',
    ];

    /**
     * Campos que devem ficar ocultos quando o model for convertido para JSON (API).
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Conversão automática de tipos.
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    // Um usuário (seja admin ou cliente) pertence a uma loja (Tenant)
    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    // Um usuário (cliente) pode ter feito vários pedidos
    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    public function isStoreAdmin(): bool
    {
        return $this->role === 'store_admin';
    }

    public function isCustomer(): bool
    {
        return $this->role === 'customer';
    }
}
