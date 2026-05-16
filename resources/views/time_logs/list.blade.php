<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">My Time Logs</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <div class="mb-4 mr-4 flex justify-end">
                    <a href="{{ route('time-logs.create') }}" class="text-blue-600">+ Add Time Log</a>
                </div>

                @if($logs->isEmpty())
                    <div>No time logs yet.</div>
                @else
                    <table class="w-full table-auto">
                        <thead>
                            <tr class="text-left">
                                <th>Date</th>
                                @if(Auth::user() && Auth::user()->isAdmin())
                                    <th>Email</th>
                                @endif
                                <th>Total</th>
                                <th>Entries</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($logs as $log)
                                <tr class="border-t">
                                    <td class="py-2">{{ $log->work_date->toDateString() }}</td>
                                    @if(Auth::user() && Auth::user()->isAdmin())
                                        <td class="py-2">{{ $log->user->email ?? '' }}</td>
                                    @endif
                                    <td class="py-2">{{ intdiv($log->total_minutes,60) }}h {{ $log->total_minutes % 60 }}m</td>
                                    <td class="py-2">
                                        <ul>
                                            @foreach($log->entries as $entry)
                                                <li><strong>{{ $entry->project->name ?? 'Project' }}</strong>: {{ $entry->description }} — {{ intdiv($entry->duration_minutes,60) }}h {{ $entry->duration_minutes % 60 }}m</li>
                                            @endforeach
                                        </ul>
                                    </td>
                                    <td class="py-2">
                                        <form method="POST" action="{{ route('time-logs.destroy', $log->id) }}" onsubmit="return confirm('Delete time log for {{ $log->work_date->toDateString() }}? This will erase all time log records for this date.');">
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
        </div>
    </div>
</x-app-layout>
