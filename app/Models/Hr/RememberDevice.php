<?php

namespace App\Models\Hr;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RememberDevice extends Model
{
    use HasFactory;

    protected $table = 'hr.remember_device';

    protected $fillable = [
        'user_id',
        'device_agent_id',
        'authenticated',
    ];
}
