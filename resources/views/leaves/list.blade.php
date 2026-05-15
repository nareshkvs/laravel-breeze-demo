<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">My Leaves</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <div class="mb-4 mr-4 flex justify-end">
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
                                @if(Auth::user() && Auth::user()->isAdmin())
                                    <th>Email</th>
                                @endif
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($leaves as $lv)
                                <tr class="border-t">
                                    <td class="py-2">{{ $lv->from_date->toDateString() }}</td>
                                    <td class="py-2">{{ $lv->to_date->toDateString() }}</td>
                                    @if(Auth::user() && Auth::user()->isAdmin())
                                        <td class="py-2">{{ $lv->user->email ?? '' }}</td>
                                    @endif
                                    <td class="py-2">{{ ucfirst(is_object($lv->status) ? $lv->status->value : $lv->status) }}</td>
                                    <td class="py-2 flex gap-2">
                                        @if(Auth::user()->isAdmin())
                                            @php
                                                $statusVal = is_object($lv->status) ? $lv->status->value : $lv->status;
                                            @endphp
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
                                        @endif

                                        <form method="POST" action="{{ route('leaves.destroy', $lv->id) }}" onsubmit="return confirm('Delete leave from {{ $lv->from_date->toDateString() }} to {{ $lv->to_date->toDateString() }}? This will erase the leave request.');">
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
