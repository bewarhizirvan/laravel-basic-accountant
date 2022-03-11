<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property mixed $full_name
 * @property mixed $address
 * @property mixed $phone
 * @property mixed $email
 * @property mixed $currency_id
 * @property bool|mixed $active
 * @property mixed $user_id
 * @method static select(string $string, string $string1)
 */
class Customer extends Model
{
    use HasFactory;

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class, 'currency_id', 'id');
    }
}
