<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SiteDetail extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'email', 'phone', 'address', 'fb', 'tw', 'wh', 'ig'];
}
