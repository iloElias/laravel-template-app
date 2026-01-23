<?php

namespace App\Models\Hr;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RememberBrowser extends Model
{
    use HasFactory;

    public $timestamps = [
        'created_at',
        'updated_at',
    ];

    protected $table = 'hr.remember_browser';

    protected $fillable = [
        'user_id',
        'browser_agent_id',
        'authenticated',
    ];
}
