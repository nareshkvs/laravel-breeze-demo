<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Admin Overview</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6 mb-6">
                <h3 class="font-semibold">Leaves (Recent 5 Requests)</h3>
                @if($leaves->isEmpty())
                    <div class="mt-2">No leave requests.</div>
                @else
                    <table class="w-full table-auto mt-2">
                        <thead>
                            <tr class="text-left">
                                <th>From</th>
                                <th>To</th>
                                <th>Email</th>
                                <th>Status</th>
                                <th>Reason</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($leaves as $lv)
                                <tr class="border-t">
                                    <td class="py-2">{{ $lv->from_date->toDateString() }}</td>
                                    <td class="py-2">{{ $lv->to_date->toDateString() }}</td>
                                    <td class="py-2">{{ $lv->user->email ?? '' }}</td>
                                    <td class="py-2">{{ ucfirst(is_object($lv->status) ? $lv->status->value : $lv->status) }}</td>
                                    <td class="py-2">{{ $lv->reason ?? '-' }}</td>
                                    <td class="py-2 flex gap-2">
                                        @php $statusVal = is_object($lv->status) ? $lv->status->value : $lv->status; @endphp
                                        @if($statusVal !== 'approved')
                                            <form method="POST" action="{{ route('leaves.updateStatus', $lv->id) }}">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="status" value="approved" />
                                                <button type="submit" class="bg-green-600 text-white px-3 py-1 rounded">Approve</button>
                                            </form>
                                        @else
                                            <form method="POST" action="{{ route('leaves.updateStatus', $lv->id) }}">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="status" value="pending" />
                                                <button type="submit" class="bg-yellow-600 text-white px-3 py-1 rounded">Unapprove</button>
                                            </form>
                                        @endif

                                        <form method="POST" action="{{ route('leaves.destroy', $lv->id) }}" onsubmit="return confirm('Delete leave from {{ $lv->from_date->toDateString() }} to {{ $lv->to_date->toDateString() }}?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="bg-red-600 text-white px-3 py-1 rounded">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <h3 class="font-semibold">Time Logs (Recent 5 Entries)</h3>
                @if($logs->isEmpty())
                    <div class="mt-2">No time logs.</div>
                @else
                    <table class="w-full table-auto mt-2">
                        <thead>
                            <tr class="text-left">
                                <th>Date</th>
                                <th>Email</th>
                                <th>Total</th>
                                <th>Entries</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($logs as $log)
                                <tr class="border-t">
                                    <td class="py-2">{{ $log->work_date->toDateString() }}</td>
                                    <td class="py-2">{{ $log->user->email ?? '' }}</td>
                                    <td class="py-2">{{ intdiv($log->total_minutes,60) }}h {{ $log->total_minutes % 60 }}m</td>
                                    <td class="py-2">
                                        <ul>
                                            @foreach($log->entries as $entry)
                                                <li><strong>{{ $entry->project->name ?? 'Project' }}</strong>: {{ $entry->description }} — {{ intdiv($entry->duration_minutes,60) }}h {{ $entry->duration_minutes % 60 }}m</li>
                                            @endforeach
                                        </ul>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
