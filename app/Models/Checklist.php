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

    public const STATUS_OPEN     = 'open';
    public const STATUS_PENDING  = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';
    protected $casts = [
        'status' => 'string',
    ];
    
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class,'user_id');
    }

    public function approver(): BelongsTo 
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function visitedZones(): HasMany
    {
        return $this->hasMany(VisitedZone::class);
    }

    public function canEdit(): bool
    {
        return $this->status === self::STATUS_OPEN;
    }

    public function canDelete(): bool
    {
        // allowed when "open" or "rejected"
        return in_array($this->status, [self::STATUS_OPEN, self::STATUS_REJECTED], true);
    }

    public function canMarkPending(): bool
    {
        return $this->status === self::STATUS_OPEN;
    }

    public function canView(): bool
    {
        return true; // always viewable
    }

}
