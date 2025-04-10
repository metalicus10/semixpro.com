<nav x-data>
    @auth
        @if(Auth::user()->inRole('admin'))
            <a
                href="{{ route('admin') }}"
                class="rounded-md px-3 py-2 text-black ring-1 ring-transparent transition hover:text-black/70 focus:outline-none focus-visible:ring-[#FF2D20] dark:text-black dark:hover:text-black/80 dark:focus-visible:ring-white"
            >
                Dashboard
            </a>
        @elseif(Auth::user()->inRole('manager'))
            <a
                href="{{ route('manager.manager') }}"
                class="inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 h-10 px-4 py-2 bg-brand-accent hover:bg-brand-accent/90 text-brand-darker"
            >
                Dashboard
            </a>
        @elseif(Auth::user()->inRole('technician'))
            <a
                href="{{ route('technician.technician') }}"
                class="rounded-md px-3 py-2 text-black ring-1 ring-transparent transition hover:text-black/70 focus:outline-none focus-visible:ring-[#FF2D20] dark:text-black dark:hover:text-black/80 dark:focus-visible:ring-white"
            >
                Dashboard
            </a>
        @endif
    @else

            <button
                @click="openLogin = true"
                class="inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 hover:bg-accent h-10 px-4 py-2 text-brand-light hover:text-brand-primary">
                Войти
            </button>


        @if (Route::has('register'))
            <a href="{{ route('register') }}">
                <button
                    class="inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 h-10 px-4 py-2 bg-brand-accent hover:bg-brand-accent/90 text-brand-darker">
                    Регистрация
                </button>
            </a>
        @endif
    @endauth
</nav>
