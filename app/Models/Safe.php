<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property mixed $full_name
 * @property mixed $address
 * @property mixed $description
 * @property mixed $type
 * @property mixed $direction
 * @property mixed $amount
 * @property mixed $currency_id
 * @property mixed $wallet_id
 * @property mixed $customer_id
 * @property bool|mixed $active
 * @property mixed $user_id
 * @property mixed $created_at
 */
class Safe extends Model
{
    use HasFactory;
    protected $table = 'safe';

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function wallet()
    {
        return $this->belongsTo(Wallet::class, 'wallet_id', 'id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'id');
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class, 'currency_id', 'id');
    }
}
