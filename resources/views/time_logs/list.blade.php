<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">My Time Logs</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <div class="mb-4">
                    <a href="{{ route('time-logs.index') }}" class="text-blue-600">+ Add Time Log</a>
                </div>

                @if($logs->isEmpty())
                    <div>No time logs yet.</div>
                @else
                    <table class="w-full table-auto">
                        <thead>
                            <tr class="text-left">
                                <th>Date</th>
                                <th>Total</th>
                                <th>Entries</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($logs as $log)
                                <tr class="border-t">
                                    <td class="py-2">{{ $log->work_date->toDateString() }}</td>
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
