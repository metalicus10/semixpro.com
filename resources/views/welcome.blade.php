<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Laravel</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />

        <!-- Styles -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="antialiased font-sans">
    <!-- Header -->
    <header class="fixed top-0 left-0 w-full z-50 bg-white shadow">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex justify-between items-center">
            <div class="text-xl font-bold tracking-tight">
                SEMIX
            </div>
            <nav class="hidden md:flex space-x-8 font-medium">
                <a href="#features" class="hover:text-blue-600 transition">Features</a>
                <a href="#pricing" class="hover:text-blue-600 transition">Pricing</a>
                <a href="#faq" class="hover:text-blue-600 transition">FAQ</a>
                <a href="#contact" class="hover:text-blue-600 transition">Contact</a>
            </nav>
            <div class="md:hidden">
                <!-- Mobile Menu Toggle -->
                <button @click="open = !open" x-data="{ open: false }" class="text-gray-700 focus:outline-none">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
                         viewBox="0 0 24 24" stroke="currentColor">
                        <path x-show="!open" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M4 6h16M4 12h16M4 18h16" />
                        <path x-show="open" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>

                <!-- Mobile Menu -->
                <div x-show="open" x-transition class="absolute top-16 left-0 w-full bg-white shadow-md">
                    <div class="flex flex-col space-y-4 px-6 py-4">
                        <a href="#features" class="hover:text-blue-600">Features</a>
                        <a href="#pricing" class="hover:text-blue-600">Pricing</a>
                        <a href="#faq" class="hover:text-blue-600">FAQ</a>
                        <a href="#contact" class="hover:text-blue-600">Contact</a>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="min-h-screen flex items-center justify-center px-6 pt-32 pb-20 bg-gradient-to-b from-gray-50 to-white">
        <div class="max-w-4xl mx-auto text-center">
            <h1 class="text-4xl sm:text-5xl md:text-6xl font-extrabold leading-tight mb-6">
                Boost your technician team with <span class="text-blue-600">Semix</span>
            </h1>
            <p class="text-lg text-gray-600 mb-8">
                A powerful and easy-to-use solution for parts management, team coordination, and warehouse tracking.
            </p>
            <div class="flex justify-center space-x-4">
                <a href="#contact" class="px-6 py-3 bg-blue-600 text-white rounded-xl shadow hover:bg-blue-700 transition">Get Started</a>
                <a href="#features" class="px-6 py-3 bg-gray-200 text-gray-900 rounded-xl hover:bg-gray-300 transition">Learn More</a>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="bg-white py-20 px-6">
        <div class="max-w-6xl mx-auto text-center mb-12">
            <h2 class="text-3xl sm:text-4xl font-bold mb-4">
                Powerful features to supercharge your workflow
            </h2>
            <p class="text-gray-600 max-w-2xl mx-auto">
                Our platform helps technicians, managers and teams streamline the spare parts process and stay efficient.
            </p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-10 max-w-6xl mx-auto">
            <!-- Feature item -->
            <div class="bg-gray-50 p-6 rounded-2xl shadow hover:shadow-md transition">
                <div class="text-blue-600 mb-4">
                    <svg class="w-10 h-10 mx-auto" fill="none" stroke="currentColor" stroke-width="2"
                         viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M9.75 3v2.25M14.25 3v2.25M3 18.75V7.5A2.25 2.25 0 015.25 5.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0h18" />
                    </svg>
                </div>
                <h3 class="text-xl font-semibold mb-2">Inventory Control</h3>
                <p class="text-gray-600">Track parts availability in real time and reduce losses across warehouses.</p>
            </div>

            <!-- Feature item -->
            <div class="bg-gray-50 p-6 rounded-2xl shadow hover:shadow-md transition">
                <div class="text-blue-600 mb-4">
                    <svg class="w-10 h-10 mx-auto" fill="none" stroke="currentColor" stroke-width="2"
                         viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h3 class="text-xl font-semibold mb-2">Full Visibility</h3>
                <p class="text-gray-600">Managers see everything — from technician usage to restocking trends.</p>
            </div>

            <!-- Feature item -->
            <div class="bg-gray-50 p-6 rounded-2xl shadow hover:shadow-md transition">
                <div class="text-blue-600 mb-4">
                    <svg class="w-10 h-10 mx-auto" fill="none" stroke="currentColor" stroke-width="2"
                         viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M5.121 17.804A13.937 13.937 0 0112 15c2.21 0 4.29.534 6.121 1.477M15 10a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                </div>
                <h3 class="text-xl font-semibold mb-2">Role-Based Access</h3>
                <p class="text-gray-600">Technicians see only what they need. Managers get control of distribution.</p>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="bg-blue-600 text-white py-20 px-6">
        <div class="max-w-4xl mx-auto text-center">
            <h2 class="text-3xl sm:text-4xl font-bold mb-6">
                Ready to simplify your parts management?
            </h2>
            <p class="text-lg text-blue-100 mb-8">
                Join Semix today and give your technicians the tools they need to stay efficient and accurate.
            </p>
            <a href="#contact" class="inline-block bg-white text-blue-600 px-6 py-3 rounded-xl font-semibold shadow hover:bg-gray-100 transition">
                Get Started Now
            </a>
        </div>
    </section>

    <!-- SpendingOverview (Аналитика склада) -->
    <section class="bg-gray-50 py-20 px-6">
        <div class="max-w-6xl mx-auto">
            <div class="text-center mb-12">
                <h2 class="text-3xl sm:text-4xl font-bold mb-4">Warehouse Analytics</h2>
                <p class="text-gray-600 max-w-2xl mx-auto">
                    Gain insights into your inventory flow, part consumption, and technician efficiency — all in one place.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-10">
                <!-- Total Parts in Stock -->
                <div class="bg-white rounded-2xl shadow p-6 text-center">
                    <h3 class="text-lg font-semibold mb-2">Total Parts in Stock</h3>
                    <p class="text-4xl font-bold text-blue-600">12,457</p>
                    <p class="text-gray-500 mt-2">Updated 5 mins ago</p>
                </div>

                <!-- Monthly Usage -->
                <div class="bg-white rounded-2xl shadow p-6 text-center">
                    <h3 class="text-lg font-semibold mb-2">Monthly Usage</h3>
                    <p class="text-4xl font-bold text-green-600">3,872</p>
                    <p class="text-gray-500 mt-2">Compared to last month: +12%</p>
                </div>

                <!-- Low Stock Alerts -->
                <div class="bg-white rounded-2xl shadow p-6 text-center">
                    <h3 class="text-lg font-semibold mb-2">Low Stock Alerts</h3>
                    <p class="text-4xl font-bold text-red-500">38</p>
                    <p class="text-gray-500 mt-2">Restock recommended</p>
                </div>
            </div>

            <!-- Progress Overview -->
            <div class="mt-16 grid grid-cols-1 lg:grid-cols-2 gap-8">
                <div class="bg-white p-6 rounded-2xl shadow">
                    <h4 class="text-xl font-semibold mb-4">Stock Consumption Rate</h4>
                    <div class="space-y-4">
                        <div>
                            <p class="text-sm mb-1">Technicians</p>
                            <div class="w-full bg-gray-200 rounded-full h-3">
                                <div class="bg-blue-500 h-3 rounded-full" style="width: 72%;"></div>
                            </div>
                        </div>
                        <div>
                            <p class="text-sm mb-1">Managers</p>
                            <div class="w-full bg-gray-200 rounded-full h-3">
                                <div class="bg-green-500 h-3 rounded-full" style="width: 58%;"></div>
                            </div>
                        </div>
                        <div>
                            <p class="text-sm mb-1">Central Warehouse</p>
                            <div class="w-full bg-gray-200 rounded-full h-3">
                                <div class="bg-purple-500 h-3 rounded-full" style="width: 86%;"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-2xl shadow">
                    <h4 class="text-xl font-semibold mb-4">Restock Frequency</h4>
                    <ul class="space-y-3 text-sm text-gray-700">
                        <li class="flex justify-between"><span>Warehouse A</span><span class="text-blue-600 font-medium">Every 14 days</span></li>
                        <li class="flex justify-between"><span>Warehouse B</span><span class="text-blue-600 font-medium">Every 21 days</span></li>
                        <li class="flex justify-between"><span>Warehouse C</span><span class="text-blue-600 font-medium">Every 30 days</span></li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- Partners Section -->
    <section class="bg-white py-20 px-6">
        <div class="max-w-6xl mx-auto text-center mb-12">
            <h2 class="text-3xl sm:text-4xl font-bold mb-4">Trusted by industry leaders</h2>
            <p class="text-gray-600 max-w-2xl mx-auto">
                SemixPro is used by teams across the U.S. to manage warehouse logistics with confidence and speed.
            </p>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-8 items-center justify-items-center max-w-5xl mx-auto">
            <img src="https://dummyimage.com/120x40/ccc/fff&text=Partner+1" alt="Partner 1" class="grayscale hover:grayscale-0 transition" />
            <img src="https://dummyimage.com/120x40/ccc/fff&text=Partner+2" alt="Partner 2" class="grayscale hover:grayscale-0 transition" />
            <img src="https://dummyimage.com/120x40/ccc/fff&text=Partner+3" alt="Partner 3" class="grayscale hover:grayscale-0 transition" />
            <img src="https://dummyimage.com/120x40/ccc/fff&text=Partner+4" alt="Partner 4" class="grayscale hover:grayscale-0 transition" />
            <img src="https://dummyimage.com/120x40/ccc/fff&text=Partner+5" alt="Partner 5" class="grayscale hover:grayscale-0 transition" />
            <img src="https://dummyimage.com/120x40/ccc/fff&text=Partner+6" alt="Partner 6" class="grayscale hover:grayscale-0 transition" />
            <img src="https://dummyimage.com/120x40/ccc/fff&text=Partner+7" alt="Partner 7" class="grayscale hover:grayscale-0 transition" />
            <img src="https://dummyimage.com/120x40/ccc/fff&text=Partner+8" alt="Partner 8" class="grayscale hover:grayscale-0 transition" />
        </div>
    </section>

    <!-- Transfer System (Система перемещения между складами) -->
    <section class="bg-gray-50 py-20 px-6">
        <div class="max-w-6xl mx-auto text-center mb-12">
            <h2 class="text-3xl sm:text-4xl font-bold mb-4">Smart Transfer System</h2>
            <p class="text-gray-600 max-w-2xl mx-auto">
                Track and control the movement of parts across your warehouse network in real time — technician to manager to central hub.
            </p>
        </div>

        <!-- Flow steps -->
        <div class="relative max-w-4xl mx-auto grid grid-cols-1 sm:grid-cols-3 gap-10 text-center">
            <!-- Step 1 -->
            <div class="bg-white p-6 rounded-2xl shadow hover:shadow-md transition">
                <div class="text-blue-600 mb-3">
                    <svg class="w-10 h-10 mx-auto" fill="none" stroke="currentColor" stroke-width="2"
                         viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M3 10h11M9 21V3m4 18v-6h8v6" />
                    </svg>
                </div>
                <h3 class="font-semibold text-lg mb-2">Technician Requests</h3>
                <p class="text-gray-600 text-sm">Technician submits part request from local stock or manager pool.</p>
            </div>

            <!-- Arrow -->
            <div class="hidden sm:flex items-center justify-center">
                <svg class="w-10 h-10 text-gray-400 rotate-90 sm:rotate-0" fill="none" stroke="currentColor" stroke-width="2"
                     viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M9 5l7 7-7 7" />
                </svg>
            </div>

            <!-- Step 2 -->
            <div class="bg-white p-6 rounded-2xl shadow hover:shadow-md transition">
                <div class="text-blue-600 mb-3">
                    <svg class="w-10 h-10 mx-auto" fill="none" stroke="currentColor" stroke-width="2"
                         viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                </div>
                <h3 class="font-semibold text-lg mb-2">Manager Approval</h3>
                <p class="text-gray-600 text-sm">Manager validates request and assigns transfer from central or their own stock.</p>
            </div>

            <!-- Arrow -->
            <div class="hidden sm:flex items-center justify-center">
                <svg class="w-10 h-10 text-gray-400 rotate-90 sm:rotate-0" fill="none" stroke="currentColor" stroke-width="2"
                     viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M9 5l7 7-7 7" />
                </svg>
            </div>

            <!-- Step 3 -->
            <div class="bg-white p-6 rounded-2xl shadow hover:shadow-md transition">
                <div class="text-blue-600 mb-3">
                    <svg class="w-10 h-10 mx-auto" fill="none" stroke="currentColor" stroke-width="2"
                         viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M12 8c1.38 0 2.5 1.12 2.5 2.5S13.38 13 12 13s-2.5-1.12-2.5-2.5S10.62 8 12 8zm0 0v4m0 4v4m-8-4h16" />
                    </svg>
                </div>
                <h3 class="font-semibold text-lg mb-2">Delivery + Logging</h3>
                <p class="text-gray-600 text-sm">Transfer completed. Stock updated automatically and delivery logged.</p>
            </div>
        </div>
    </section>

    <!-- Pricing Plans -->
    <section id="pricing" class="bg-white py-20 px-6">
        <div class="max-w-6xl mx-auto text-center mb-12">
            <h2 class="text-3xl sm:text-4xl font-bold mb-4">Choose your plan</h2>
            <p class="text-gray-600 max-w-2xl mx-auto">
                Flexible pricing options for every size of team. Start free or scale with powerful features.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-10 max-w-6xl mx-auto">
            <!-- Free Plan -->
            <div class="border rounded-2xl p-8 text-center shadow hover:shadow-md transition">
                <h3 class="text-xl font-semibold mb-2">Free</h3>
                <p class="text-4xl font-bold text-blue-600 mb-4">$0</p>
                <ul class="text-sm text-gray-700 space-y-2 mb-6">
                    <li>✔ 1 Technician</li>
                    <li>✔ Basic Inventory</li>
                    <li>✔ Email Support</li>
                </ul>
                <a href="#contact" class="inline-block bg-blue-100 text-blue-700 px-6 py-3 rounded-xl hover:bg-blue-200 transition">
                    Start Free
                </a>
            </div>

            <!-- Pro Plan -->
            <div class="border-2 border-blue-600 rounded-2xl p-8 text-center shadow-lg scale-105">
                <h3 class="text-xl font-semibold mb-2">Pro</h3>
                <p class="text-4xl font-bold text-blue-600 mb-4">$19.99</p>
                <ul class="text-sm text-gray-700 space-y-2 mb-6">
                    <li>✔ Up to 10 Technicians</li>
                    <li>✔ Advanced Reporting</li>
                    <li>✔ Email & Chat Support</li>
                </ul>
                <a href="#contact" class="inline-block bg-blue-600 text-white px-6 py-3 rounded-xl hover:bg-blue-700 transition">
                    Upgrade Now
                </a>
            </div>

            <!-- Business Plan -->
            <div class="border rounded-2xl p-8 text-center shadow hover:shadow-md transition">
                <h3 class="text-xl font-semibold mb-2">Business</h3>
                <p class="text-4xl font-bold text-blue-600 mb-4">$49.99</p>
                <ul class="text-sm text-gray-700 space-y-2 mb-6">
                    <li>✔ Unlimited Technicians</li>
                    <li>✔ Multi-Warehouse Support</li>
                    <li>✔ Priority Support</li>
                </ul>
                <a href="#contact" class="inline-block bg-blue-100 text-blue-700 px-6 py-3 rounded-xl hover:bg-blue-200 transition">
                    Contact Sales
                </a>
            </div>
        </div>
    </section>

    <!-- Testimonials -->
    <section class="bg-gray-50 py-20 px-6">
        <div class="max-w-6xl mx-auto text-center mb-12">
            <h2 class="text-3xl sm:text-4xl font-bold mb-4">Отзывы наших клиентов</h2>
            <p class="text-gray-600 max-w-2xl mx-auto">
                Мы помогаем сотням складов по всей стране работать быстрее, точнее и эффективнее.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 max-w-6xl mx-auto">
            <!-- Отзыв 1 -->
            <div class="bg-white p-6 rounded-2xl shadow hover:shadow-md transition">
                <p class="text-gray-800 italic mb-4">“SemixPro полностью изменил наш подход к работе на складе. Всё стало прозрачным и под контролем.”</p>
                <div class="text-sm font-semibold text-gray-700">— Алексей, старший кладовщик</div>
            </div>

            <!-- Отзыв 2 -->
            <div class="bg-white p-6 rounded-2xl shadow hover:shadow-md transition">
                <p class="text-gray-800 italic mb-4">“Раньше мы теряли много времени на учёт и поиск деталей. Теперь всё автоматизировано и просто.”</p>
                <div class="text-sm font-semibold text-gray-700">— Мария, менеджер склада</div>
            </div>

            <!-- Отзыв 3 -->
            <div class="bg-white p-6 rounded-2xl shadow hover:shadow-md transition">
                <p class="text-gray-800 italic mb-4">“Система помогла нам оптимизировать передачу запчастей между техниками и избежать путаницы.”</p>
                <div class="text-sm font-semibold text-gray-700">— Дмитрий, технический директор</div>
            </div>
        </div>
    </section>

    <!-- Company Stats -->
    <section class="bg-white py-20 px-6">
        <div class="max-w-6xl mx-auto text-center mb-12">
            <h2 class="text-3xl sm:text-4xl font-bold mb-4">SemixPro в цифрах</h2>
            <p class="text-gray-600 max-w-2xl mx-auto">
                Мы гордимся стабильной и масштабируемой системой, которой доверяют сотни компаний.
            </p>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-4 gap-10 max-w-4xl mx-auto text-center">
            <div>
                <p class="text-4xl font-bold text-blue-600">1,200+</p>
                <p class="text-gray-600 mt-2 text-sm">Активных пользователей</p>
            </div>
            <div>
                <p class="text-4xl font-bold text-blue-600">320</p>
                <p class="text-gray-600 mt-2 text-sm">Складов подключено</p>
            </div>
            <div>
                <p class="text-4xl font-bold text-blue-600">85K</p>
                <p class="text-gray-600 mt-2 text-sm">Операций выполнено</p>
            </div>
            <div>
                <p class="text-4xl font-bold text-blue-600">99.9%</p>
                <p class="text-gray-600 mt-2 text-sm">Аптайм системы</p>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="bg-gray-50 py-20 px-6">
        <div class="max-w-3xl mx-auto text-center mb-12">
            <h2 class="text-3xl sm:text-4xl font-bold mb-4">Свяжитесь с нами</h2>
            <p class="text-gray-600">
                Оставьте заявку, и мы свяжемся с вами для подключения и демонстрации возможностей SemixPro.
            </p>
        </div>

        <div class="max-w-xl mx-auto bg-white p-8 rounded-2xl shadow" x-data="{ name: '', email: '', message: '', submitted: false }">
            <form @submit.prevent="submitted = true">
                <div class="mb-4 text-left">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Имя</label>
                    <input type="text" x-model="name" required class="w-full px-4 py-2 border rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div class="mb-4 text-left">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" x-model="email" required class="w-full px-4 py-2 border rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div class="mb-6 text-left">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Сообщение</label>
                    <textarea rows="4" x-model="message" required class="w-full px-4 py-2 border rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                </div>

                <button type="submit"
                        class="w-full bg-blue-600 text-white font-semibold py-3 rounded-xl hover:bg-blue-700 transition">
                    Отправить сообщение
                </button>
            </form>

            <!-- Подтверждение -->
            <div x-show="submitted" x-transition class="mt-6 text-green-600 text-center font-medium">
                ✅ Спасибо! Мы скоро свяжемся с вами.
            </div>
        </div>
    </section>


    <!-- Footer -->
    <footer class="bg-gray-100 text-gray-600 py-10 px-6">
        <div class="max-w-6xl mx-auto flex flex-col md:flex-row justify-between items-center space-y-6 md:space-y-0">
            <div class="text-lg font-semibold text-gray-800">
                &copy; 2025 Semix. All rights reserved.
            </div>
            <div class="flex space-x-6 text-sm">
                <a href="#features" class="hover:text-blue-600">Features</a>
                <a href="#pricing" class="hover:text-blue-600">Pricing</a>
                <a href="#faq" class="hover:text-blue-600">FAQ</a>
                <a href="#contact" class="hover:text-blue-600">Contact</a>
            </div>
        </div>
    </footer>

    </body>
</html>
