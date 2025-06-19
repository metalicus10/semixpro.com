<!DOCTYPE html>
<html class="dark" lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet"/>

    <!-- Scripts -->
    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="no-scroll bg-background font-sans antialiased">
<div x-data="() => ({
        currentTab: localStorage.getItem('activeSideTab') || 'nomenclatures',
        role: 'manager',
        showSidebar: false,
        showInventory: false,
        setTab(tabName) {
            this.currentTab = tabName;
            localStorage.setItem('activeSideTab', tabName);
        },
        setDefaultTabForRole() {
            if (!localStorage.getItem('activeSideTab')) {
                const tabs = {
                    manager: 'nomenclatures',
                    technician: 'parts',
                    admin: 'dashboard'
                };
                this.currentTab = tabs[this.role] || 'nomenclatures';
                localStorage.setItem('activeSideTab', this.currentTab);
            }
        },
    })" x-init="setDefaultTabForRole()" class="flex flex-col h-screen">
    <section
        class="top-0 z-50 relative w-full border-b border-gray-200 dark:border-gray-600 dark:bg-brand-background border-brand-border">
        <div class="flex items-center justify-between py-3 px-6 lg:px-5 lg:pl-3">

            <!-- Левая часть: Логотип, название и название меню -->
            <div class="flex items-center space-x-2 md:space-x-0">
                <!-- Кнопка для открытия боковой панели на мобильных устройствах -->
                <button @click="showSidebar = !showSidebar"
                        class="py-1 px-2 bg-transparent border border-gray-600 text-white z-20 top-4 rounded left-4 md:hidden">
                    <div class="relative w-5 h-5">
                            <span x-show="!showSidebar" class="absolute inset-0 flex items-center justify-center"
                                  x-transition:enter="transition ease-out duration-200"
                                  x-transition:enter-start="opacity-0"
                                  x-transition:enter-end="opacity-100"
                                  x-transition:leave="transition ease-in duration-150"
                                  x-transition:leave-start="opacity-100"
                                  x-transition:leave-end="opacity-0">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24"
                                     stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M4 6h16M4 12h16M4 18h16"/>
                                </svg>
                            </span>
                        <span x-show="showSidebar" class="absolute inset-0 flex items-center justify-center"
                              x-transition:enter="transition ease-out duration-200"
                              x-transition:enter-start="opacity-0"
                              x-transition:enter-end="opacity-100"
                              x-transition:leave="transition ease-in duration-150"
                              x-transition:leave-start="opacity-100"
                              x-transition:leave-end="opacity-0">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24"
                                     stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </span>
                    </div>
                </button>
                <!-- Логотип и название приложения -->
                <a href="https://semixpro.com" class="flex items-center">
                    <img src="https://flowbite.com/docs/images/logo.svg" class="h-8 me-3" alt="Semixpro Logo"/>
                    <span
                        class="self-center text-xl font-semibold sm:text-2xl whitespace-nowrap dark:text-white uppercase">semixpro</span>
                </a>
            </div>

            <!-- Центральное главное меню -->
            <nav class="flex items-center space-x-1 absolute left-1/2 transform -translate-x-1/2 dark:text-brand-light">
                @if(Auth::user()->inRole('manager'))
                    <button @click="setTab('dashboard')"
                            x-bind:class="currentTab == 'dashboard' ? 'bg-gray-200 dark:bg-gray-700 text-gray-900 dark:text-white' : ''"
                            class="justify-center text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none
                            focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none cursor-pointer
                            disabled:opacity-50 hover:bg-accent hover:text-accent-foreground h-9 rounded-md flex items-center px-3 py-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                             stroke="currentColor" stroke-width="2"
                             stroke-linecap="round" stroke-linejoin="round"
                             class="lucide lucide-layout-dashboard h-5 w-5"
                        >
                            <rect width="7" height="9" x="3" y="3" rx="1"></rect>
                            <rect width="7" height="5" x="14" y="3" rx="1"></rect>
                            <rect width="7" height="9" x="14" y="12" rx="1"></rect>
                            <rect width="7" height="5" x="3" y="16" rx="1"></rect>
                        </svg>
                        <span class="ml-2">Dashboard</span>
                    </button>
                    <div class="relative inline-block">
                        <div class="flex items-center">
                            <button @click="showInventory = !showInventory" @click.away="showInventory = false;" class="justify-center text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none
                        focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none cursor-pointer
                        disabled:opacity-50 hover:bg-accent hover:text-accent-foreground h-9 rounded-md flex items-center px-3 py-2"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                     fill="none" stroke="currentColor"
                                     stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                     class="lucide lucide-package h-5 w-5"
                                >
                                    <path
                                        d="M11 21.73a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73z"></path>
                                    <path d="M12 22V12"></path>
                                    <path d="m3.3 7 7.703 4.734a2 2 0 0 0 1.994 0L20.7 7"></path>
                                    <path d="m7.5 4.27 9 5.15"></path>
                                </svg>
                                <span class="ml-2">Inventory</span>
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                     fill="none" stroke="currentColor"
                                     stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                     class="lucide lucide-chevron-down h-4 w-4 ml-1"
                                >
                                    <path d="m6 9 6 6 6-6"></path>
                                </svg>
                            </button>
                        </div>
                        <div x-show="showInventory" x-cloak
                             class="absolute z-50 mt-1 w-48 left-0 rounded-md bg-gray-800 shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none">
                            <div class="py-1">
                                @if(auth()->user()->hasAccess('manage_nomenclature'))
                                    <a @click="setTab('nomenclatures')"
                                       x-bind:class="currentTab === 'nomenclatures' ? 'bg-gray-200 dark:bg-gray-700 text-gray-900 dark:text-white' : ''"
                                       class="block px-4 py-2 text-sm text-gray-200 hover:bg-gray-700 hover:text-white cursor-pointer">
                                        <span class="whitespace-nowrap">Nomenclatures</span>
                                    </a>
                                @endif
                                @if(auth()->user()->hasAccess('manage_warehouses'))
                                    <a @click="setTab('warehouses')"
                                       x-bind:class="currentTab === 'warehouses' ? 'bg-gray-200 dark:bg-gray-700 text-gray-900 dark:text-white' : ''"
                                       class="block px-4 py-2 text-sm text-gray-200 hover:bg-gray-700 hover:text-white cursor-pointer">
                                        <span class="whitespace-nowrap">Warehouses</span>
                                    </a>
                                @endif
                                @if(Auth::user()->inRole('manager'))
                                    <a @click="setTab('categories'), showSidebar = false"
                                       x-bind:class="currentTab === 'categories' ? 'bg-gray-200 dark:bg-gray-700 text-gray-900 dark:text-white' : ''"
                                       class="block px-4 py-2 text-sm text-gray-200 hover:bg-gray-700 hover:text-white cursor-pointer">
                                        <span class="whitespace-nowrap">Categories</span>
                                    </a>
                                @endif
                                @if(Auth::user()->inRole('manager'))
                                    <a @click="setTab('suppliers')"
                                       x-bind:class="currentTab === 'suppliers' ? 'bg-gray-200 dark:bg-gray-700 text-gray-900 dark:text-white' : ''"
                                       class="block px-4 py-2 text-sm text-gray-200 hover:bg-gray-700 hover:text-white cursor-pointer">
                                        <span class="whitespace-nowrap">Suppliers</span>
                                    </a>
                                @endif
                                @if(Auth::user()->inRole('manager'))
                                    <a @click="setTab('brands')"
                                       x-bind:class="currentTab === 'brands' ? 'bg-gray-200 dark:bg-gray-700 text-gray-900 dark:text-white' : ''"
                                       class="block px-4 py-2 text-sm text-gray-200 hover:bg-gray-700 hover:text-white cursor-pointer">
                                        <span class="whitespace-nowrap">Brands</span>
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif
                <div class="relative inline-block">
                    <div class="flex items-center">
                        <button @click="setTab('parts')"
                                x-bind:class="currentTab == 'parts' ? 'bg-gray-200 dark:bg-gray-700 text-gray-900 dark:text-white' : ''"
                                class="justify-center text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none
                            focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none cursor-pointer
                            disabled:opacity-50 hover:bg-accent hover:text-accent-foreground h-9 rounded-md flex items-center px-3 py-2"
                        >
                            @include('icons.parts')
                            <span class="ms-2">Parts</span>
                        </button>
                    </div>
                </div>
                @if(Auth::user()->inRole('manager'))
                    <div class="relative inline-block">
                        <div class="flex items-center cursor-pointer">
                            <button @click="setTab('statistics')"
                                    x-bind:class="currentTab === 'statistics' ? 'bg-gray-200 dark:bg-gray-700 text-gray-900 dark:text-white' : ''"
                                    class="justify-center text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none
                        focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50
                        hover:bg-accent hover:text-accent-foreground h-9 rounded-md flex items-center px-3 py-2">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                     fill="none" stroke="currentColor"
                                     stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                     class="lucide lucide-file-text h-5 w-5"
                                >
                                    <path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"></path>
                                    <path d="M14 2v4a2 2 0 0 0 2 2h4"></path>
                                    <path d="M10 9H8"></path>
                                    <path d="M16 13H8"></path>
                                    <path d="M16 17H8"></path>
                                </svg>
                                <span class="ml-2">Statistics</span>
                            </button>
                        </div>
                    </div>
                    <a href="#">
                        <button class="justify-center text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none
                    focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50
                    hover:bg-accent hover:text-accent-foreground h-9 rounded-md flex items-center px-3 py-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                 fill="none"
                                 stroke="currentColor"
                                 stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                 class="lucide lucide-chart-no-axes-column-increasing h-5 w-5"
                            >
                                <line x1="12" x2="12" y1="20" y2="10"></line>
                                <line x1="18" x2="18" y1="20" y2="4"></line>
                                <line x1="6" x2="6" y1="20" y2="16"></line>
                            </svg>
                            <span class="ml-2">Report</span>
                        </button>
                    </a>
                    <a href="#">
                        <button class="justify-center text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2
                    focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 hover:bg-accent hover:text-accent-foreground h-9
                    rounded-md flex items-center px-3 py-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                 fill="none"
                                 stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                 class="lucide lucide-file-spreadsheet h-5 w-5"
                            >
                                <path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"></path>
                                <path d="M14 2v4a2 2 0 0 0 2 2h4"></path>
                                <path d="M8 13h2"></path>
                                <path d="M14 13h2"></path>
                                <path d="M8 17h2"></path>
                                <path d="M14 17h2"></path>
                            </svg>
                            <span class="ml-2">Document</span></button>
                    </a>
                    <div class="relative inline-block">
                        <div class="flex items-center cursor-pointer">
                            <button @click="setTab('technicians')"
                                    x-bind:class="currentTab === 'technicians' ? 'bg-gray-200 dark:bg-gray-700 text-gray-900 dark:text-white' : ''"
                                    class="justify-center text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none
                        focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50
                        hover:bg-accent hover:text-accent-foreground h-9 rounded-md flex items-center px-3 py-2">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                     fill="none" stroke="currentColor"
                                     stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                     class="lucide lucide-file-text h-5 w-5"
                                >
                                    <path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"></path>
                                    <path d="M14 2v4a2 2 0 0 0 2 2h4"></path>
                                    <path d="M10 9H8"></path>
                                    <path d="M16 13H8"></path>
                                    <path d="M16 17H8"></path>
                                </svg>
                                <span class="ml-2">Technicians</span>
                            </button>
                        </div>
                    </div>
                @endif
            </nav>

            <!-- Правая часть: Меню пользователя -->
            <div x-data="{ open: false }" class="flex items-center gap-3">
                <livewire:global-notification/>
                <!-- Кнопка открытия меню -->
                <button @click="open = !open"
                        class="flex text-sm bg-gray-800 rounded-full focus:ring-4 focus:ring-gray-300 dark:focus:ring-gray-600">
                    <span class="sr-only">Open user menu</span>
                    <img class="w-8 h-8 rounded-full"
                         src="https://flowbite.com/docs/images/people/profile-picture-5.jpg"
                         alt="user photo">
                </button>

                <!-- Выпадающее меню пользователя -->
                <div x-show="open" @click.away="open = false" x-transition x-cloak
                     class="z-50 absolute top-12 right-0 my-4 text-base list-none bg-white divide-y divide-gray-100 rounded shadow dark:bg-gray-700 dark:divide-gray-600">
                    <!-- Информация о пользователе -->
                    <div class="px-4 py-3">
                        <p class="text-sm text-gray-900 dark:text-white">Neil Sims</p>
                        <p class="text-sm font-medium text-gray-900 truncate dark:text-gray-300">
                            neil.sims@flowbite.com</p>
                    </div>

                    <!-- Список ссылок -->
                    <ul class="py-1">
                        <li>
                            <a @click="setTab('profile'); open = false"
                               class="block px-4 cursor-pointer py-2 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-600 dark:hover:text-white">
                                {{ __('Profile') }}
                            </a>
                        </li>
                        <li>
                            @livewire('logout')
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <div
        class="flex flex-col h-screen min-h-0 overflow-y-auto m-1 p-1 border-2 border-gray-200 border-dashed rounded-lg dark:border-gray-600 bg-gray-900">
        <livewire:notification/>
        {{ $slot }}
    </div>

</div>

<!-- Общий Lightbox для всех изображений -->
<livewire:components.lightbox/>

@livewireScripts
</body>
</html>
