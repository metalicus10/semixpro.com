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
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="dark font-sans antialiased bg-gray-800">
<div x-data="{
        currentTab: localStorage.getItem('activeSideTab') || 'nomenclatures',
        role: 'manager',
        showSidebar: false,
        setTab(tabName) {
            this.currentTab = tabName;
            localStorage.setItem('activeSideTab', tabName);
        },
        setDefaultTabForRole() {
            if (!localStorage.getItem('activeSideTab')) {
                this.currentTab = this.role === 'manager' ? 'nomenclatures' :
                                  this.role === 'technician' ? 'parts' :
                                  this.role === 'admin' ? 'dashboard' : 'nomenclatures';
                localStorage.setItem('activeSideTab', this.currentTab);
            }
        }
    }" x-init="setDefaultTabForRole()">

    <nav class="fixed top-0 z-50 w-full bg-white border-b border-gray-200 dark:bg-gray-800 dark:border-gray-700">
        <div class="px-3 py-3 lg:px-5 lg:pl-3">
            <div class="flex items-center justify-between">

                <!-- Левая часть: Логотип, название и название меню -->
                <div class="flex items-center md:space-x-0 space-x-2">
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
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                                </svg>
                            </span>
                            <span x-show="showSidebar" class="absolute inset-0 flex items-center justify-center"
                                  x-transition:enter="transition ease-out duration-200"
                                  x-transition:enter-start="opacity-0"
                                  x-transition:enter-end="opacity-100"
                                  x-transition:leave="transition ease-in duration-150"
                                  x-transition:leave-start="opacity-100"
                                  x-transition:leave-end="opacity-0">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </span>
                        </div>
                    </button>
                    <!-- Логотип и название приложения -->
                    <a href="https://semixpro.com" class="flex items-center">
                        <img src="https://flowbite.com/docs/images/logo.svg" class="h-8 me-3" alt="Semixpro Logo"/>
                        <span class="self-center text-xl font-semibold sm:text-2xl whitespace-nowrap dark:text-white uppercase">semixpro</span>
                    </a>
                </div>

                <!-- Правая часть: Меню пользователя -->
                <div x-data="{ open: false }" class="flex items-center">
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
                            <p class="text-sm font-medium text-gray-900 truncate dark:text-gray-300">neil.sims@flowbite.com</p>
                        </div>

                        <!-- Список ссылок -->
                        <ul class="py-1">
                            <li>
                                <a x-on:click="currentTab = 'profile', showSidebar = false"
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
        </div>
    </nav>

    <aside id="logo-sidebar" :class="{'translate-x-0': showSidebar, '-translate-x-full': !showSidebar}"
           class="fixed top-0 left-0 z-40 w-64 h-screen pt-20 transition-transform -translate-x-full bg-white border-r border-gray-200 sm:translate-x-0 dark:bg-gray-800 dark:border-gray-700"
           aria-label="Sidebar">
        <div class="h-full px-1 pb-4 overflow-y-auto bg-white dark:bg-gray-800">
            <ul class="space-y-2 font-medium">
                <!--<li>
                    <a x-on:click="currentTab = 'dashboard', showSidebar = false"
                       class="flex items-center cursor-pointer p-2 text-gray-900 rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group">
                        <svg
                            class="w-5 h-5 text-gray-500 transition duration-75 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white"
                            aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor"
                            viewBox="0 0 22 21">
                            <path
                                d="M16.975 11H10V4.025a1 1 0 0 0-1.066-.998 8.5 8.5 0 1 0 9.039 9.039.999.999 0 0 0-1-1.066h.002Z"/>
                            <path
                                d="M12.5 0c-.157 0-.311.01-.565.027A1 1 0 0 0 11 1.02V10h8.975a1 1 0 0 0 1-.935c.013-.188.028-.374.028-.565A8.51 8.51 0 0 0 12.5 0Z"/>
                        </svg>
                        <span class="ms-3">Dashboard</span>
                    </a>
                </li>-->
                @if(auth()->user()->hasAccess('manage_nomenclature'))
                    <li>
                        <a @click="currentTab = 'nomenclatures'"
                           x-bind:class="currentTab === 'nomenclatures' ? 'bg-gray-200 dark:bg-gray-700 text-gray-900 dark:text-white' : ''"
                           class="flex items-center cursor-pointer p-2 text-gray-900 rounded-lg dark:text-white
                           hover:bg-gray-100 dark:hover:bg-gray-700 group">
                            <svg
                                class="flex-shrink-0 w-5 h-5 text-gray-500 transition duration-75 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white"
                                aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor"
                                viewBox="0 0 18 18">
                                <path
                                    d="M6.143 0H1.857A1.857 1.857 0 0 0 0 1.857v4.286C0 7.169.831 8 1.857 8h4.286A1.857 1.857 0 0 0 8 6.143V1.857A1.857 1.857 0 0 0 6.143 0Zm10 0h-4.286A1.857 1.857 0 0 0 10 1.857v4.286C10 7.169 10.831 8 11.857 8h4.286A1.857 1.857 0 0 0 18 6.143V1.857A1.857 1.857 0 0 0 16.143 0Zm-10 10H1.857A1.857 1.857 0 0 0 0 11.857v4.286C0 17.169.831 18 1.857 18h4.286A1.857 1.857 0 0 0 8 16.143v-4.286A1.857 1.857 0 0 0 6.143 10Zm10 0h-4.286A1.857 1.857 0 0 0 10 11.857v4.286c0 1.026.831 1.857 1.857 1.857h4.286A1.857 1.857 0 0 0 18 16.143v-4.286A1.857 1.857 0 0 0 16.143 10Z"/>
                            </svg>
                            <span class="flex-1 ms-3 whitespace-nowrap">Nomenclatures</span>
                        </a>
                    </li>
                @endif
                @if(auth()->user()->hasAccess('manage_warehouses'))
                    <li>
                        <a @click="currentTab = 'warehouses'"
                           x-bind:class="currentTab === 'warehouses' ? 'bg-gray-200 dark:bg-gray-700 text-gray-900 dark:text-white' : ''"
                           class="flex items-center cursor-pointer p-2 text-gray-900 rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group">
                            <svg
                                class="flex-shrink-0 w-5 h-5 text-gray-500 transition duration-75 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white"
                                aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor"
                                viewBox="0 0 18 18">
                                <path
                                    d="M6.143 0H1.857A1.857 1.857 0 0 0 0 1.857v4.286C0 7.169.831 8 1.857 8h4.286A1.857 1.857 0 0 0 8 6.143V1.857A1.857 1.857 0 0 0 6.143 0Zm10 0h-4.286A1.857 1.857 0 0 0 10 1.857v4.286C10 7.169 10.831 8 11.857 8h4.286A1.857 1.857 0 0 0 18 6.143V1.857A1.857 1.857 0 0 0 16.143 0Zm-10 10H1.857A1.857 1.857 0 0 0 0 11.857v4.286C0 17.169.831 18 1.857 18h4.286A1.857 1.857 0 0 0 8 16.143v-4.286A1.857 1.857 0 0 0 6.143 10Zm10 0h-4.286A1.857 1.857 0 0 0 10 11.857v4.286c0 1.026.831 1.857 1.857 1.857h4.286A1.857 1.857 0 0 0 18 16.143v-4.286A1.857 1.857 0 0 0 16.143 10Z"/>
                            </svg>
                            <span class="flex-1 ms-3 whitespace-nowrap">Warehouses</span>
                        </a>
                    </li>
                @endif
                @if(Auth::user()->inRole('manager'))
                    <li>
                        <a @click="currentTab = 'categories', showSidebar = false"
                           x-bind:class="currentTab === 'categories' ? 'bg-gray-200 dark:bg-gray-700 text-gray-900 dark:text-white' : ''"
                           class="flex items-center cursor-pointer p-2 text-gray-900 rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group">
                            <svg
                                class="flex-shrink-0 w-5 h-5 text-gray-500 transition duration-75 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white"
                                aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor"
                                viewBox="0 0 18 18">
                                <path
                                    d="M6.143 0H1.857A1.857 1.857 0 0 0 0 1.857v4.286C0 7.169.831 8 1.857 8h4.286A1.857 1.857 0 0 0 8 6.143V1.857A1.857 1.857 0 0 0 6.143 0Zm10 0h-4.286A1.857 1.857 0 0 0 10 1.857v4.286C10 7.169 10.831 8 11.857 8h4.286A1.857 1.857 0 0 0 18 6.143V1.857A1.857 1.857 0 0 0 16.143 0Zm-10 10H1.857A1.857 1.857 0 0 0 0 11.857v4.286C0 17.169.831 18 1.857 18h4.286A1.857 1.857 0 0 0 8 16.143v-4.286A1.857 1.857 0 0 0 6.143 10Zm10 0h-4.286A1.857 1.857 0 0 0 10 11.857v4.286c0 1.026.831 1.857 1.857 1.857h4.286A1.857 1.857 0 0 0 18 16.143v-4.286A1.857 1.857 0 0 0 16.143 10Z"/>
                            </svg>
                            <span class="flex-1 ms-3 whitespace-nowrap">Categories</span>
                        </a>
                    </li>
                @endif
                <li>
                    <a @click="currentTab = 'parts'"
                       x-bind:class="currentTab === 'parts' ? 'bg-gray-200 dark:bg-gray-700 text-gray-900 dark:text-white' : ''"
                       class="flex items-center cursor-pointer p-2 text-gray-900 rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group">
                        <svg
                            class="flex-shrink-0 w-5 h-5 text-gray-500 transition duration-75 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white"
                            aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor"
                            viewBox="0 0 20 20">
                            <path
                                d="m17.418 3.623-.018-.008a6.713 6.713 0 0 0-2.4-.569V2h1a1 1 0 1 0 0-2h-2a1 1 0 0 0-1 1v2H9.89A6.977 6.977 0 0 1 12 8v5h-2V8A5 5 0 1 0 0 8v6a1 1 0 0 0 1 1h8v4a1 1 0 0 0 1 1h2a1 1 0 0 0 1-1v-4h6a1 1 0 0 0 1-1V8a5 5 0 0 0-2.582-4.377ZM6 12H4a1 1 0 0 1 0-2h2a1 1 0 0 1 0 2Z"/>
                        </svg>
                        <span class="flex-1 ms-3 whitespace-nowrap">Parts</span>
                    </a>
                </li>
                @if(Auth::user()->inRole('manager'))
                    <li>
                        <a @click="currentTab = 'brands'"
                           x-bind:class="currentTab === 'brands' ? 'bg-gray-200 dark:bg-gray-700 text-gray-900 dark:text-white' : ''"
                           class="flex items-center cursor-pointer p-2 text-gray-900 rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group">
                            <svg
                                class="flex-shrink-0 w-5 h-5 text-gray-500 transition duration-75 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white"
                                aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor"
                                viewBox="0 0 20 18">
                                <path
                                    d="M14 2a3.963 3.963 0 0 0-1.4.267 6.439 6.439 0 0 1-1.331 6.638A4 4 0 1 0 14 2Zm1 9h-1.264A6.957 6.957 0 0 1 15 15v2a2.97 2.97 0 0 1-.184 1H19a1 1 0 0 0 1-1v-1a5.006 5.006 0 0 0-5-5ZM6.5 9a4.5 4.5 0 1 0 0-9 4.5 4.5 0 0 0 0 9ZM8 10H5a5.006 5.006 0 0 0-5 5v2a1 1 0 0 0 1 1h11a1 1 0 0 0 1-1v-2a5.006 5.006 0 0 0-5-5Z"/>
                            </svg>
                            <span class="flex-1 ms-3 whitespace-nowrap">Brands</span>
                        </a>
                    </li>
                @endif
                @if(Auth::user()->inRole('manager'))
                    <li>
                        <a @click="currentTab = 'suppliers'"
                           x-bind:class="currentTab === 'suppliers' ? 'bg-gray-200 dark:bg-gray-700 text-gray-900 dark:text-white' : ''"
                           class="flex items-center cursor-pointer p-2 text-gray-900 rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group">
                            <svg
                                class="flex-shrink-0 w-5 h-5 text-gray-500 transition duration-75 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white"
                                aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor"
                                viewBox="0 0 20 18">
                                <path
                                    d="M14 2a3.963 3.963 0 0 0-1.4.267 6.439 6.439 0 0 1-1.331 6.638A4 4 0 1 0 14 2Zm1 9h-1.264A6.957 6.957 0 0 1 15 15v2a2.97 2.97 0 0 1-.184 1H19a1 1 0 0 0 1-1v-1a5.006 5.006 0 0 0-5-5ZM6.5 9a4.5 4.5 0 1 0 0-9 4.5 4.5 0 0 0 0 9ZM8 10H5a5.006 5.006 0 0 0-5 5v2a1 1 0 0 0 1 1h11a1 1 0 0 0 1-1v-2a5.006 5.006 0 0 0-5-5Z"/>
                            </svg>
                            <span class="flex-1 ms-3 whitespace-nowrap">Suppliers</span>
                        </a>
                    </li>
                @endif
                @if(Auth::user()->inRole('manager'))
                    <li>
                        <a @click="currentTab = 'statistics'"
                           x-bind:class="currentTab === 'statistics' ? 'bg-gray-200 dark:bg-gray-700 text-gray-900 dark:text-white' : ''"
                           class="flex items-center cursor-pointer p-2 text-gray-900 rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group">
                            <svg
                                class="flex-shrink-0 w-5 h-5 text-gray-500 transition duration-75 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white"
                                aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor"
                                viewBox="0 0 18 20">
                                <path
                                    d="M17 5.923A1 1 0 0 0 16 5h-3V4a4 4 0 1 0-8 0v1H2a1 1 0 0 0-1 .923L.086 17.846A2 2 0 0 0 2.08 20h13.84a2 2 0 0 0 1.994-2.153L17 5.923ZM7 9a1 1 0 0 1-2 0V7h2v2Zm0-5a2 2 0 1 1 4 0v1H7V4Zm6 5a1 1 0 1 1-2 0V7h2v2Z"/>
                            </svg>
                            <span class="flex-1 ms-3 whitespace-nowrap">Statistics</span>
                        </a>
                    </li>
                @endif
                @if(Auth::user()->inRole('manager'))
                    <li>
                        <a @click="currentTab = 'technicians'"
                           x-bind:class="currentTab === 'technicians' ? 'bg-gray-200 dark:bg-gray-700 text-gray-900 dark:text-white' : ''"
                           class="flex items-center cursor-pointer p-2 text-gray-900 rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group">
                            <svg
                                class="flex-shrink-0 w-5 h-5 text-gray-500 transition duration-75 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white"
                                aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 18 16">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                      stroke-width="2"
                                      d="M1 8h11m0 0L8 4m4 4-4 4m4-11h3a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2h-3"/>
                            </svg>
                            <span class="flex-1 ms-3 whitespace-nowrap">Technicians</span>
                        </a>
                    </li>
                @endif
            </ul>
        </div>
    </aside>


    <div class="p-1 sm:ml-64">
        <div class="p-1 border-2 border-gray-200 border-dashed rounded-lg dark:border-gray-600 bg-gray-900 mt-14">
            <livewire:global-notification />
            {{ $slot }}
        </div>
    </div>
</div>

<!-- Общий Lightbox для всех изображений -->
<livewire:components.lightbox />

@livewireScripts
</body>
</html>
