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
    <span class="inline-flex items-center gap-2">
      {{-- Dashboard (grid) --}}
      <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
        <rect x="3" y="3" width="8" height="8" rx="1.5"></rect>
        <rect x="13" y="3" width="8" height="8" rx="1.5"></rect>
        <rect x="3" y="13" width="8" height="8" rx="1.5"></rect>
        <rect x="13" y="13" width="8" height="8" rx="1.5"></rect>
      </svg>
      <span>Dashboard</span>
    </span>
  </x-nav.link>

  <p class="px-3 pt-3 text-[11px] font-semibold uppercase text-base-content/60">Manage</p>

  <x-nav.link route="department" match="department.*" class="mb-1">
    <span class="inline-flex items-center gap-2">
      {{-- Departments (building) --}}
      <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
        <path d="M3 21h18"></path>
        <rect x="6" y="3" width="6" height="14" rx="1"></rect>
        <rect x="14" y="7" width="4" height="10" rx="1"></rect>
        <path d="M8 7h2M8 10h2M8 13h2"></path>
      </svg>
      <span>Departments</span>
    </span>
  </x-nav.link>

  <x-nav.link route="employee" match="employee.*" class="mb-1">
    <span class="inline-flex items-center gap-2">
      {{-- Employees (users) --}}
      <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
        <path d="M16 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
        <circle cx="10" cy="7" r="3"></circle>
        <path d="M20 21v-2a3 3 0 0 0-2-2"></path>
        <path d="M17 3a3 3 0 0 1 0 6"></path>
      </svg>
      <span>Employees</span>
    </span>
  </x-nav.link>

  <x-nav.link route="zone" match="zone.*" class="mb-1">
    <span class="inline-flex items-center gap-2">
      {{-- Zone (map pin) --}}
      <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
        <path d="M12 21s-6-4.5-6-10a6 6 0 1 1 12 0c0 5.5-6 10-6 10z"></path>
        <circle cx="12" cy="11" r="2.5"></circle>
      </svg>
      <span>Zone</span>
    </span>
  </x-nav.link>

  <x-nav.link route="" match="a" class="mb-1">
    <span class="inline-flex items-center gap-2">
      {{-- Authentication (shield) --}}
      <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
        <path d="M12 3l7 3v6c0 5-3.5 8.5-7 9-3.5-.5-7-4-7-9V6l7-3z"></path>
        <path d="M10 12l2 2 4-4"></path>
      </svg>
      <span>Authentication</span>
    </span>
  </x-nav.link>

  <x-nav.group title="Checklist" match="checklist.*" class="mb-1">
    <x-nav.link route="checklist" match="Checklist" size="sm">
      <span class="inline-flex items-center gap-2">
        {{-- List (lines) --}}
        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
          <path d="M4 6h16M4 12h16M4 18h10"></path>
        </svg>
        <span>List</span>
      </span>
    </x-nav.link>
    <x-nav.link route="checklist.create" match="Checklist.create" size="sm">
      <span class="inline-flex items-center gap-2">
        {{-- Upload (arrow up tray) --}}
        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
          <path d="M12 16V4"></path>
          <path d="M8 8l4-4 4 4"></path>
          <path d="M4 20h16"></path>
        </svg>
        <span>Upload</span>
      </span>
    </x-nav.link>
  </x-nav.group>
</nav>

        </div>

        <!-- Footer -->
        <div class="p-3 border-t border-base-300/60">
            <a class="text-primary font-bold justify-start w-full rounded-lg">Hi {{trim(auth()->user()->first_name. ' '.auth()->user()->last_name)}}</a>
        </div>
    </aside>
</div>