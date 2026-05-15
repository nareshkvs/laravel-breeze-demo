<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserLeave extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'from_date',
        'to_date',
        'reason',
        'status',
    ];

    protected $casts = [
        'from_date' => 'date',
        'to_date'   => 'date',
        'status'    => \App\Enums\LeaveStatus::class,
    ];

    /*
    |-----------------------------------
    | Relationships
    |-----------------------------------
    */

    // Leave belongs to one user
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

}
