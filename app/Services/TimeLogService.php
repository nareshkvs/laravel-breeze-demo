<?php

namespace App\Services;

use App\Models\TimeLog;
use App\Models\TimeLogEntry;
use App\Models\UserLeave;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Enums\PaginationCount;

class TimeLogService
{
    /**
     * Create time log entries for a user.
     * $data should contain 'work_date' and 'entries' array.
     */
    public function create(array $data, $user)
    {
        $workDate = Carbon::parse($data['work_date'])->startOfDay();

        // Check if user has a leave for this date
        $hasLeave = UserLeave::where('user_id', $user->id)
            ->where('from_date', '<=', $workDate->toDateString())
            ->where('to_date', '>=', $workDate->toDateString())
            ->exists();

        if ($hasLeave) {
            throw new \Exception('You have a leave for the selected date. Cannot submit work report.');
        }

        $entriesInput = $data['entries'];
        $totalMinutesNew = 0;
        $parsedEntries = [];

        foreach ($entriesInput as $idx => $e) {
            $durationText = trim($e['duration']);
            $durationText = str_replace('.', ':', $durationText);

            if (!preg_match('/^\d{1,2}:\d{2}$/', $durationText)) {
                throw new \Exception("Duration must be in H:MM format for entry #".($idx+1));
            }

            [$h, $m] = explode(':', $durationText);
            $h = intval($h);
            $m = intval($m);

            if ($m < 0 || $m > 59) {
                throw new \Exception("Invalid minutes value for entry #".($idx+1));
            }

            $minutes = $h * 60 + $m;

            if ($minutes > 600) {
                throw new \Exception("Individual task cannot exceed 10 hours for entry #".($idx+1));
            }

            $totalMinutesNew += $minutes;

            $parsedEntries[] = [
                'project_id' => $e['project_id'],
                'description' => $e['description'],
                'duration_minutes' => $minutes,
            ];
        }

        DB::beginTransaction();
        try {
            // Check existing time log for this user and date
            $timeLog = TimeLog::where('user_id', $user->id)
                ->where('work_date', $workDate->toDateString())
                ->first();

            $existingMinutes = $timeLog ? $timeLog->total_minutes : 0;

            if ($existingMinutes + $totalMinutesNew > 600) {
                DB::rollBack();
                throw new \Exception('Total daily time cannot exceed 10 hours.');
            }

            if (! $timeLog) {
                $timeLog = TimeLog::create([
                    'user_id' => $user->id,
                    'work_date' => $workDate->toDateString(),
                    'total_minutes' => $totalMinutesNew,
                ]);
            } else {
                $timeLog->total_minutes = $existingMinutes + $totalMinutesNew;
                $timeLog->save();
            }

            // Create entries (use correct foreign key column)
            foreach ($parsedEntries as $pe) {
                TimeLogEntry::create(array_merge($pe, ['time_log_id' => $timeLog->id]));
            }

            DB::commit();

            return $timeLog;

        } catch (\Exception $ex) {
            DB::rollBack();
            throw $ex;
        }
    }

    public function listForUser($user)
    {
        return TimeLog::with('entries.project')
            ->where('user_id', $user->id)
            ->orderBy('work_date', 'desc')
            ->get();
    }

    /**
     * Paginated list for a user
     */
    public function listForUserPaginated($user, int $perPage = PaginationCount::ONE->value)
    {
        return TimeLog::with('entries.project')
            ->where('user_id', $user->id)
            ->orderBy('work_date', 'desc')
            ->paginate($perPage);
    }

    /**
     * List all time logs (admin view).
     */
    public function listAll(int|null $limit = null)
    {
        return TimeLog::with('entries.project', 'user')
            ->orderBy('work_date', 'desc')
            ->when($limit, function($q) use ($limit){ $q->limit($limit); })
            ->get();
    }

    /**
     * Paginated list of all time logs, optional filter by user_id
     */
    public function listAllPaginated(int $perPage = PaginationCount::ONE->value, int|null $userId = null)
    {
        return TimeLog::with('entries.project', 'user')
            ->when($userId, function($q) use ($userId){ $q->where('user_id', $userId); })
            ->orderBy('work_date', 'desc')
            ->paginate($perPage);
    }

    public function deleteForUser($user, $id)
    {
        $timeLog = TimeLog::where('id', $id)->where('user_id', $user->id)->firstOrFail();
        $timeLog->delete();
        return $timeLog;
    }
}
