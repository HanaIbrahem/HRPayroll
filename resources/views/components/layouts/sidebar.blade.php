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
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"
              aria-hidden="true">
              <rect x="3" y="3" width="8" height="8" rx="1.5"></rect>
              <rect x="13" y="3" width="8" height="8" rx="1.5"></rect>
              <rect x="3" y="13" width="8" height="8" rx="1.5"></rect>
              <rect x="13" y="13" width="8" height="8" rx="1.5"></rect>
            </svg>
            <span>Dashboard</span>
          </span>
        </x-nav.link>

        {{-- administrtor routes --}}

        @if (auth()->user()->isRole('admin'))
        <p class="px-3 pt-3 text-[11px] font-semibold uppercase text-base-content/60">Manage</p>

        <x-nav.link route="department" match="department.*" class="mb-1">
          <span class="inline-flex items-center gap-2">
            {{-- Departments (building) --}}
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"
              aria-hidden="true">
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
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"
              aria-hidden="true">
              <path d="M16 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
              <circle cx="10" cy="7" r="3"></circle>
              <path d="M20 21v-2a3 3 0 0 0-2-2"></path>
              <path d="M17 3a3 3 0 0 1 0 6"></path>
            </svg>
            <span>Employees</span>
          </span>
        </x-nav.link>

        <x-nav.link route="location" match="location.*" class="mb-1">
          <span class="inline-flex items-center gap-2">
            {{-- location (map pin) --}}
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none"
              stroke="currentColor" stroke-width="1.5" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round"
                d="M9 20.25l-5.25-2.25V6.75L9 9m0 11.25l6-2.25m-6 2.25V9m6 11.25L20.25 18V6.75L15 9m0 11.25V9M15 9l-6-2.25M9 6.75L15 9" />
            </svg>

            <span>Location</span>
          </span>
        </x-nav.link>

        <x-nav.link route="zone" match="zone.*" class="mb-1">
          <span class="inline-flex items-center gap-2">
            {{-- Zone (map pin) --}}
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"
              aria-hidden="true">
              <path d="M12 21s-6-4.5-6-10a6 6 0 1 1 12 0c0 5.5-6 10-6 10z"></path>
              <circle cx="12" cy="11" r="2.5"></circle>
            </svg>
            <span>Zone</span>
          </span>
        </x-nav.link>

        <x-nav.link route="user" match="user.*" class="mb-1">
          <span class="inline-flex items-center gap-2">
            {{-- Authentication (shield) --}}
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"
              aria-hidden="true">
              <path d="M12 3l7 3v6c0 5-3.5 8.5-7 9-3.5-.5-7-4-7-9V6l7-3z"></path>
              <path d="M10 12l2 2 4-4"></path>
            </svg>
            <span>Authentication</span>
          </span>
        </x-nav.link>
        @endif


        {{-- mnager routes --}}
        @if (auth()->user()->isRole('manager'))
        <x-nav.group title="Checklist" match="checklist.*" class="mb-1">

          <x-slot:icon>
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"
              stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
              <!-- clipboard body -->
              <path d="M8 4h8a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2z" />
              <!-- clip top -->
              <path d="M9 4V3a3 3 0 0 1 6 0v1" />
              <!-- check mark -->
              <path d="M9 13l2 2 4-4" />
            </svg>
          </x-slot:icon>
          <x-nav.link route="checklist" match="Checklist" size="sm">
            <span class="inline-flex items-center gap-2">
              {{-- List (lines) --}}
              <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"
                aria-hidden="true">
                <path d="M4 6h16M4 12h16M4 18h10"></path>
              </svg>
              <span>List</span>
            </span>
          </x-nav.link>
          <x-nav.link route="checklist.create" match="Checklist.create" size="sm">
            <span class="inline-flex items-center gap-2">
              {{-- Upload (arrow up tray) --}}
              <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"
                aria-hidden="true">
                <path d="M12 16V4"></path>
                <path d="M8 8l4-4 4 4"></path>
                <path d="M4 20h16"></path>
              </svg>
              <span>Upload</span>
            </span>
          </x-nav.link>
        </x-nav.group>
        @endif

        {{-- HR routes --}}

        @if (auth()->user()->isRole('hr'))

        <x-nav.link route="hr.pending" match="hr.pending*" class="mb-1">
          <span class="inline-flex items-center gap-2">
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"
              stroke-width="1.5">
              <path d="M7 3h10M7 21h10M7 3c0 4 5 6 5 9s-5 5-5 9M17 3c0 4-5 6-5 9s5 5 5 9" />
            </svg>
            <span>Pending</span>
          </span>
        </x-nav.link>

        <x-nav.link route="hr.checklist" match="hr.checklist*" class="mb-1">
          <span class="inline-flex items-center gap-2">
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"
              stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
              <!-- Person -->
              <circle cx="8" cy="7" r="2"></circle>
              <path d="M4.5 20v-2a5.5 5.5 0 0 1 11 0v2"></path>
              <!-- Location pin -->
              <path d="M16 4c-2.21 0-4 1.79-4 4 0 3.2 4 7 4 7s4-3.8 4-7c0-2.21-1.79-4-4-4z"></path>
              <circle cx="16" cy="8" r="1"></circle>
            </svg>
            <span>Employee Visits</span>
          </span>
        </x-nav.link>





        <x-nav.link route="hr.report" match="hr.report*" class="mb-1">
          <span class="inline-flex items-center gap-2">
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"
              stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
              <!-- Document with fold -->
              <path d="M8 3h6l5 5v11a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2z" />
              <path d="M14 3v5h5" />
              <!-- Bars -->
              <path d="M9 17v-4M12 17v-6M15 17v-3" />
            </svg>
            <span>Reports</span>
          </span>
        </x-nav.link>


        @endif

      </nav>

    </div>

    <!-- Footer -->
    <div class="p-3 border-t border-base-300/60">
      <a class="text-primary font-bold justify-start w-full rounded-lg">Hi {{trim(auth()->user()->first_name. '
        '.auth()->user()->last_name)}}</a>
    </div>
  </aside>
</div>