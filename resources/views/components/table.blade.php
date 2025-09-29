<!-- filepath: /d:/xampp/htdocs/pbo-registry/resources/views/components/table.blade.php -->

@props(['headers', 'rows', 'actions'])

<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mt-6">
    <div class="p-6 bg-white border-b border-gray-200">
        <h3 class="text-lg font-medium text-gray-900">{{ $title ?? 'Table' }}</h3>
        <table class="min-w-full bg-white">
            <thead>
                <tr>
                    @foreach ($headers as $header)
                    <th class="px-4 py-2 border-b-2 border-gray-300 text-left leading-4 text-gray-600 tracking-wider">{{ $header }}</th>
                    @endforeach
                    @if ($actions)
                    <th class="px-4 py-2 border-b-2 border-gray-300 text-left leading-4 text-gray-600 tracking-wider">{{ __('Actions') }}</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @foreach ($rows as $row)
                <tr>
                    @foreach ($row as $cell)
                    <td class="px-4 py-2 border-b border-gray-300 text-gray-600">{{ $cell }}</td>
                    @endforeach
                    @if ($actions)
                    <td class="px-4 py-2 border-b border-gray-300 text-gray-600">
                        <button onclick="openEditModal({{ $row['id'] }})" class="inline-flex items-center px-4 py-2 bg-blue-500 border border-transparent rounded-md font-semibold text-xs text-white tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:border-blue-900 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150">
                            {{ __('Edit') }}
                        </button>
                    </td>
                    @endif
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>