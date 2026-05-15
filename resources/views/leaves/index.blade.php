<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Apply Leave</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">

                @if(session('success'))
                    <div class="mb-4 text-green-700">{{ session('success') }}</div>
                @endif

                @if($errors->any())
                    <div class="mb-4 text-red-700">
                        <ul>
                            @foreach($errors->all() as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('leaves.store') }}" id="leave-form">
                    @csrf

                    <div id="client-errors" class="mb-4 text-red-700 hidden">
                        <ul id="client-errors-list"></ul>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm">From Date</label>
                        <input type="date" name="from_date" max="{{ now()->toDateString() }}" value="{{ old('from_date') }}" required class="mt-1 block border-gray-300 rounded-md" />
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm">To Date</label>
                        <input type="date" name="to_date" max="{{ now()->toDateString() }}" value="{{ old('to_date') }}" required class="mt-1 block border-gray-300 rounded-md" />
                    </div>

                    <div>
                        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Apply Leave</button>
                    </div>
                </form>

                <script>
                    (function(){
                        const form = document.getElementById('leave-form');
                        const clientErrors = document.getElementById('client-errors');
                        const clientErrorsList = document.getElementById('client-errors-list');

                        form.addEventListener('submit', function(ev){
                            clientErrorsList.innerHTML = '';
                            clientErrors.classList.add('hidden');
                            const errors = [];
                            const from = form.querySelector('input[name="from_date"]').value;
                            const to = form.querySelector('input[name="to_date"]').value;
                            const today = new Date().toISOString().slice(0,10);

                            if (!from) errors.push('Please select a from date.');
                            if (!to) errors.push('Please select a to date.');
                            if (from && to && to < from) errors.push('To date must be the same or after From date.');
                            if (from && from > today) errors.push('From date cannot be in the future.');
                            if (to && to > today) errors.push('To date cannot be in the future.');

                            if (errors.length>0) {
                                ev.preventDefault();
                                clientErrors.classList.remove('hidden');
                                errors.forEach(err => {
                                    const li = document.createElement('li'); li.textContent = err; clientErrorsList.appendChild(li);
                                });
                                window.scrollTo({ top: clientErrors.getBoundingClientRect().top + window.scrollY - 80, behavior: 'smooth' });
                            }
                        });
                    })();
                </script>
            </div>
        </div>
    </div>
</x-app-layout>
