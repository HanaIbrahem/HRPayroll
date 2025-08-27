<header class="sticky top-0 z-20 border-b border-base-300/60 bg-base-100/70 backdrop-blur">
    <div class="flex items-center justify-between gap-3 px-3 py-2 md:px-4">
        <div class="flex items-center gap-2">
            <label for="sidebar-toggle" class="btn btn-square btn-ghost lg:hidden">☰</label>
            <h1 class="text-xl md:text-2xl font-semibold tracking-tight">Dashboard</h1>
        </div>

        <!-- Search -->
        {{-- <div class="hidden md:flex items-center max-w-md w-full">
            <label class="input input-bordered input-sm flex items-center gap-2 w-full">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"
                    class="w-4 h-4 opacity-70">
                    <path fill-rule="evenodd"
                        d="M10.5 3.75a6.75 6.75 0 1 0 4.2 12.06l3.72 3.72a.75.75 0 1 0 1.06-1.06l-3.72-3.72a6.75 6.75 0 0 0-5.26-11zM5.25 10.5a5.25 5.25 0 1 1 10.5 0 5.25 5.25 0 0 1-10.5 0z"
                        clip-rule="evenodd" />
                </svg>
                <input type="text" class="grow" placeholder="Search..." />
            </label>
        </div> --}}

        <div class="flex items-center gap-2 md:gap-3">

            <x-nav.theme />


            <div class="dropdown dropdown-end">
                <div tabindex="0" role="button" class="avatar btn btn-ghost btn-circle">
                    <div class="w-10 rounded-full ring-2 ring-base-300/60">
                        <img src="{{ asset('profileimg.png') }}" />
                    </div>
                </div>
                <ul
                    class="menu dropdown-content mt-4 p-2 shadow bg-base-100 rounded-box w-56 z-30 border border-base-300/60">
                    <li>HI {{auth()->user()->first_name}}</li>
                    <li>
                        <form action="{{ route('logout') }}" method="post">
                            @csrf
                            <input type="submit"  class="font-bold text-error" value="Logout">
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</header>