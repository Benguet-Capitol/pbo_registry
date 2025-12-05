<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h3 class="font-semibold text-xl leading-tight dark:text-gray-200">
                {{ __('Activity Logs') }}
            </h3>

            <!-- Right: Breadcrumb Navigation -->
                @if(isset($breadcrumb))
                <nav class="text-xs text-gray-600 dark:text-gray-300" aria-label="Breadcrumb">
                    <ol class="list-none p-0 inline-flex items-center space-x-1 rtl:space-x-reverse">
                        @foreach ($breadcrumb as $index => $item)
                        <li>
                            @if (!empty($item['route']) && $index < count($breadcrumb) - 1)
                                <a href="{{ $item['route'] }}" class="text-gray-600 hover:underline dark:text-blue-400">
                                {{ $item['label'] }}
                                </a>
                                <span class="mx-2">/</span>
                                @else
                                <span class="text-gray-500 dark:text-gray-400">{{ $item['label'] }}</span>
                                @endif
                        </li>
                        @endforeach
                    </ol>
                </nav>
                @endif
        </div>
    </x-slot>

   <!-- Filter Section -->
    <div class="bg-white p-4 rounded-lg shadow-md mb-3 dark:bg-gray-800">
    <h4 class="text-xs font-semibold text-gray-700 dark:text-gray-100 mb-3">Filters</h4>
    <form method="GET" id="filterForm" class="space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="md:col-span-2">
                <input 
                    type="text" 
                    name="search" 
                    value="{{ request('search') }}"
                    placeholder="Search logs..." 
                    class="text-xs w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 focus:border-blue-500 focus:ring-blue-500">
            </div>

            <div>
                <select 
                    name="event_type" 
                    class="text-xs w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 focus:border-blue-500 focus:ring-blue-500">
                    <option value="">All Event Types</option>
                    @foreach($eventTypes as $type)
                        <option value="{{ $type }}" {{ request('event_type') == $type ? 'selected' : '' }}>
                            {{ ucfirst($type) }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">From Date</label>
                <input 
                    type="date" 
                    name="date_from" 
                    value="{{ request('date_from') }}"
                    class="text-xs w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 focus:border-blue-500 focus:ring-blue-500">
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">To Date</label>
                <input 
                    type="date" 
                    name="date_to" 
                    value="{{ request('date_to') }}"
                    class="text-xs w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 focus:border-blue-500 focus:ring-blue-500">
            </div>
        </div>
    </form>
</div>

                    <!-- Table -->
    <div class="bg-white overflow-hidden sm:rounded-lg shadow-md mb-6 dark:bg-gray-800">
        <div class="p-6 bg-white rounded-md border-b border-gray-200 relative overflow-x-auto shadow-md sm:rounded-lg dark:bg-gray-800 dark:border-gray-700">
            <div class="flex justify-between items-center mb-4">
                <table class="w-full text-left text-gray-800 dark:text-gray-200">
                    <thead class="text-center text-xs border-b-2 border-gray-700 text-gray-700 bg-gray-50 border-t-2 dark:bg-gray-700 dark:text-gray-400">
                        <tr>
                            <th class="px-2 py-3 border-gray-300 leading-4 text-gray-600 dark:text-gray-300">User</th>
                            <th class="px-2 py-3 border-gray-300 leading-4 text-gray-600 dark:text-gray-300">Event Type</th>
                            <th class="px-2 py-3 border-gray-300 leading-4 text-gray-600 dark:text-gray-300">Date & Time</th>
                            <th class="px-2 py-3 border-gray-300 leading-4 text-gray-600 dark:text-gray-300">Description</th>
                            <th class="px-2 py-3 border-gray-300 leading-4 text-gray-600 dark:text-gray-300">IP Address</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700 text-xs">
                        @forelse($logs as $log)
                            <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 border-gray-200 hover:bg-gray-50 dark:hover:bg-gray-600">
                                <td class="px-2 py-3 text-center text-gray-600 dark:text-gray-300">{{ $log->user->name ?? 'N/A' }}</td>
                                <td class="px-2 py-3 text-center text-gray-600 dark:text-gray-300">
                                    <span class="px-2 py-1 rounded-full text-xs font-semibold {{ 
                                        match($log->event_type) {
                                            'create' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900 dark:text-emerald-200',
                                            'update' => 'bg-sky-100 text-sky-800 dark:bg-sky-900 dark:text-sky-200',
                                            'delete' => 'bg-rose-100 text-rose-800 dark:bg-rose-900 dark:text-rose-200',
                                            'auth' => 'bg-violet-100 text-violet-800 dark:bg-violet-900 dark:text-violet-200',
                                            default => 'bg-slate-100 text-slate-800 dark:bg-slate-700 dark:text-slate-200'
                                        }
                                    }}">
                                        {{ ucfirst($log->event_type) }}
                                    </span>
                                </td>
                                <td class="px-2 py-3 text-center text-gray-600 dark:text-gray-300">{{ $log->created_at->setTimezone('Asia/Manila')->format('F j, Y h:i A') }}</td>
                                <td class="px-2 py-3 text-center text-gray-600 dark:text-gray-300">{{ $log->description }}</td>
                                <td class="px-2 py-3 text-center text-gray-600 dark:text-gray-300">{{ $log->ip_address }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-2 py-3 text-center text-gray-500 dark:text-gray-400">
                                    No activity logs found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-4">
                {{ $logs->links() }}
            </div>
        </div>
    </div>
</div>

<script>
    document.querySelectorAll('#filterForm input, #filterForm select').forEach(element => {
        element.addEventListener('change', () => {
            document.getElementById('filterForm').submit();
        });
    });
</script>

</x-app-layout>