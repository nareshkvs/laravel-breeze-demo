<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TimeLogEntries extends Model
{
    use HasFactory;

    protected $fillable = [
        'time_logs_id',
        'project_id',
        'description',
        'duration_minutes',
    ];

    /*
    |-----------------------------------
    | Relationships
    |-----------------------------------
    */

    // Entry belongs to one TimeLog
    public function timeLog(): BelongsTo
    {
        return $this->belongsTo(TimeLogs::class);
    }

    // Entry belongs to one Project
    public function project(): BelongsTo
    {
        return $this->belongsTo(Projects::class);
    }
}
