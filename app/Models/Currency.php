<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property mixed $code
 * @property mixed $rate
 * @property bool|mixed $active
 * @property mixed $user_id
 */
class Currency extends Model
{
    use HasFactory;
}
