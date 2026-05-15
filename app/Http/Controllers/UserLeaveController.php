<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\UserLeaves;
use App\Models\TimeLogs;

class UserLeaveController extends Controller
{
    public function index()
    {
        return view('leaves.index');
    }

    /**
     * Display list of user's leaves.
     */
    public function list()
    {
        $user = Auth::user();

        $leaves = UserLeaves::where('user_id', $user->id)
            ->orderBy('from_date', 'desc')
            ->get();

        return view('leaves.list', compact('leaves'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'from_date' => ['required', 'date', 'before_or_equal:today'],
            'to_date' => ['required', 'date', 'after_or_equal:from_date', 'before_or_equal:today'],
        ]);

        $from = Carbon::parse($request->input('from_date'))->startOfDay();
        $to = Carbon::parse($request->input('to_date'))->startOfDay();

        // Check for any time logs in the range
        $conflict = TimeLogs::where('user_id', $user->id)
            ->whereBetween('work_date', [$from->toDateString(), $to->toDateString()])
            ->exists();

        if ($conflict) {
            return back()->withErrors(['from_date' => 'Cannot apply leave: work reports exist in the selected date range.'])->withInput();
        }

        UserLeaves::create([
            'user_id' => $user->id,
            'from_date' => $from->toDateString(),
            'to_date' => $to->toDateString(),
            'status' => 'pending',
        ]);

        return redirect()->route('leaves.list')->with('success', 'Leave request submitted.');
    }
}
