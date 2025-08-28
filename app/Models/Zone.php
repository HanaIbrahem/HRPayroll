<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Zone extends Model
{
    /** @use HasFactory<\Database\Factories\ZoneFactory> */
    use HasFactory;

    protected $guarded=[];

    public function visitedZones()
    {
        return $this->hasMany(VisitedZone::class);
    }
    
     // return only acive rows
    public function scopeActive($q)
    {
        return $q->where('is_active', true);
    }
}
    

