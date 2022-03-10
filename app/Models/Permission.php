<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $table = 'permissions';

    public function profiles()
    {
        return $this->belongsToMany('App\Models\Profile')->withTimestamps();
    }
}
