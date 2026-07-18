<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'full_name',
        'photo',
        'phone_number',
        'company',
        'address',
        'role',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
