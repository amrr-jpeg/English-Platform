<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChestOpen extends Model
{
    protected $fillable = [
        'user_id',
        'reward_type',
        'reward_amount',
        'reward_label',
    ];
}