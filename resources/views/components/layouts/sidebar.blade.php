<div class="drawer-side">
    <label for="sidebar-toggle" class="drawer-overlay"></label>

    <aside class="w-64 h-full bg-base-100 shadow-lg flex flex-col border-r border-base-300/60">
        <div class="p-4 text-xl font-bold tracking-tight">

            <x-logo />
        </div>


        <!-- Navigation -->
        <div class="flex-1 overflow-y-auto sidebar-scroll ">
            <nav class="p-3 space-y-1 text-sm">
                <p class="px-3 pt-3 text-[11px] font-semibold uppercase text-base-content/60">Main</p>

                <x-nav.link route="dashboard" match="dashboard" class="mb-1">
                    📊 Dashboard
                </x-nav.link>

                <p class="px-3 pt-3 text-[11px] font-semibold uppercase text-base-content/60">Manage</p>

                 <x-nav.link route="department"  match="department.*" class="mb-1">
                    Departments
                </x-nav.link>

                <x-nav.link route="employee"  match="employee.*" class="mb-1">
                    Employees
                </x-nav.link>

                 <x-nav.link route="zone"  match="zone.*" class="mb-1">
                    Zone
                </x-nav.link>

                  <x-nav.link route="checklist"  match="checklist.*" class="mb-1">
                    Checklist
                </x-nav.link>
                <x-nav.group title="⚙️ Management" :match="['users.*','orders.*','reports.*']" class="mb-1">
                    <x-nav.link  match="users.*" size="sm">👥 Users</x-nav.link>
                    <x-nav.link  match="orders.*" size="sm">📦 Orders</x-nav.link>
                    <x-nav.link  match="reports.*" size="sm">📊 Reports</x-nav.link>
                </x-nav.group>

            </nav>
        </div>

        <!-- Footer -->
        <div class="p-3 border-t border-base-300/60">
            <a class="btn btn-ghost justify-start w-full rounded-lg">🔧 Settings</a>
        </div>
    </aside>
</div>