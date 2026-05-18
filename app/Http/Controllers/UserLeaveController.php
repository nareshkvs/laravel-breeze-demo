<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\User;
use App\Services\LeaveService;
use App\Models\UserLeave;
use App\Enums\LeaveStatus;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use App\Enums\PaginationCount;
use App\Policies\LeavePolicy;

class UserLeaveController extends Controller
{
    use AuthorizesRequests, ValidatesRequests;
    
    /**
     * @var LeaveService
     */
    protected LeaveService $service;

    public function __construct(LeaveService $service)
    {
        $this->service = $service;
    }

    public function create()
    {
        $user = Auth::user();
        $existingLeaves = $this->service->listForUser($user);
        return view('leaves.index', compact('existingLeaves'));
    }

    /**
     * Display list of user's leaves.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $perPage = intval($request->input('per_page', PaginationCount::ONE->value));
        $selectedUser = $request->input('user_id');

        if ($user->isAdmin()) {
            $users = User::orderBy('name')->get();
            $leaves = $this->service->listAllPaginated($perPage, $selectedUser ? intval($selectedUser) : null);
            return view('leaves.list', compact('leaves','users','selectedUser'));
        } else {
            $leaves = $this->service->listForUserPaginated($user, $perPage);
            return view('leaves.list', compact('leaves'));
        }
    }

    /**
     * Delete a leave request.
     */
    public function destroy($id)
    {
        $user = Auth::user();
        $leave = $this->service->deleteForUser($user, $id);
        $range = $leave->from_date->toDateString() . ' to ' . $leave->to_date->toDateString();
        return redirect()->route('leaves.index')->with('success', "Leave ({$range}) deleted.");
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'from_date' => ['required', 'date', 'before_or_equal:today'],
            'to_date' => ['required', 'date', 'after_or_equal:from_date', 'before_or_equal:today'],
        ]);

        try {
            $leave = $this->service->create($request->only(['from_date','to_date','reason']), $user);
            return redirect()->route('leaves.index')->with('success', 'Leave request submitted.');
        } catch (\Exception $ex) {
            return back()->withErrors(['from_date' => $ex->getMessage()])->withInput();
        }
    }

    /**
     * Update leave status (approve/unapprove) - admin action.
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => ['required', 'in:'.implode(',', array_column(LeaveStatus::cases(), 'value'))],
        ]);

        $leave = UserLeave::findOrFail($id);

        $this->authorize('approve', $leave);

        $this->service->setStatus($id, $request->input('status'));

        return redirect()->route('leaves.index')->with('success', 'Leave status updated.');
    }
}
