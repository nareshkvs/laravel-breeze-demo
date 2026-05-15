<?php

namespace App\Services;

use App\Models\UserLeave;
use App\Models\TimeLog;
use App\Enums\LeaveStatus;
use Carbon\Carbon;

class LeaveService
{
    public function create(array $data, $user)
    {
        $from = Carbon::parse($data['from_date'])->startOfDay();
        $to = Carbon::parse($data['to_date'])->startOfDay();

        // Check for any time logs in the range
        $conflict = TimeLog::where('user_id', $user->id)
            ->whereBetween('work_date', [$from->toDateString(), $to->toDateString()])
            ->exists();

        if ($conflict) {
            throw new \Exception('Cannot apply leave: work reports exist in the selected date range.');
        }

        return UserLeave::create([
            'user_id' => $user->id,
            'from_date' => $from->toDateString(),
            'to_date' => $to->toDateString(),
            'reason' => $data['reason'] ?? null,
            'status' => LeaveStatus::PENDING->value,
        ]);
    }

    public function listForUser($user)
    {
        return UserLeave::where('user_id', $user->id)
            ->orderBy('from_date', 'desc')
            ->get();
    }

    /**
     * List all leaves (admin view).
     */
    public function listAll()
    {
        return UserLeave::with('user')
            ->orderBy('from_date', 'desc')
            ->limit(5)
            ->get();
    }

    public function deleteForUser($user, $id)
    {
        $leave = UserLeave::where('id', $id)->where('user_id', $user->id)->firstOrFail();
        $leave->delete();
        return $leave;
    }
    
    public function setStatus(int $id, string $status)
    {
        $leave = UserLeave::where('id', $id)->firstOrFail();
        $leave->status = $status;
        $leave->save();
        return $leave;
    }
}
