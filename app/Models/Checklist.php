<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
class Checklist extends Model
{
    /** @use HasFactory<\Database\Factories\ChecklistFactory> */
    use HasFactory;
    protected $guarded=[];

    
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function visitedZones(): HasMany
    {
        return $this->hasMany(VisitedZone::class);
    }

}
