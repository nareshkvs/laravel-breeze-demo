<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Projects;
use App\Models\TimeLogs;
use App\Models\TimeLogEntries;
use App\Models\UserLeaves;

class TimeLogController extends Controller
{
    /**
     * Show the time log form.
     */
    public function index()
    {
        $projects = Projects::where('status', 'active')->get();

        return view('time_logs.index', compact('projects'));
    }

    /**
     * Display list of user's time logs.
     */
    public function list()
    {
        $user = Auth::user();

        $logs = TimeLogs::with('entries.project')
            ->where('user_id', $user->id)
            ->orderBy('work_date', 'desc')
            ->get();

        return view('time_logs.list', compact('logs'));
    }

    /**
     * Store time log entries for a date.
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'work_date' => ['required', 'date', 'before_or_equal:today'],
            'entries' => ['required', 'array', 'min:1'],
            'entries.*.project_id' => ['required', 'exists:projects,id'],
            'entries.*.description' => ['required', 'string', 'max:1000'],
            'entries.*.duration' => ['required', 'string'],
        ]);

        $workDate = Carbon::parse($request->input('work_date'))->startOfDay();

        // Check if user has a leave for this date
        $hasLeave = UserLeaves::where('user_id', $user->id)
            ->where('from_date', '<=', $workDate->toDateString())
            ->where('to_date', '>=', $workDate->toDateString())
            ->exists();

        if ($hasLeave) {
            return back()->withErrors(['work_date' => 'You have a leave for the selected date. Cannot submit work report.'])->withInput();
        }

        // Parse and validate durations
        $entriesInput = $request->input('entries');
        $totalMinutesNew = 0;
        $parsedEntries = [];

        foreach ($entriesInput as $idx => $e) {
            $durationText = trim($e['duration']);

            // Accept formats like H:MM or HH:MM or H.MM (treat dot as colon)
            $durationText = str_replace('.', ':', $durationText);

            if (!preg_match('/^\d{1,2}:\d{2}$/', $durationText)) {
                return back()->withErrors(["entries.$idx.duration" => 'Duration must be in H:MM format.'])->withInput();
            }

            [$h, $m] = explode(':', $durationText);
            $h = intval($h);
            $m = intval($m);

            if ($m < 0 || $m > 59) {
                return back()->withErrors(["entries.$idx.duration" => 'Invalid minutes value.'])->withInput();
            }

            $minutes = $h * 60 + $m;

            if ($minutes > 600) { // single task cannot exceed 10 hours
                return back()->withErrors(["entries.$idx.duration" => 'Individual task cannot exceed 10 hours.'])->withInput();
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
            $timeLog = TimeLogs::where('user_id', $user->id)
                ->where('work_date', $workDate->toDateString())
                ->first();

            $existingMinutes = $timeLog ? $timeLog->total_minutes : 0;

            if ($existingMinutes + $totalMinutesNew > 600) {
                DB::rollBack();
                return back()->withErrors(['entries' => 'Total daily time cannot exceed 10 hours.'])->withInput();
            }

            if (! $timeLog) {
                $timeLog = TimeLogs::create([
                    'user_id' => $user->id,
                    'work_date' => $workDate->toDateString(),
                    'total_minutes' => $totalMinutesNew,
                ]);
            } else {
                $timeLog->total_minutes = $existingMinutes + $totalMinutesNew;
                $timeLog->save();
            }

            // Create entries
            foreach ($parsedEntries as $pe) {
                TimeLogEntries::create(array_merge($pe, ['time_logs_id' => $timeLog->id]));
            }

            DB::commit();

            return redirect()->route('time-logs.list')->with('success', 'Time log saved.');

        } catch (\Exception $ex) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to save time log.'])->withInput();
        }
    }
}
