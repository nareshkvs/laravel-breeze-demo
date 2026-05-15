<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TimeLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'work_date',
        'total_minutes',
    ];

    protected $casts = [
        'work_date' => 'date',
    ];

    /*
    |-----------------------------------
    | Relationships
    |-----------------------------------
    */

    // TimeLog belongs to one user
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // TimeLog has many task entries
    public function entries(): HasMany
    {
        return $this->hasMany(TimeLogEntry::class);
    }
}
