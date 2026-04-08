<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <!-- Left: Obligations Title -->
            <h3 class="font-semibold text-xl leading-tight dark:text-gray-200">
                {{ __('Purchase Orders') }} (
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

    <!-- Page Content Wrapper with Transition -->
    <div class="page-transition">

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
                @can('create purchase orders')
                <button onclick="openCreateModal()" class="text-blue-600 inline-flex leading-4 tracking-wider items-center hover:text-white border border-blue-600 hover:bg-blue-600 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-xs px-5 py-3 text-center dark:border-blue-500 dark:text-blue-500 dark:hover:text-white dark:hover:bg-blue-600 dark:focus:ring-blue-900">
                    <i class="fas fa-plus text-xl mr-1 -ml-1 w-5 h-5"></i>
                    {{ __('Add Purchase Order') }}
                </button>
                @endcan
            </div>
            <table id="adjustmentsTable" class="text-center w-full text-xs text-left rtl:text-right text-gray-500 dark:text-gray-400 mb-8">
                <thead class="text-center text-xs border-b-2 border-gray-400 text-gray-700 bg-gray-50 border-t-2 dark:bg-gray-700 dark:text-gray-400">
                    <tr>
                        <th class="px-2 py-3 border-gray-300 leading-4 text-gray-600 dark:text-gray-300">PO Number</th>
                        <th class="px-2 py-3 border-gray-300 leading-4 text-gray-600 dark:text-gray-300">Date</th>
                        <th class="px-2 py-3 border-gray-300 leading-4 text-gray-600 dark:text-gray-300">Supplier</th>
                        <th class="px-2 py-3 border-gray-300 leading-4 text-gray-600 dark:text-gray-300">PR Number</th>
                        <th class="px-2 py-3 border-gray-300 leading-4 text-gray-600 dark:text-gray-300">Delivery Period</th>
                        <th class="px-2 py-3 border-gray-300 leading-4 text-gray-600 dark:text-gray-300">Remarks</th>
                        <th class="px-2 py-3 border-gray-300 leading-4 text-gray-600 dark:text-gray-300">Program</th>
                        <th class="px-2 py-3 border-gray-300 leading-4 text-gray-600 dark:text-gray-300">Account Code</th>
                        <th class="px-2 py-3 border-gray-300 leading-4 text-gray-600 dark:text-gray-300">Description</th>
                        <th class="px-2 py-3 border-gray-300 leading-4 text-gray-600 dark:text-gray-300">PO Amount</th>
                        @canany(['edit purchase orders', 'delete purchase orders'])<th class="px-2 py-3 border-gray-300 leading-4 text-gray-600 dark:text-gray-300">Actions</th>@endcanany
                    </tr>
                </thead>
                <tbody>
                   @foreach ($purchase_orders as $purchase_order)
                        @php
                            $isDuplicatePo = $purchase_order->is_duplicate_po ?? false;
                        @endphp
                        <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 border-gray-200 hover:bg-gray-50 dark:hover:bg-gray-600">
                            {{-- PO Number --}} 
                            <td class="px-2 py-3 border-b border-gray-300 text-gray-600 dark:text-gray-300">
                                {{ $isDuplicatePo ? '' : $purchase_order['po_number'] }}
                            </td>
                            {{-- PO Date --}}
                            <td class="px-2 py-3 border-b border-gray-300 text-gray-600 dark:text-gray-300">
                                {{ $isDuplicatePo ? '' : $purchase_order['po_date'] }}
                            </td>
                            {{-- Supplier --}}
                            <td class="px-2 py-3 border-b border-gray-300 text-gray-600 dark:text-gray-300">
                                {{ $isDuplicatePo ? '' : $purchase_order['supplier'] }}
                            </td>
                            {{-- PR No --}}
                            <td class="px-2 py-3 border-b border-gray-300 text-gray-600 dark:text-gray-300">
                                {{ $isDuplicatePo ? '' : $purchase_order['pr_no'] }}
                            </td>
                            {{-- Delivery Period --}}
                            <td class="px-2 py-3 border-b border-gray-300 text-gray-600 dark:text-gray-300">
                                {{ $isDuplicatePo ? '' : $purchase_order['delivery_period'] }}
                            </td>
                            {{-- Remarks --}}
                            <td class="px-2 py-3 border-b border-gray-300 text-gray-600 dark:text-gray-300">
                                {{ $isDuplicatePo ? '' : $purchase_order['po_remarks'] }}
                            </td>
                            {{-- Program --}}
                            <td class="px-2 py-3 border-b border-gray-300 text-gray-600 dark:text-gray-300">
                                {{ $purchase_order->obligationAmount->appropriation->programs ?? '-' }}
                            </td>
                            {{-- Account Code --}}
                            <td class="px-2 py-3 border-b border-gray-300 text-gray-600 dark:text-gray-300">
                                {{ $purchase_order->obligationAmount->appropriation->account_code ?? '-' }}
                            </td>
                            {{-- Description --}}
                            <td class="px-2 py-3 border-b border-gray-300 text-gray-600 dark:text-gray-300">
                                {{ $purchase_order->obligationAmount->appropriation->description ?? '-' }}
                            </td>
                            {{-- PO Amount --}}
                            <td class="px-2 py-3 border-b border-gray-300 text-gray-600 dark:text-gray-300">
                                {{ number_format($purchase_order['po_amount'], 2) }}
                            </td>
                            {{-- Action --}}
                            <td class="px-2 py-3 border-b border-gray-300 text-gray-600 dark:text-gray-300">
                                <div class="relative inline-block text-left">
                                    @canany(['edit purchase orders', 'delete purchase orders'])
                                    <button onclick="toggleDropdown(this)" 
                                            class="relative text-xs group px-2 py-1.5">
                                            <span class="fas fa-ellipsis-v"></span>
                                            <!-- Tooltip -->
                                            <span class="absolute right-full ml-2 top-1/2 -translate-y-1/2 hidden group-hover:block bg-gray-800 text-white text-[10px] rounded px-2 py-1 whitespace-nowrap z-20">
                                                {{ $purchase_order['po_number'] }} | {{ $purchase_order['supplier'] }} | {{ number_format($purchase_order['po_amount'], 2 ) }}
                                            </span>
                                        </button>
                                    <div class="absolute right-0 mt-1 w-40 bg-white border border-gray-300 rounded-lg shadow-lg hidden dropdown-menu z-10 dark:bg-gray-700 dark:border-gray-600">
                                        @can('edit purchase orders')
                                        <!-- <button onclick='openUpdatePaymentModal(@json($purchase_order))' class="w-full text-left px-4 py-2 text-xs text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-600">
                                            <i class="fas fa-pencil-alt mr-2"></i>Update Payment
                                        </button> -->
                                        <button onclick='openEditPurchaseOrderModal(@json($purchase_order))' class="w-full text-left px-4 py-2 text-xs text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-600">
                                            <i class="fas fa-edit mr-2"></i>Edit
                                        </button>
                                        @endcan
                                        @can('delete purchase orders')
                                        <button onclick="openDeletePurchaseOrderModal({{ $purchase_order['id'] }}, '{{ $purchase_order['po_number'] }}', '{{ $purchase_order['po_date'] }}', '{{ $purchase_order->obligationAmount->appropriation->account_code }}', '{{ $purchase_order['po_amount'] }}')" class="w-full text-left px-4 py-2 text-xs text-red-600 hover:bg-gray-100 dark:text-red-400 dark:hover:bg-gray-600">
                                            <i class="fas fa-trash mr-2"></i>Delete
                                        </button>
                                        @endcan
                                    </div>
                                    @endcanany
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>


    @include('purchase_orders.modal.create')
    @include('purchase_orders.modal.edit')
    @include('purchase_orders.modal.delete')
    @include('purchase_orders.modal.update_payment')
    @include('purchase_orders.modal.purchase_order_files')

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