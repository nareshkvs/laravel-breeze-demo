<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">My Leaves</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <div class="mb-4">
                    <a href="{{ route('leaves.index') }}" class="text-blue-600">+ Apply Leave</a>
                </div>

                @if($leaves->isEmpty())
                    <div>No leave requests yet.</div>
                @else
                    <table class="w-full table-auto">
                        <thead>
                            <tr class="text-left">
                                <th>From</th>
                                <th>To</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($leaves as $lv)
                                <tr class="border-t">
                                    <td class="py-2">{{ $lv->from_date->toDateString() }}</td>
                                    <td class="py-2">{{ $lv->to_date->toDateString() }}</td>
                                    <td class="py-2">{{ ucfirst($lv->status) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
