<?php

namespace App\Http\Controllers;

use App\Enums\PaginationCount;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Project;
use App\Services\TimeLogService;
use App\Services\LeaveService;


class TimeLogController extends Controller
{
    /**
     * @var TimeLogService
     */
    protected TimeLogService $service;
    protected LeaveService $leaveService;

    public function __construct(TimeLogService $service, LeaveService $leaveService)
    {
        $this->service = $service;
        $this->leaveService = $leaveService;
    }

    /**
     * Show the time log form.
     */
    public function create()
    {
        $projects = Project::where('status', 'active')->get();

        return view('time_logs.index', compact('projects'));
    }

    /**
     * Display list of user's time logs.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $perPage = intval($request->input('per_page', PaginationCount::ONE->value));
        $selectedUser = $request->input('user_id');

        if ($user->isAdmin()) {
            // provide users list for filter
            $users = User::orderBy('name')->get();
            $logs = $this->service->listAllPaginated($perPage, $selectedUser ? intval($selectedUser) : null);
            return view('time_logs.list', compact('logs','users','selectedUser'));
        } else {
            $logs = $this->service->listForUserPaginated($user, $perPage);
            return view('time_logs.list', compact('logs'));
        }
    }

    /**
     * Admin overview showing all leaves and timelogs.
     */
    public function adminOverview()
    {
        $user = Auth::user();
        if (! $user->isAdmin()) {
            abort(403);
        }

        $leaves = $this->leaveService->listAll(5);
        $logs = $this->service->listAll(5);

        return view('admin.overview', compact('leaves','logs'));
    }

    /**
     * Delete a time log and its entries for the user.
     */
    public function destroy($id)
    {
        $user = Auth::user();
        $timeLog = $this->service->deleteForUser($user, $id);
        $date = $timeLog->work_date->toDateString();
        return redirect()->route('time-logs.index')->with('success', "Time log for {$date} deleted.");
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

        try {
            $this->service->create($request->only(['work_date','entries']), $user);
            return redirect()->route('time-logs.index')->with('success', 'Time log saved.');
        } catch (\Exception $ex) {
            return back()->withErrors(['error' => $ex->getMessage()])->withInput();
        }
    }
}
