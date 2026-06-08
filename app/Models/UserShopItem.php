<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserShopItem extends Model
{
    protected $fillable = [
        'user_id',
        'item_key',
    ];
}