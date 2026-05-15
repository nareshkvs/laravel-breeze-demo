<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Projects extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'status',
    ];

    /*
    |-----------------------------------
    | Relationships
    |-----------------------------------
    */

    // A project can have many time log entries
    public function timeLogEntries(): HasMany
    {
        return $this->hasMany(TimeLogEntries::class);
    }
}
