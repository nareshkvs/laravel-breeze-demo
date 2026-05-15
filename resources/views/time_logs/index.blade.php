<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Time Logs</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
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

                <form method="POST" action="{{ route('time-logs.store') }}" id="time-log-form">
                    @csrf

                    <div id="client-errors" class="mb-4 text-red-700 hidden">
                        <ul id="client-errors-list"></ul>
                    </div>

                    <div class="mb-4">
                        <label class="block font-medium text-sm text-gray-700">Choose Date</label>
                        <input type="date" name="work_date" value="{{ old('work_date', now()->toDateString()) }}" max="{{ now()->toDateString() }}" required class="mt-1 block w-48 border-gray-300 rounded-md" />
                    </div>

                    <div id="tasks-container">
                        <div class="task-row mb-4 p-3 border rounded">
                            <div class="grid grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-sm">Project</label>
                                    <select name="entries[0][project_id]" class="mt-1 block w-full border-gray-300 rounded-md" required>
                                        <option value="">-- Select Project --</option>
                                        @foreach($projects as $p)
                                            <option value="{{ $p->id }}">{{ $p->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm">Duration (H:MM)</label>
                                    <input type="text" name="entries[0][duration]" placeholder="1:30" required class="mt-1 block w-full border-gray-300 rounded-md" />
                                </div>

                                <div>
                                    <label class="block text-sm">&nbsp;</label>
                                    <button type="button" class="remove-task bg-red-500 text-white px-3 py-1 rounded hidden">Remove</button>
                                </div>
                            </div>

                            <div class="mt-3">
                                <label class="block text-sm">Task Description</label>
                                <textarea name="entries[0][description]" rows="2" maxlength="1000" required class="mt-1 block w-full border-gray-300 rounded-md"></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <button type="button" id="add-task" class="bg-blue-600 text-white px-4 py-2 rounded">Add Task</button>
                    </div>

                    <div class="flex items-center gap-3">
                        <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded">Submit Time Log</button>
                        <a href="{{ route('time-logs.list') }}" class="bg-gray-300 text-gray-800 px-4 py-2 rounded">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        (() => {
            let idx = 1;
            const container = document.getElementById('tasks-container');
            document.getElementById('add-task').addEventListener('click', () => {
                const row = document.querySelector('.task-row').cloneNode(true);
                // Update names
                row.querySelectorAll('[name]').forEach(el => {
                    const name = el.getAttribute('name');
                    const newName = name.replace(/entries\[\d+\]/, `entries[${idx}]`);
                    el.setAttribute('name', newName);
                    if (el.tagName === 'SELECT') el.selectedIndex = 0;
                    if (el.tagName === 'INPUT') el.value = '';
                    if (el.tagName === 'TEXTAREA') el.value = '';
                });
                row.querySelector('.remove-task').classList.remove('hidden');
                container.appendChild(row);
                idx++;
            });

            document.addEventListener('click', (e) => {
                if (e.target && e.target.classList.contains('remove-task')) {
                    const rows = document.querySelectorAll('.task-row');
                    if (rows.length > 1) {
                        e.target.closest('.task-row').remove();
                    }
                }
            });

            // Client-side validation on submit
            const form = document.getElementById('time-log-form');
            const clientErrors = document.getElementById('client-errors');
            const clientErrorsList = document.getElementById('client-errors-list');

            form.addEventListener('submit', (ev) => {
                clientErrorsList.innerHTML = '';
                clientErrors.classList.add('hidden');

                const errors = [];
                const dateInput = form.querySelector('input[name="work_date"]');
                const workDate = dateInput.value;
                const today = new Date().toISOString().slice(0,10);
                if (!workDate) {
                    errors.push('Please select a work date.');
                } else if (workDate > today) {
                    errors.push('Work date cannot be in the future.');
                }

                const rows = container.querySelectorAll('.task-row');
                let totalMinutes = 0;

                rows.forEach((row, i) => {
                    const project = row.querySelector('select[name]');
                    const durationEl = row.querySelector('input[name$="[duration]"]');
                    const desc = row.querySelector('textarea[name$="[description]"]');

                    if (!project || !project.value) {
                        errors.push(`Row ${i+1}: Select a project.`);
                    }
                    if (!desc || !desc.value.trim()) {
                        errors.push(`Row ${i+1}: Enter a task description.`);
                    } else if (desc.value.length > 1000) {
                        errors.push(`Row ${i+1}: Description exceeds 1000 characters.`);
                    }

                    if (!durationEl) {
                        errors.push(`Row ${i+1}: Duration is required.`);
                        return;
                    }

                    let durationText = durationEl.value.trim();
                    durationText = durationText.replace('.', ':');
                    const match = /^\d{1,2}:\d{2}$/.test(durationText);
                    if (!match) {
                        errors.push(`Row ${i+1}: Duration must be in H:MM format.`);
                        return;
                    }
                    const parts = durationText.split(':');
                    const h = parseInt(parts[0], 10);
                    const m = parseInt(parts[1], 10);
                    if (isNaN(h) || isNaN(m) || m < 0 || m > 59) {
                        errors.push(`Row ${i+1}: Invalid duration value.`);
                        return;
                    }
                    const minutes = h * 60 + m;
                    if (minutes > 600) {
                        errors.push(`Row ${i+1}: Individual task cannot exceed 10 hours.`);
                    }
                    totalMinutes += minutes;
                });

                if (totalMinutes > 600) {
                    errors.push('Total daily time cannot exceed 10 hours.');
                }

                if (errors.length > 0) {
                    ev.preventDefault();
                    clientErrors.classList.remove('hidden');
                    errors.forEach(err => {
                        const li = document.createElement('li');
                        li.textContent = err;
                        clientErrorsList.appendChild(li);
                    });
                    window.scrollTo({ top: clientErrors.getBoundingClientRect().top + window.scrollY - 80, behavior: 'smooth' });
                }
            });
        })();
    </script>
</x-app-layout>
