<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    /** @use HasFactory<\Database\Factories\LocationFactory> */
    use HasFactory;
     protected $guarded=[];

    public function employees()
    {
        return $this->hasMany(Employee::class);
    }
    public function scopeActive($q)
    {
        return $q->where('is_active', true);
    }
}
