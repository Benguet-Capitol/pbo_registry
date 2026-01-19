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

    <!-- Page Content Wrapper with Transition -->
    <div class="page-transition">

   <!-- Filter Section -->
    <div class="bg-white overflow-hidden shadow-md sm:rounded-lg mb-6 dark:bg-gray-800 transition-all duration-300 ease-in-out">
    <div class="p-4 bg-white rounded-md border-b border-gray-200 dark:bg-gray-800 dark:border-gray-700 transition-colors duration-300 ease-in-out">
    <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-100 mb-4 flex items-center">
        <i class="fas fa-filter mr-2 text-blue-600 dark:text-blue-400"></i>
        Filters
    </h4>
    <form method="GET" id="filterForm" class="space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="md:col-span-2">
                <input 
                    type="text" 
                    name="search" 
                    value="{{ request('search') }}"
                    placeholder="Search logs..." 
                    class="text-xs w-full border border-gray-300 rounded-lg px-3 py-2 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-400 focus:border-transparent transition-all duration-200 ease-in-out hover:border-blue-400 dark:hover:border-blue-500">
            </div>

            <div>
                <select 
                    name="event_type" 
                    class="text-xs w-full border border-gray-300 rounded-lg px-3 py-2 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-400 focus:border-transparent transition-all duration-200 ease-in-out hover:border-blue-400 dark:hover:border-blue-500">
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
                    class="text-xs w-full border border-gray-300 rounded-lg px-3 py-2 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-400 focus:border-transparent transition-all duration-200 ease-in-out hover:border-blue-400 dark:hover:border-blue-500">
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">To Date</label>
                <input 
                    type="date" 
                    name="date_to" 
                    value="{{ request('date_to') }}"
                    class="text-xs w-full border border-gray-300 rounded-lg px-3 py-2 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-400 focus:border-transparent transition-all duration-200 ease-in-out hover:border-blue-400 dark:hover:border-blue-500">
            </div>
        </div>
    </form>
    </div>

                    <!-- Table -->
    <div class="p-4 bg-white rounded-md border-b border-gray-200 relative overflow-x-auto shadow-md sm:rounded-lg dark:bg-gray-800 dark:border-gray-700 transition-colors duration-300 ease-in-out">
        <div class="overflow-x-auto overflow-y-auto max-h-[700px] border border-gray-300 dark:border-gray-600 rounded-md">
            <table class="w-full text-left text-gray-800 dark:text-gray-200 text-[11px]">
                <thead class="sticky top-0 z-10 bg-gray-700 text-gray-100 dark:bg-gray-200 dark:text-gray-900 border border-gray-300 dark:border-gray-600">
                    <tr>
                            <th class="px-1 py-1 min-w-[120px] text-center border border-gray-300 dark:border-gray-600">User</th>
                            <th class="px-1 py-1 min-w-[100px] text-center border border-gray-300 dark:border-gray-600">Event Type</th>
                            <th class="px-1 py-1 min-w-[150px] text-center border border-gray-300 dark:border-gray-600">Date & Time</th>
                            <th class="px-1 py-1 min-w-[200px] text-center border border-gray-300 dark:border-gray-600">Description</th>
                            <th class="px-1 py-1 min-w-[120px] text-center border border-gray-300 dark:border-gray-600">IP Address</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                            <tr class="bg-white border dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                                <td class="px-1 py-2 text-center border border-gray-300 dark:border-gray-600 text-gray-900 dark:text-white font-semibold">{{ $log->user->name ?? 'N/A' }}</td>
                                <td class="px-1 py-2 text-center border border-gray-300 dark:border-gray-600">
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
                                <td class="px-1 py-2 text-center border border-gray-300 dark:border-gray-600 text-gray-600 dark:text-gray-300">{{ $log->created_at->setTimezone('Asia/Manila')->format('F j, Y h:i A') }}</td>
                                <td class="px-1 py-2 text-center border border-gray-300 dark:border-gray-600 text-gray-600 dark:text-gray-300">{{ $log->description }}</td>
                                <td class="px-1 py-2 text-center border border-gray-300 dark:border-gray-600 text-gray-600 dark:text-gray-300">{{ $log->ip_address }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-1 py-2 text-center border border-gray-300 dark:border-gray-600 text-gray-500 dark:text-gray-400">
                                    No activity logs found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-4 px-4 py-2">
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

<style>
    @keyframes pageSlideUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .page-transition {
        animation: pageSlideUp 0.4s ease-in-out;
    }
</style>
    </div>
</x-app-layout>