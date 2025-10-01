<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <!-- Left: Obligations Title -->
            <h3 class="font-semibold text-xl leading-tight dark:text-gray-200">
                {{ __('Disbursements') }} (
                <span class="text-blue-800 dark:text-blue-400">
                {{ $officeAllotmentClass->offices->office_abbreviation }} - {{ $officeAllotmentClass->allotmentClass->class }} |
                OBR No: {{ $obligation->obr_no }}
                </span> )
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

    <!-- Combined Obligation Details and Appropriations -->
    <div class="bg-white overflow-hidden sm:rounded-lg shadow-md mb-6 dark:bg-gray-800">
        <!-- Obligation Details -->
        <div class="px-6 pt-6 border-gray-200 dark:border-gray-700">
            <h4 class="text-lg font-semibold text-gray-700 dark:text-gray-300">Obligation Details</h4>
            <table class="mt-4 w-full text-xs text-left text-gray-500 dark:text-gray-400">
                <tbody>
                    <tr class="bg-white border-t border-b dark:bg-gray-800 dark:border-gray-700">
                        <td class="px-4 py-3 font-semibold text-gray-700 dark:text-gray-300">Office:</td>
                        <td class="px-4 py-3 text-gray-600 dark:text-gray-400">{{ $officeAllotmentClass->offices->office_abbreviation }}</td>
                        <td class="px-4 py-3 font-semibold text-gray-700 dark:text-gray-300">Allotment Class:</td>
                        <td class="px-4 py-3 text-gray-600 dark:text-gray-400">{{ $officeAllotmentClass->allotmentClass->class }}</td>
                    </tr>
                    <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                        <td class="px-4 py-3 font-semibold text-gray-700 dark:text-gray-300">OBR No:</td>
                        <td class="px-4 py-3 text-gray-600 dark:text-gray-400">{{ $obligation->obr_no }}</td>
                        <td class="px-4 py-3 font-semibold text-gray-700 dark:text-gray-300">OBR Type:</td>
                        <td class="px-4 py-3 text-gray-600 dark:text-gray-400">{{ $obligation->obr_type }}</td>
                    </tr>
                    <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                        <td class="px-4 py-3 font-semibold text-gray-700 dark:text-gray-300">OBR Date:</td>
                        <td class="px-4 py-3 text-gray-600 dark:text-gray-400">{{ $obligation->obr_date }}</td>
                        <td class="px-4 py-3 font-semibold text-gray-700 dark:text-gray-300">Particulars:</td>
                        <td class="px-4 py-3 text-gray-600 dark:text-gray-400">{{ $obligation->particulars }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Obligation Appropriations Table -->
        <div class="px-6 pb-6">
            <div class="overflow-x-auto">
                <table class="w-full text-xs text-left text-gray-500 dark:text-gray-400">
                    <thead class="text-xs text-gray-700 bg-gray-100 dark:bg-gray-700 dark:text-gray-300">
                        <tr>
                            <th class="px-4 py-3 text-center">Program</th>
                            <th class="px-4 py-3 text-center">Account Code</th>
                            <th class="px-4 py-3 text-center">Description</th>
                            <th class="px-4 py-3 text-center">Adjusted Obligation Amount</th>
                            <th class="px-4 py-3 text-center">Purchase Order Amount</th>
                            <th class="px-4 py-3 text-center">Disbursement Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($appropriations as $row)
                            <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                                <td class="px-4 py-3 text-gray-700 dark:text-gray-300 text-center">{{ $row['program'] ?: '-' }}</td>
                                <td class="px-4 py-3 text-gray-700 dark:text-gray-300 text-center">{{ $row['account_code'] }}</td>
                                <td class="px-4 py-3 text-gray-700 dark:text-gray-300 text-center">{{ $row['description'] }}</td>
                                <td class="px-4 py-3 text-right text-gray-700 dark:text-gray-300">
                                    {{ number_format($row['obr_amount'], 2) }}
                                </td>
                                <td class="px-4 py-3 text-right text-gray-700 dark:text-gray-300">
                                    {{ number_format($row['po_amount'], 2) }}
                                </td>
                                <td class="px-4 py-3 text-right text-gray-700 dark:text-gray-300">
                                    {{ number_format($row['disbursement_amount'], 2) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Flash Messages -->
    @if(session('status'))
        @php
            $status = session('status');
            $color = match ($status['type'] ?? 'info') {
                'delete' => 'red',
                'update' => 'blue',
                default => 'green'
            };
        @endphp
        <div class="bg-{{ $color }}-100 border border-{{ $color }}-400 text-{{ $color }}-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">{!! $status['message'] ?? $status !!}</span>
            <button type="button" class="absolute top-0 bottom-0 right-0 px-4 py-3" onclick="this.parentElement.style.display='none';">
                <span class="text-{{ $color }}-700 ">&times;</span>
            </button>
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">{!! session('error') !!}</span>
            <button type="button" class="absolute top-0 bottom-0 right-0 px-4 py-3" onclick="this.parentElement.style.display='none';">
                <span class="text-red-700 ">&times;</span>
            </button>
        </div>
    @endif

    <div class="bg-white overflow-hidden sm:rounded-lg shadow-md mb-6 dark:bg-gray-800">
        <div class="p-6 bg-white rounded-md border-b border-gray-200 relative overflow-x-auto shadow-md sm:rounded-lg dark:bg-gray-800 dark:border-gray-700">
            <div class="flex justify-between items-center mb-4">
                <button onclick="openCreateModal()" class="text-blue-600 inline-flex leading-4 tracking-wider items-center hover:text-white border border-blue-600 hover:bg-blue-600 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-xs px-5 py-3 text-center dark:border-blue-500 dark:text-blue-500 dark:hover:text-white dark:hover:bg-blue-600 dark:focus:ring-blue-900">
                    <i class="fas fa-plus text-xl mr-1 -ml-1 w-5 h-5"></i>
                    {{ __('Add Disbursement') }}
                </button>
            </div>
            <table id="adjustmentsTable" class="text-center w-full text-xs text-left rtl:text-right text-gray-500 dark:text-gray-400 mb-10">
                <thead class="text-center text-xs border-b-2 border-gray-400 text-gray-700 bg-gray-50 border-t-2 dark:bg-gray-700 dark:text-gray-400">
                    <tr>
                        <th class="px-2 py-3 border-gray-300 leading-4 text-gray-600 dark:text-gray-300">DV / Check Number</th>
                        <th class="px-2 py-3 border-gray-300 leading-4 text-gray-600 dark:text-gray-300">Date</th>
                        <th class="px-2 py-3 border-gray-300 leading-4 text-gray-600 dark:text-gray-300">Status</th>
                        <th class="px-2 py-3 border-gray-300 leading-4 text-gray-600 dark:text-gray-300">Remarks</th>
                        <th class="px-2 py-3 border-gray-300 leading-4 text-gray-600 dark:text-gray-300">Program</th>
                        <th class="px-2 py-3 border-gray-300 leading-4 text-gray-600 dark:text-gray-300">Account Code</th>
                        <th class="px-2 py-3 border-gray-300 leading-4 text-gray-600 dark:text-gray-300">Description</th>
                        <th class="px-2 py-3 border-gray-300 leading-4 text-gray-600 dark:text-gray-300">DV / Check Amount</th>
                        <th class="px-2 py-3 border-gray-300 leading-4 text-gray-600 dark:text-gray-300">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $lastDvNo = null;
                        $lastDvDate = null;
                        $lastStatus = null;
                    @endphp
                    @foreach ($disbursements as $disbursement)
                    <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 border-gray-200 hover:bg-gray-50 dark:hover:bg-gray-600">
                        <td class="px-2 py-3 text-gray-700 dark:text-gray-300">{{ $disbursement->dv_no !== $lastDvNo ? $disbursement->dv_no : '' }}</td>
                        <td class="px-2 py-3 text-gray-700 dark:text-gray-300">{{ $disbursement->dv_no !== $lastDvNo ? $disbursement->disbursement_date : '' }}</td>
                        <td class="px-2 py-3 text-gray-700 dark:text-gray-300">{{ $disbursement->dv_no !== $lastDvNo ? $disbursement->status : '' }}</td>
                        <td class="px-2 py-3 text-gray-700 dark:text-gray-300">{{ $disbursement->dv_no !== $lastDvNo ? $disbursement->remarks : '' }}</td>
                        <td class="px-2 py-3 text-gray-700 dark:text-gray-300">{{ $disbursement->obligationAmount->appropriation->programs ?? '-' }}</td>
                        <td class="px-2 py-3 text-gray-700 dark:text-gray-300">{{ $disbursement->obligationAmount->appropriation->account_code ?? '-' }}</td>
                        <td class="px-2 py-3 text-gray-700 dark:text-gray-300">{{ $disbursement->obligationAmount->appropriation->description ?? '-' }}</td>
                        <td class="px-2 py-3 text-right text-gray-700 dark:text-gray-300">{{ number_format($disbursement->disbursement_amount, 2) }}</td>
                        <td class="px-2 py-3 text-gray-700 dark:text-gray-300">
                            <div class="relative inline-block text-left">
                                <button onclick="toggleDropdown(this)" 
                                    class="relative text-xs group px-2 py-1.5">
                                    <i class="fas fa-ellipsis-v"></i>
                                    <!-- Tooltip -->
                                    <span class="absolute right-full ml-2 top-1/2 -translate-y-1/2 hidden group-hover:block bg-gray-800 text-white text-[10px] rounded px-2 py-1 whitespace-nowrap z-20">
                                        {{ $disbursement->obligationAmount->appropriation->account_code }} - {{ $disbursement->obligationAmount->appropriation->description }} | {{ number_format($disbursement->disbursement_amount, 2 ) }}
                                    </span>
                                </button>  
                                    <div class="absolute right-0 mt-1 w-40 bg-white border border-gray-300 rounded-lg shadow-lg hidden dropdown-menu z-10 dark:bg-gray-700 dark:border-gray-600">
                                        <button onclick='openEditDisbursementModal(@json($disbursement))' class="w-full text-left px-4 py-2 text-xs text-gray-600 hover:bg-gray-200 dark:text-gray-300 dark:hover:bg-gray-600">
                                            <i class="fas fa-edit mr-2"></i>Edit
                                        </button>
                                        @can('delete disbursement')
                                        <button onclick="openDeleteDisbursementModal({{ $disbursement->id }}, '{{ $disbursement->dv_no }}', '{{ $disbursement->disbursement_date }}', '{{ $disbursement->obligationAmount->appropriation->account_code }}', '{{ $disbursement->disbursement_amount }}')" class="w-full text-left px-4 py-2 text-xs text-red-600 hover:bg-gray-200 dark:text-red-400 dark:hover:bg-gray-600">
                                            <i class="fas fa-trash mr-2"></i>Delete
                                        </button>
                                        @endcan
                                    </div>                          
                            </div>
                        </td> 
                    </tr>
                    @php
                        $lastDvNo = $disbursement->dv_no;
                    @endphp
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    @include('disbursements.modal.create')
    @include('disbursements.modal.delete')
    @include('disbursements.modal.edit')

</x-app-layout>

<script>

    // Function to toggle dropdown menu
    function toggleDropdown(button) {
        let dropdown = button.nextElementSibling;
        let isOpen = !dropdown.classList.contains("hidden"); // true if already visible

        closeAllDropdowns(); // close all first

        if (!isOpen) {
            dropdown.classList.remove("hidden"); // open only if it wasn't open
        }
    }

    function closeAllDropdowns() {
        document.querySelectorAll(".dropdown-menu").forEach(menu => menu.classList.add("hidden"));
    }

    // Close dropdown if click happens outside
    document.addEventListener('click', function(event) {
        if (!event.target.closest('.relative.inline-block')) {
            closeAllDropdowns();
        }
    });

    function closeDropdown() {
        // Example: hide any elements with a class of 'dropdown' or 'autocomplete-dropdown'
        document.querySelectorAll('.dropdown, .autocomplete-dropdown').forEach(drop => {
            drop.classList.add('hidden');
        });
    }
    //Checks if an input has a value and adjusts the text color accordingly
    document.addEventListener("DOMContentLoaded", function() {
        const elements = document.querySelectorAll("input, select");

        elements.forEach(element => {
            updateTextColor(element); // Check initial values

            element.addEventListener("input", function() {
                updateTextColor(this);
            });

            element.addEventListener("change", function() {
                updateTextColor(this);
            });

            element.addEventListener("focus", function() {
                updateTextColor(this);
            });

            // Detect when a field is enabled
            const observer = new MutationObserver(() => updateTextColor(element));
            observer.observe(element, {
                attributes: true,
                attributeFilter: ["disabled"]
            });
        });

        // Handle autofill values after a short delay
        setTimeout(() => {
            elements.forEach(updateTextColor);
        }, 100);

        function updateTextColor(element) {
            if (element.disabled) {
                element.classList.remove("text-gray-900", "dark:text-gray-100");
                element.classList.add("text-gray-400");
            } else if (element.value.trim() !== "") {
                element.classList.remove("text-gray-500", "text-gray-400");
                element.classList.add("text-gray-900", "dark:text-gray-100");
            } else {
                element.classList.remove("text-gray-900", "dark:text-gray-100", "text-gray-400");
            }
        }
    });
</script>