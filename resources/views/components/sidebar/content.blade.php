<x-perfect-scrollbar
    as="nav"
    aria-label="main"
    class="flex flex-col flex-1 gap-3 px-3">


    <!-- Administrator | Developer roles -->
    @role('Administrator|Developer')
    <x-sidebar.link
        title="Dashboard | Balances"
        href="{{ route('dashboard') }}"
        :isActive="request()->routeIs('dashboard')">
        <x-slot name="icon">
            <i class="fas fa-tachometer-alt text-xl flex-shrink-0 dark:text-gray-100" aria-hidden="true"></i>
        </x-slot>
    </x-sidebar.link>
    <div
        x-transition
        x-show="isSidebarOpen || isSidebarHovered"
        class="text-sm text-gray-900 dark:text-gray-100">
        Registry
    </div>
    <x-sidebar.link
        title="Allotment Class | Account"
        href="{{ route('office_allotment_classes.index') }}"
        :isActive="request()->routeIs('office_allotment_classes.index')">
        <x-slot name="icon">
            <i class="fas fa-folder-plus text-xl flex-shrink-0 dark:text-gray-100" aria-hidden="true"></i>
        </x-slot>
    </x-sidebar.link>
    <x-sidebar.link
        title="Obligation"
        href="{{ route('obligations.index') }}"
        :isActive="request()->routeIs('obligations.index')">
        <x-slot name="icon">
            <i class="fas fa-list-check text-xl flex-shrink-0 dark:text-gray-100" aria-hidden="true"></i>
        </x-slot>
    </x-sidebar.link>
    <x-sidebar.link
        title="Realignment | Augmentation"
        href="{{ route('realignments.index') }}"
        :isActive="request()->routeIs('realignments.index')">
        <x-slot name="icon">
            <i class="fas fa-random text-xl flex-shrink-0 dark:text-gray-100" aria-hidden="true"></i>
        </x-slot>
    </x-sidebar.link>
    <x-sidebar.link
        title="Supplemental | Reversion"
        href="{{ route('supplementals.index') }}"
        :isActive="request()->routeIs('supplementals.index')">
        <x-slot name="icon">
            <i class="fas fa-exchange-alt text-xl flex-shrink-0 dark:text-gray-100" aria-hidden="true"></i>
        </x-slot>
    </x-sidebar.link>
    <!-- <x-sidebar.link
        title="Forward Capital Outlay"
        href="">
        <x-slot name="icon">
            <i class="fas fa-share-from-square text-xl flex-shrink-0 dark:text-gray-100" aria-hidden="true"></i>
        </x-slot>
    </x-sidebar.link> -->

    <x-sidebar.dropdown
        title="Auxiliary"
        :active="request()->routeIs('account_codes.index') || request()->routeIs('offices.index') || request()->routeIs('allotment_classes.index') || request()->routeIs('funds.index') || request()->routeIs('fund_sources.index') || request()->routeIs('sectors.index') || request()->routeIs('programs.index')">
        <x-slot name="icon">
            <i class="fas fa-toolbox text-xl flex-shrink-0 dark:text-gray-100" aria-hidden="true"></i>
        </x-slot>
        <x-sidebar.sublink
            title="Account"
            href="{{ route('account_codes.index') }}"
            :active="request()->routeIs('account_codes.index')">
            <x-slot name="icon">
                <i class="fas fa-stream text-base flex-shrink-0 dark:text-gray-100"></i>
            </x-slot>
        </x-sidebar.sublink>
        <x-sidebar.sublink
            title="Allotment Class"
            href="{{ route('allotment_classes.index') }}"
            :active="request()->routeIs('allotment_classes.index')">
            <x-slot name="icon">
                <i class="fas fa-server text-base flex-shrink-0 dark:text-gray-100"></i>
            </x-slot>
        </x-sidebar.sublink>
        <x-sidebar.sublink
            title="Fund"
            href="{{ route('funds.index') }}"
            :active="request()->routeIs('funds.index')">
            <x-slot name="icon">
                <i class="fas fa-sliders-h text-base flex-shrink-0 dark:text-gray-100"></i>
            </x-slot>
        </x-sidebar.sublink>
        <x-sidebar.sublink
            title="Fund Source"
            href="{{ route('fund_sources.index') }}"
            :active="request()->routeIs('fund_sources.index')">
            <x-slot name="icon">
                <i class="fas fa-landmark text-base flex-shrink-0 dark:text-gray-100"></i>
            </x-slot>
        </x-sidebar.sublink>
        <x-sidebar.sublink
            title="Office"
            href="{{ route('offices.index') }}"
            :active="request()->routeIs('offices.index')">
            <x-slot name="icon">
                <i class="fas fa-qrcode text-base flex-shrink-0 dark:text-gray-100"></i>
            </x-slot>
        </x-sidebar.sublink>
        <x-sidebar.sublink
            title="Program"
            href="{{ route('programs.index') }}"
            :active="request()->routeIs('programs.index')">
            <x-slot name="icon">
                <i class="fas fa-file-alt text-base flex-shrink-0 dark:text-gray-100"></i>
            </x-slot>
        </x-sidebar.sublink>
        <x-sidebar.sublink
            title="Sector"
            href="{{ route('sectors.index') }}"
            :active="request()->routeIs('sectors.index')">
            <x-slot name="icon">
                <i class="fas fa-vector-square text-base flex-shrink-0 dark:text-gray-100"></i>
            </x-slot>
        </x-sidebar.sublink>
    </x-sidebar.dropdown>

    <div
        x-transition
        x-show="isSidebarOpen || isSidebarHovered"
        class="text-sm text-gray-900 dark:text-gray-100">
        Reports / Summaries
    </div>

    <x-sidebar.dropdown
        title="SAAOB"
        :active="request()->routeIs('saaob.index') || request()->routeIs('saaobco.index') || request()->routeIs('saaobfundsector.index') || 
                    request()->routeIs('saaobgfcurrent.index') || request()->routeIs('saaobfundsource.index') || request()->routeIs('saaobgfcurrentsummary.index')">
        <x-slot name="icon">
            <i class="fas fa-paste text-xl flex-shrink-0 dark:text-gray-100" aria-hidden="true"></i>
        </x-slot>

        <x-sidebar.sublink
            title="GF | BeGH & SEF"
            href="{{ route('saaobfundsector.index') }}"
            :active="request()->routeIs('saaobfundsector.index')">
            <x-slot name="icon">
                <i class="fas fa-file-alt text-base flex-shrink-0 dark:text-gray-100"></i>
            </x-slot>
        </x-sidebar.sublink>
        <x-sidebar.sublink
            title="Offices (Current)"
            href="{{ route('saaob.index') }}"
            :active="request()->routeIs('saaob.index')">
            <x-slot name="icon">
                <i class="fas fa-file-contract text-base flex-shrink-0 dark:text-gray-100"></i>
            </x-slot>
        </x-sidebar.sublink>
        <x-sidebar.sublink
            title="Offices (Continuing)"
            href="{{ route('saaobco.index') }}"
            :active="request()->routeIs('saaobco.index')">
            <x-slot name="icon">
                <i class="fas fa-file-signature text-base flex-shrink-0 dark:text-gray-100"></i>
            </x-slot>
        </x-sidebar.sublink>
        <x-sidebar.sublink
            title="Current | Continuing"
            href="{{ route('saaobfundsource.index') }}"
            :active="request()->routeIs('saaobfundsource.index')">
            <x-slot name="icon">
                <i class="fas fa-file-invoice text-base flex-shrink-0 dark:text-gray-100"></i>
            </x-slot>
        </x-sidebar.sublink>
        <x-sidebar.sublink
            title="GF per Sector (Current)"
            href="{{ route('saaobgfcurrent.index') }}"
            :active="request()->routeIs('saaobgfcurrent.index')">
            <x-slot name="icon">
                <i class="fas fa-file-code text-base flex-shrink-0 dark:text-gray-100"></i>
            </x-slot>
        </x-sidebar.sublink>
        <x-sidebar.sublink
            title="GF (Current)"
            href="{{ route('saaobgfcurrentsummary.index') }}"
            :active="request()->routeIs('saaobgfcurrentsummary.index')">
            <x-slot name="icon">
                <i class="fas fa-file-excel text-base flex-shrink-0 dark:text-gray-100"></i>
            </x-slot>
        </x-sidebar.sublink>

    </x-sidebar.dropdown>
    <x-sidebar.dropdown
        title="SAAODB"
        :active="request()->routeIs('saaodboffice.index')">
        <x-slot name="icon">
            <i class="fas fa-copy text-xl flex-shrink-0 dark:text-gray-100" aria-hidden="true"></i>
        </x-slot>
        <x-sidebar.sublink
            title="Offices"
            href="{{ route('saaodboffice.index') }}"
            :active="request()->routeIs('saaodboffice.index')">
            <x-slot name="icon">
                <i class="fas fa-file-contract text-base flex-shrink-0 dark:text-gray-100"></i>
            </x-slot>
        </x-sidebar.sublink>
    </x-sidebar.dropdown>

    <div
        x-transition
        x-show="isSidebarOpen || isSidebarHovered"
        class="text-sm text-gray-900 dark:text-gray-100">
        User Management
    </div>
    <x-sidebar.link
        title="User"
        href="{{ route('users.index') }}"
        :isActive="request()->routeIs('users.index')">
        <x-slot name="icon">
            <i class="fas fa-user-circle text-xl flex-shrink-0 dark:text-gray-100" aria-hidden="true"></i>
        </x-slot>
    </x-sidebar.link>
    <x-sidebar.link
        title="Employee"
        href="{{ route('employees.index') }}"
        :isActive="request()->routeIs('employees.index')">
        <x-slot name="icon">
            <i class="fas fa-users text-xl flex-shrink-0 dark:text-gray-100" aria-hidden="true"></i>
        </x-slot>
    </x-sidebar.link>

    <!-- User | Obligation | Payment roles -->
    @elserole('User|Obligation|Payment')
    <x-sidebar.link
        title="Dashboard | Balances"
        href="{{ route('dashboard') }}"
        :isActive="request()->routeIs('dashboard')">
        <x-slot name="icon">
            <i class="fas fa-tachometer-alt text-xl flex-shrink-0 dark:text-gray-100" aria-hidden="true"></i>
        </x-slot>
    </x-sidebar.link>
    <div
        x-transition
        x-show="isSidebarOpen || isSidebarHovered"
        class="text-sm text-gray-900 dark:text-gray-100">
        Registry
    </div>
    <x-sidebar.link
        title="Allotment Class | Account"
        href="{{ route('office_allotment_classes.index') }}"
        :isActive="request()->routeIs('office_allotment_classes.index')">
        <x-slot name="icon">
            <i class="fas fa-share-from-square text-xl flex-shrink-0 dark:text-gray-100" aria-hidden="true"></i>
        </x-slot>
    </x-sidebar.link>
    <x-sidebar.link
        title="Obligation"
        href="{{ route('obligations.index') }}"
        :isActive="request()->routeIs('obligations.index')">
        <x-slot name="icon">
            <i class="fas fa-list-check text-xl flex-shrink-0 dark:text-gray-100" aria-hidden="true"></i>
        </x-slot>
    </x-sidebar.link>
    <x-sidebar.link
        title="Realignment | Augmentation"
        href="{{ route('realignments.index') }}"
        :isActive="request()->routeIs('realignments.index')">
        <x-slot name="icon">
            <i class="fas fa-random text-xl flex-shrink-0 dark:text-gray-100" aria-hidden="true"></i>
        </x-slot>
    </x-sidebar.link>
    <x-sidebar.dropdown
        title="Auxiliary"
        :active="request()->routeIs('account_codes.index') || request()->routeIs('offices.index') || request()->routeIs('allotment_classes.index') || request()->routeIs('funds.index') || request()->routeIs('fund_sources.index') || request()->routeIs('sectors.index') || request()->routeIs('programs.index')">
        <x-slot name="icon">
            <i class="fas fa-toolbox text-xl flex-shrink-0 dark:text-gray-100" aria-hidden="true"></i>
        </x-slot>
        <x-sidebar.sublink
            title="Account"
            href="{{ route('account_codes.index') }}"
            :active="request()->routeIs('account_codes.index')">
            <x-slot name="icon">
                <i class="fas fa-stream text-base flex-shrink-0 dark:text-gray-100"></i>
            </x-slot>
        </x-sidebar.sublink>
        <x-sidebar.sublink
            title="Allotment Class"
            href="{{ route('allotment_classes.index') }}"
            :active="request()->routeIs('allotment_classes.index')">
            <x-slot name="icon">
                <i class="fas fa-server text-base flex-shrink-0 dark:text-gray-100"></i>
            </x-slot>
        </x-sidebar.sublink>
        <x-sidebar.sublink
            title="Fund"
            href="{{ route('funds.index') }}"
            :active="request()->routeIs('funds.index')">
            <x-slot name="icon">
                <i class="fas fa-sliders-h text-base flex-shrink-0 dark:text-gray-100"></i>
            </x-slot>
        </x-sidebar.sublink>
        <x-sidebar.sublink
            title="Fund Source"
            href="{{ route('fund_sources.index') }}"
            :active="request()->routeIs('fund_sources.index')">
            <x-slot name="icon">
                <i class="fas fa-landmark text-base flex-shrink-0 dark:text-gray-100"></i>
            </x-slot>
        </x-sidebar.sublink>
        <x-sidebar.sublink
            title="Office"
            href="{{ route('offices.index') }}"
            :active="request()->routeIs('offices.index')">
            <x-slot name="icon">
                <i class="fas fa-qrcode text-base flex-shrink-0 dark:text-gray-100"></i>
            </x-slot>
        </x-sidebar.sublink>
        <x-sidebar.sublink
            title="Program"
            href="{{ route('programs.index') }}"
            :active="request()->routeIs('programs.index')">
            <x-slot name="icon">
                <i class="fas fa-file-alt text-base flex-shrink-0 dark:text-gray-100"></i>
            </x-slot>
        </x-sidebar.sublink>
        <x-sidebar.sublink
            title="Sector"
            href="{{ route('sectors.index') }}"
            :active="request()->routeIs('sectors.index')">
            <x-slot name="icon">
                <i class="fas fa-vector-square text-base flex-shrink-0 dark:text-gray-100"></i>
            </x-slot>
        </x-sidebar.sublink>
    </x-sidebar.dropdown>
    @endrole

    <!-- <div
        x-transition
        x-show="isSidebarOpen || isSidebarHovered"
        class="text-sm text-gray-900 dark:text-gray-100"
    >
        Dummy Link
    </div>
    <x-sidebar.dropdown
        title="Buttons"
        :active="Str::startsWith(request()->route()->uri(), 'buttons')"
    >
        <x-slot name="icon">
        <i class="fas fa-th-large text-xl flex-shrink-0 dark:text-gray-100" aria-hidden="true"></i>
        </x-slot>

        <x-sidebar.sublink
            title="Text button"
            href="{{ route('buttons.text') }}"
            :active="request()->routeIs('buttons.text')"
        />
        <x-sidebar.sublink
            title="Icon button"
            href="{{ route('buttons.icon') }}"
            :active="request()->routeIs('buttons.icon')"
        />
        <x-sidebar.sublink
            title="Text with icon"
            href="{{ route('buttons.text-icon') }}"
            :active="request()->routeIs('buttons.text-icon')"
        />
    </x-sidebar.dropdown> -->

</x-perfect-scrollbar>