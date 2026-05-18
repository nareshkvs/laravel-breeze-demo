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

        // Prevent overlapping leaves for the same user
        $overlap = UserLeave::where('user_id', $user->id)
            ->whereDate('from_date', '<=', $to->toDateString())
            ->whereDate('to_date', '>=', $from->toDateString())
            ->exists();

        if ($overlap) {
            throw new \Exception('Cannot apply leave: overlapping leave already exists for the selected date range.');
        }

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
     * Paginated list for a user
     */
    public function listForUserPaginated($user, int $perPage = PaginationCount::ONE->value)
    {
        return UserLeave::where('user_id', $user->id)
            ->orderBy('from_date', 'desc')
            ->paginate($perPage);
    }

    /**
     * List all leaves (admin view).
     */
    public function listAll(int|null $limit = null)
    {
        return UserLeave::with('user')
            ->orderBy('from_date', 'desc')
            ->when($limit, function($q) use ($limit){ $q->limit($limit); })
            ->get();
    }

    /**
     * Paginated list of all leaves, optional filter by user_id
     */
    public function listAllPaginated(int $perPage = 15, int|null $userId = null)
    {
        return UserLeave::with('user')
            ->when($userId, function($q) use ($userId){ $q->where('user_id', $userId); })
            ->orderBy('from_date', 'desc')
            ->paginate($perPage);
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
