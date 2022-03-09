<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property mixed $name
 * @property mixed $description
 * @property mixed $address
 * @property mixed $currency
 * @property bool|mixed $active
 * @method static find(int $id)
 */
class Wallet extends Model
{
    use HasFactory;

    public function currency()
    {
        return $this->belongsTo(Currency::class, 'currency_id', 'id');
    }
}
