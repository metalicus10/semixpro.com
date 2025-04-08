<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark scroll-smooth overflow-x-hidden">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Laravel</title>

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Rubik:wght@400;500;700&display=swap" rel="stylesheet">

    <!-- Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="antialiased bg-dark text-text font-sans overflow-x-hidden">
<div class="min-h-screen bg-background">
    <!-- Navbar -->
    <header
        x-data="{ scrolled: false }"
        x-init="$watch('scrolled', val => console.log(val)); window.addEventListener('scroll', () => scrolled = window.scrollY > 5)"
        x-effect="console.log('Scrolled:', scrolled)"
        :class="scrolled
        ? 'fixed top-0 left-0 right-0 z-50 transition-all duration-300 inset-x-0 w-full overflow-hidden bg-background/95 backdrop-blur-md shadow-md'
        : 'fixed top-0 left-0 right-0 z-50 transition-all duration-300 bg-transparent inset-x-0 w-full overflow-hidden'"
    >

        <div class="container mx-auto max-w-screen-xl px-4 md:px-6">
            <div class="flex h-16 items-center justify-between min-w-0">
                <div class="flex items-center"><a href="/">
                        <div class="flex items-center space-x-2">
                            <div class="flex h-8 w-8 items-center justify-center rounded-md bg-brand-accent">
                                <svg class="h-5 w-5 text-brand-darker" xmlns="http://www.w3.org/2000/svg"
                                     viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                     stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M20 9v9a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V9"></path>
                                    <path d="M9 22V12h6v10"></path>
                                    <path d="M2 10l10-5 10 5"></path>
                                </svg>
                            </div>
                            <span class="text-xl font-bold text-brand-primary">InventoryPro</span></div>
                    </a>
                    <nav class="ml-10 hidden space-x-6 md:flex">
                        <a href="#" class="text-sm text-brand-light transition-colors hover:text-brand-primary">
                            Главная
                        </a>
                        <a href="#about" class="text-sm text-brand-light transition-colors hover:text-brand-primary">
                            О системе
                        </a>
                        <a href="#features" class="text-sm text-brand-light transition-colors hover:text-brand-primary">
                            Функции
                        </a>
                        <a href="#pricing" class="text-sm text-brand-light transition-colors hover:text-brand-primary">
                            Тарифы
                        </a>
                        <a href="#contact" class="text-sm text-brand-light transition-colors hover:text-brand-primary">
                            Контакты
                        </a>
                    </nav>
                </div>
                <div
                    class="hidden md:flex items-center space-x-4">
                    <button
                        class="inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 [&amp;_svg]:pointer-events-none [&amp;_svg]:size-4 [&amp;_svg]:shrink-0 hover:bg-accent h-10 px-4 py-2 text-brand-light hover:text-brand-primary">
                        Войти
                    </button>
                    <button
                        class="inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 [&amp;_svg]:pointer-events-none [&amp;_svg]:size-4 [&amp;_svg]:shrink-0 h-10 px-4 py-2 bg-brand-accent hover:bg-brand-accent/90 text-brand-darker">
                        Регистрация
                    </button>
                </div>
                <button
                    class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 [&_svg]:pointer-events-none [&_svg]:size-4 [&_svg]:shrink-0 hover:bg-accent hover:text-accent-foreground h-10 w-10 md:hidden text-brand-light"
                    type="button" aria-haspopup="dialog" aria-expanded="false" aria-controls="radix-:r0:"
                    data-state="closed">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                         class="lucide lucide-menu h-6 w-6">
                        <line x1="4" x2="20" y1="12" y2="12"></line>
                        <line x1="4" x2="20" y1="6" y2="6"></line>
                        <line x1="4" x2="20" y1="18" y2="18"></line>
                    </svg>
                    <span class="sr-only">Toggle menu</span>
                </button>
            </div>
        </div>
    </header>

    <main>

        <section class="pt-32 pb-20 relative overflow-hidden">
            <div
                class="absolute top-1/4 left-1/4 w-64 h-64 bg-primary/10 rounded-full filter blur-3xl"></div>
            <div
                class="absolute bottom-1/4 right-1/4 w-96 h-96 bg-primary/10 rounded-full filter blur-3xl"></div>
            <div
                class="container mx-auto px-4 sm:px-6 lg:px-8 relative">
                <div
                    class="text-center max-w-4xl mx-auto mb-16"><h1

                        class="text-4xl md:text-5xl lg:text-6xl font-sans font-bold mb-6 leading-none tracking-tight text-brand-light">
                        Эффективное Управление <br><span

                            class="text-brand-primary">Складом и Запчастями</span></h1>
                    <p
                        class="text-xl text-brand-light mb-10 max-w-160 mx-auto leading-none">Современное решение для
                        управления
                        номенклатурой, складами и запчастями. Прозрачный учет, гибкие настройки доступа.</p>
                    <div
                        class="flex flex-col sm:flex-row justify-center gap-4">
                        <button
                            class="inline-flex items-center justify-center gap-2 whitespace-nowrap text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 [&amp;_svg]:pointer-events-none [&amp;_svg]:size-4 [&amp;_svg]:shrink-0 h-11 rounded-md px-8 bg-brand-accent hover:bg-brand-accent/90 text-brand-darker">
                            Начать Работу
                        </button>
                        <button
                            class="inline-flex items-center justify-center gap-2 whitespace-nowrap text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 [&amp;_svg]:pointer-events-none [&amp;_svg]:size-4 [&amp;_svg]:shrink-0 border bg-background hover:text-accent-foreground h-11 rounded-md px-8 text-brand-primary border-brand-primary hover:bg-brand-primary/10">
                            Узнать Больше
                        </button>
                    </div>
                </div>
                <div
                    class="relative max-w-5xl mx-auto">
                    <div
                        class="dashboard-shadow bg-card rounded-xl overflow-hidden border border-muted relative">
                        <div
                            class="relative">
                            <div
                                class="w-full h-auto bg-card/50">
                                <div
                                    class="p-6 text-left">
                                    <div class="flex items-center justify-between mb-6">
                                        <div><p
                                                class="text-muted-foreground text-sm">Добрый день,
                                                Александр</p>
                                            <h3 class="text-xl font-medium">Панель управления</h3>
                                        </div>
                                        <div
                                            class="text-xs text-muted-foreground flex items-center">Обновлено: <span
                                                class="text-primary ml-1">5 минут назад</span>
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                                        <div
                                            class="bg-brand-darker/30 p-4 rounded-lg border border-brand-border-grey/20">
                                            <div class="flex justify-between items-start mb-2">
                                                <div><p
                                                        class="text-xs text-brand-light/70">Всего
                                                        Номенклатур</p><h4

                                                        class="text-2xl font-semibold text-brand-light">1,247</h4></div>
                                                <div class="bg-brand-accent/20 p-1 rounded">
                                                    <svg class="h-5 w-5 text-brand-accent"
                                                         xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                                                         fill="none"
                                                         stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                                         stroke-linejoin="round">
                                                        <path
                                                            d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                                        <path d="M14 2v6h6"></path>
                                                        <path d="M16 13H8"></path>
                                                        <path d="M16 17H8"></path>
                                                        <path d="M10 9H8"></path>
                                                    </svg>
                                                </div>
                                            </div>
                                            <div class="flex items-center text-xs"><span
                                                    class="text-brand-accent">+15</span><span
                                                    class="text-brand-light/70 ml-1">за последнюю неделю</span>
                                            </div>
                                        </div>
                                        <div
                                            class="bg-brand-darker/30 p-4 rounded-lg border border-brand-border-grey/20">
                                            <div class="flex justify-between items-start mb-2">
                                                <div><p
                                                        class="text-xs text-brand-light/70">
                                                        Количество Складов</p><h4
                                                        class="text-2xl font-semibold text-brand-light">8</h4></div>
                                                <div class="bg-brand-second/20 p-1 rounded">
                                                    <svg class="h-5 w-5 text-brand-second"
                                                         xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                                                         fill="none"
                                                         stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                                         stroke-linejoin="round">
                                                        <path
                                                            d="M20 9v9a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V9"></path>
                                                        <path d="M9 22V12h6v10"></path>
                                                        <path d="M2 10l10-5 10 5"></path>
                                                    </svg>
                                                </div>
                                            </div>
                                            <div
                                                class="w-full bg-brand-border-grey/20 rounded-full h-1.5">
                                                <div class="bg-brand-second h-1.5 rounded-full"
                                                     style="width: 75%;"></div>
                                            </div>
                                            <div
                                                class="flex justify-between items-center text-xs mt-1"><span
                                                    class="text-brand-light/70">6 активных</span><span
                                                    class="text-brand-second">75%</span></div>
                                        </div>
                                        <div
                                            class="bg-brand-darker/30 p-4 rounded-lg border border-brand-border-grey/20">
                                            <div class="flex justify-between items-start mb-2">
                                                <div><p
                                                        class="text-xs text-brand-light/70">Учтено
                                                        Запчастей</p><h4
                                                        class="text-2xl font-semibold text-brand-light">5,892</h4></div>
                                                <div class="bg-brand-dark/20 p-1 rounded">
                                                    <svg class="h-5 w-5 text-brand-dark"
                                                         xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                                                         fill="none"
                                                         stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                                         stroke-linejoin="round">
                                                        <path
                                                            d="M5 5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2z"></path>
                                                        <path d="M12 8v8"></path>
                                                        <path d="M8 12h8"></path>
                                                    </svg>
                                                </div>
                                            </div>
                                            <div class="flex items-center text-xs"><span
                                                    class="text-brand-dark">+142</span><span
                                                    class="text-brand-light/70 ml-1">за последний месяц</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="flex items-center justify-between mb-4"><h4
                                                class="font-medium text-brand-light">Последние
                                                Действия</h4>
                                            <button
                                                class="inline-flex items-center justify-center gap-2 whitespace-nowrap font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 [&amp;_svg]:pointer-events-none [&amp;_svg]:size-4 [&amp;_svg]:shrink-0 hover:bg-accent h-9 rounded-md px-3 text-xs text-brand-light/70 hover:text-brand-primary">
                                                Показать Все
                                            </button>
                                        </div>
                                        <div class="space-y-3">
                                            <div
                                                class="flex items-center justify-between p-3 bg-brand-darker/30 rounded-lg border border-brand-border-grey/20">
                                                <div class="flex items-center">
                                                    <div
                                                        class="w-10 h-10 rounded-full bg-brand-dark/10 flex items-center justify-center mr-3">
                                                        <svg class="h-5 w-5 text-brand-dark"
                                                             xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                                                             fill="none" stroke="currentColor" stroke-width="2"
                                                             stroke-linecap="round" stroke-linejoin="round">
                                                            <path
                                                                d="M5 5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2z"></path>
                                                            <path
                                                                d="M12 8v8"></path>
                                                            <path
                                                                d="M8 12h8"></path>
                                                        </svg>
                                                    </div>
                                                    <div><p
                                                            class="font-medium text-brand-light">
                                                            Добавлена запчасть: Датчик ABX-12</p>
                                                        <p class="text-xs text-brand-light/70">07
                                                            Апреля, 2025</p></div>
                                                </div>
                                                <span class="text-brand-light font-medium">+24 шт.</span>
                                            </div>
                                            <div
                                                class="flex items-center justify-between p-3 bg-brand-darker/30 rounded-lg border border-brand-border-grey/20">
                                                <div class="flex items-center">
                                                    <div
                                                        class="w-10 h-10 rounded-full bg-brand-second/10 flex items-center justify-center mr-3">
                                                        <svg class="h-5 w-5 text-brand-second"
                                                             xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                                                             fill="none" stroke="currentColor" stroke-width="2"
                                                             stroke-linecap="round" stroke-linejoin="round">
                                                            <path
                                                                d="M20 9v9a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V9"></path>
                                                            <path
                                                                d="M9 22V12h6v10"></path>
                                                            <path
                                                                d="M2 10l10-5 10 5"></path>
                                                        </svg>
                                                    </div>
                                                    <div><p
                                                            class="font-medium text-brand-light">
                                                            Создан новый склад: Южный</p>
                                                        <p class="text-xs text-brand-light/70">05
                                                            Апреля, 2025</p></div>
                                                </div>
                                                <span class="text-brand-accent font-medium">Техник: Иванов</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="absolute top-0 left-0 right-0 bottom-0 dashboard-gradient"></div>
                        </div>
                    </div>
                    <div
                        class="absolute -top-6 -left-6 bg-brand-darker/80 border border-brand-border-grey/20 rounded-lg p-3 shadow-lg hidden md:block">
                        <div
                            class="flex items-center space-x-2">
                            <div
                                class="w-3 h-3 rounded-full bg-brand-accent"></div>
                            <span
                                class="text-sm font-medium text-brand-light">Отслеживание в реальном времени</span>
                        </div>
                    </div>
                    <div
                        class="absolute -bottom-6 -right-6 bg-brand-darker/80 border border-brand-border-grey/20 rounded-lg p-3 shadow-lg hidden md:block">
                        <div
                            class="flex items-center space-x-2">
                            <svg
                                class="h-5 w-5 text-brand-primary" xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round">
                                <rect width="18" height="18" x="3" y="3" rx="2" ry="2"></rect>
                                <line x1="3" y1="9" x2="21" y2="9"></line>
                                <line x1="9" y1="21" x2="9" y2="9"></line>
                            </svg>
                            <span class="text-sm font-medium text-brand-light">Анализ запасов</span></div>
                    </div>
                </div>
            </div>
        </section>

        <section class="py-16 bg-secondary/30">
            <div class="container mx-auto px-4 sm:px-6 lg:px-8"><p
                    class="text-center text-muted-foreground text-sm uppercase tracking-wider font-medium mb-8">Our
                    Recent
                    Clients &amp; Partners</p>
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-8 items-center justify-items-center">
                    <div
                        class="grayscale opacity-70 hover:grayscale-0 hover:opacity-100 transition-all duration-300 flex items-center justify-center">
                        <svg stroke="currentColor" fill="currentColor" stroke-width="0" role="img" viewBox="0 0 24 24"
                             class="h-8 w-auto text-muted-foreground hover:text-primary"
                             height="1em" width="1em" xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M0 15.415c0 .468.38.85.848.85h5.937V.575L0 7.72v7.695m15.416 8.582c.467 0 .846-.38.846-.849v-5.937H.573l7.146 6.785h7.697M24 8.587a.844.844 0 0 0-.847-.846h-5.938V23.43l6.782-7.148L24 8.586M8.585.003a.847.847 0 0 0-.847.847v5.94h15.688L16.282.003H8.585Z"></path>
                        </svg>
                    </div>
                    <div
                        class="grayscale opacity-70 hover:grayscale-0 hover:opacity-100 transition-all duration-300 flex items-center justify-center">
                        <svg stroke="currentColor" fill="currentColor" stroke-width="0" role="img" viewBox="0 0 24 24"
                             class="h-8 w-auto text-muted-foreground hover:text-primary"
                             height="1em" width="1em" xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M9.112 8.262L5.97 15.758H3.92L2.374 9.775c-.094-.368-.175-.503-.461-.658C1.447 8.864.677 8.627 0 8.479l.046-.217h3.3a.904.904 0 01.894.764l.817 4.338 2.018-5.102zm8.033 5.049c.008-1.979-2.736-2.088-2.717-2.972.006-.269.262-.555.822-.628a3.66 3.66 0 011.913.336l.34-1.59a5.207 5.207 0 00-1.814-.333c-1.917 0-3.266 1.02-3.278 2.479-.012 1.079.963 1.68 1.698 2.04.756.367 1.01.603 1.006.931-.005.504-.602.725-1.16.734-.975.015-1.54-.263-1.992-.473l-.351 1.642c.453.208 1.289.39 2.156.398 2.037 0 3.37-1.006 3.377-2.564m5.061 2.447H24l-1.565-7.496h-1.656a.883.883 0 00-.826.55l-2.909 6.946h2.036l.405-1.12h2.488zm-2.163-2.656l1.02-2.815.588 2.815zm-8.16-4.84l-1.603 7.496H8.34l1.605-7.496z"></path>
                        </svg>
                    </div>
                    <div
                        class="grayscale opacity-70 hover:grayscale-0 hover:opacity-100 transition-all duration-300 flex items-center justify-center">
                        <svg stroke="currentColor" fill="currentColor" stroke-width="0" role="img" viewBox="0 0 24 24"
                             class="h-8 w-auto text-muted-foreground hover:text-primary"
                             height="1em" width="1em" xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M11.343 18.031c.058.049.12.098.181.146-1.177.783-2.59 1.238-4.107 1.238C3.32 19.416 0 16.096 0 12c0-4.095 3.32-7.416 7.416-7.416 1.518 0 2.931.456 4.105 1.238-.06.051-.12.098-.165.15C9.6 7.489 8.595 9.688 8.595 12c0 2.311 1.001 4.51 2.748 6.031zm5.241-13.447c-1.52 0-2.931.456-4.105 1.238.06.051.12.098.165.15C14.4 7.489 15.405 9.688 15.405 12c0 2.31-1.001 4.507-2.748 6.031-.058.049-.12.098-.181.146 1.177.783 2.588 1.238 4.107 1.238C20.68 19.416 24 16.096 24 12c0-4.094-3.32-7.416-7.416-7.416zM12 6.174c-.096.075-.189.15-.28.231C10.156 7.764 9.169 9.765 9.169 12c0 2.236.987 4.236 2.551 5.595.09.08.185.158.28.232.096-.074.189-.152.28-.232 1.563-1.359 2.551-3.359 2.551-5.595 0-2.235-.987-4.236-2.551-5.595-.09-.08-.184-.156-.28-.231z"></path>
                        </svg>
                    </div>
                    <div
                        class="grayscale opacity-70 hover:grayscale-0 hover:opacity-100 transition-all duration-300 flex items-center justify-center">
                        <svg stroke="currentColor" fill="currentColor" stroke-width="0" role="img" viewBox="0 0 24 24"
                             class="h-8 w-auto text-muted-foreground hover:text-primary"
                             height="1em" width="1em" xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M.045 18.02c.072-.116.187-.124.348-.022 3.636 2.11 7.594 3.166 11.87 3.166 2.852 0 5.668-.533 8.447-1.595l.315-.14c.138-.06.234-.1.293-.13.226-.088.39-.046.525.13.12.174.09.336-.12.48-.256.19-.6.41-1.006.654-1.244.743-2.64 1.316-4.185 1.726a17.617 17.617 0 01-10.951-.577 17.88 17.88 0 01-5.43-3.35c-.1-.074-.151-.15-.151-.22 0-.047.021-.09.051-.13zm6.565-6.218c0-1.005.247-1.863.743-2.577.495-.71 1.17-1.25 2.04-1.615.796-.335 1.756-.575 2.912-.72.39-.046 1.033-.103 1.92-.174v-.37c0-.93-.105-1.558-.3-1.875-.302-.43-.78-.65-1.44-.65h-.182c-.48.046-.896.196-1.246.46-.35.27-.575.63-.675 1.096-.06.3-.206.465-.435.51l-2.52-.315c-.248-.06-.372-.18-.372-.39 0-.046.007-.09.022-.15.247-1.29.855-2.25 1.82-2.88.976-.616 2.1-.975 3.39-1.05h.54c1.65 0 2.957.434 3.888 1.29.135.15.27.3.405.48.12.165.224.314.283.45.075.134.15.33.195.57.06.254.105.42.135.51.03.104.062.3.076.615.01.313.02.493.02.553v5.28c0 .376.06.72.165 1.036.105.313.21.54.315.674l.51.674c.09.136.136.256.136.36 0 .12-.06.226-.18.314-1.2 1.05-1.86 1.62-1.963 1.71-.165.135-.375.15-.63.045a6.062 6.062 0 01-.526-.496l-.31-.347a9.391 9.391 0 01-.317-.42l-.3-.435c-.81.886-1.603 1.44-2.4 1.665-.494.15-1.093.227-1.83.227-1.11 0-2.04-.343-2.76-1.034-.72-.69-1.08-1.665-1.08-2.94l-.05-.076zm3.753-.438c0 .566.14 1.02.425 1.364.285.34.675.512 1.155.512.045 0 .106-.007.195-.02.09-.016.134-.023.166-.023.614-.16 1.08-.553 1.424-1.178.165-.28.285-.58.36-.91.09-.32.12-.59.135-.8.015-.195.015-.54.015-1.005v-.54c-.84 0-1.484.06-1.92.18-1.275.36-1.92 1.17-1.92 2.43l-.035-.02zm9.162 7.027c.03-.06.075-.11.132-.17.362-.243.714-.41 1.05-.5a8.094 8.094 0 011.612-.24c.14-.012.28 0 .41.03.65.06 1.05.168 1.172.33.063.09.099.228.099.39v.15c0 .51-.149 1.11-.424 1.8-.278.69-.664 1.248-1.156 1.68-.073.06-.14.09-.197.09-.03 0-.06 0-.09-.012-.09-.044-.107-.12-.064-.24.54-1.26.806-2.143.806-2.64 0-.15-.03-.27-.087-.344-.145-.166-.55-.257-1.224-.257-.243 0-.533.016-.87.046-.363.045-.7.09-1 .135-.09 0-.148-.014-.18-.044-.03-.03-.036-.047-.02-.077 0-.017.006-.03.02-.063v-.06z"></path>
                        </svg>
                    </div>
                    <div
                        class="grayscale opacity-70 hover:grayscale-0 hover:opacity-100 transition-all duration-300 flex items-center justify-center">
                        <svg stroke="currentColor" fill="currentColor" stroke-width="0" role="img" viewBox="0 0 24 24"
                             class="h-8 w-auto text-muted-foreground hover:text-primary"
                             height="1em" width="1em" xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M12.48 10.92v3.28h7.84c-.24 1.84-.853 3.187-1.787 4.133-1.147 1.147-2.933 2.4-6.053 2.4-4.827 0-8.6-3.893-8.6-8.72s3.773-8.72 8.6-8.72c2.6 0 4.507 1.027 5.907 2.347l2.307-2.307C18.747 1.44 16.133 0 12.48 0 5.867 0 .307 5.387.307 12s5.56 12 12.173 12c3.573 0 6.267-1.173 8.373-3.36 2.16-2.16 2.84-5.213 2.84-7.667 0-.76-.053-1.467-.173-2.053H12.48z"></path>
                        </svg>
                    </div>
                    <div
                        class="grayscale opacity-70 hover:grayscale-0 hover:opacity-100 transition-all duration-300 flex items-center justify-center">
                        <svg stroke="currentColor" fill="currentColor" stroke-width="0" role="img" viewBox="0 0 24 24"
                             class="h-8 w-auto text-muted-foreground hover:text-primary"
                             height="1em" width="1em" xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M12.152 6.896c-.948 0-2.415-1.078-3.96-1.04-2.04.027-3.91 1.183-4.961 3.014-2.117 3.675-.546 9.103 1.519 12.09 1.013 1.454 2.208 3.09 3.792 3.039 1.52-.065 2.09-.987 3.935-.987 1.831 0 2.35.987 3.96.948 1.637-.026 2.676-1.48 3.676-2.948 1.156-1.688 1.636-3.325 1.662-3.415-.039-.013-3.182-1.221-3.22-4.857-.026-3.04 2.48-4.494 2.597-4.559-1.429-2.09-3.623-2.324-4.39-2.376-2-.156-3.675 1.09-4.61 1.09zM15.53 3.83c.843-1.012 1.4-2.427 1.245-3.83-1.207.052-2.662.805-3.532 1.818-.78.896-1.454 2.338-1.273 3.714 1.338.104 2.715-.688 3.559-1.701"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </section>

        <!-- Hero Section -->
        <section id="features"
                 class="py-24">
            <div class="container mx-auto px-4 md:px-6">
                <div class="text-center mb-16"><h2 class="text-3xl font-bold tracking-tight mb-4">
                        Система Управления Складом <br><span
                            class="text-primary">для Вашего Бизнеса</span></h2></div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <div
                        class="rounded-lg border text-card-foreground shadow-sm bg-secondary/30 border-muted hover:border-primary/30 transition-all duration-300 overflow-hidden h-full group">
                        <div class="p-6 feature-gradient h-full">
                            <div
                                class="w-12 h-12 rounded-full bg-primary/10 flex items-center justify-center mb-6 group-hover:bg-primary/20 transition-colors duration-300">
                                <svg class="h-6 w-6 text-primary" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                                     fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                     stroke-linejoin="round">
                                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                    <path d="M14 2v6h6"></path>
                                    <path d="M16 13H8"></path>
                                    <path d="M16 17H8"></path>
                                    <path d="M10 9H8"></path>
                                </svg>
                            </div>
                            <h3 class="text-xl font-semibold mb-3">Управление Номенклатурой</h3>
                            <p class="text-muted-foreground leading-none">Создавайте и управляйте каталогом шаблонов
                                запчастей с удобной
                                системой версионирования и архивации.</p></div>
                    </div>
                    <div
                        class="rounded-lg border text-card-foreground shadow-sm bg-secondary/30 border-muted hover:border-primary/30 transition-all duration-300 overflow-hidden h-full group">
                        <div class="p-6 feature-gradient h-full">
                            <div
                                class="w-12 h-12 rounded-full bg-primary/10 flex items-center justify-center mb-6 group-hover:bg-primary/20 transition-colors duration-300">
                                <svg class="h-6 w-6 text-primary"
                                     xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                     stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                     stroke-linejoin="round">
                                    <path d="M20 9v9a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V9"></path>
                                    <path d="M9 22V12h6v10"></path>
                                    <path d="M2 10l10-5 10 5"></path>
                                </svg>
                            </div>
                            <h3 class="text-xl font-semibold mb-3">Управление Складами</h3>
                            <p class="text-muted-foreground leading-none">Создавайте и настраивайте несколько складов с
                                гибкой системой
                                доступа для менеджеров и техников.</p></div>
                    </div>
                    <div
                        class="rounded-lg border text-card-foreground shadow-sm bg-secondary/30 border-muted hover:border-primary/30 transition-all duration-300 overflow-hidden h-full group">
                        <div class="p-6 feature-gradient h-full">
                            <div
                                class="w-12 h-12 rounded-full bg-primary/10 flex items-center justify-center mb-6 group-hover:bg-primary/20 transition-colors duration-300">
                                <svg class="h-6 w-6 text-primary"
                                     xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                     stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                     stroke-linejoin="round">
                                    <path
                                        d="M5 5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2z"></path>
                                    <path d="M12 8v8"></path>
                                    <path d="M8 12h8"></path>
                                </svg>
                            </div>
                            <h3 class="text-xl font-semibold mb-3">Учет Запчастей</h3>
                            <p class="text-muted-foreground leading-none">Отслеживайте запчасти на разных складах,
                                перемещайте между ними
                                и управляйте остатками в режиме реального времени.</p></div>
                    </div>
                </div>
            </div>
        </section>

        <section class="py-16 md:py-24">
            <div class="container mx-auto px-4 md:px-6">
                <div data-replit-metadata="client/src/components/SpendingOverview.tsx:16:8" data-component-name="div"
                     class="grid grid-cols-1 lg:grid-cols-2 gap-8 items-center">
                    <div class="rounded-lg border text-card-foreground shadow-sm bg-card border-muted overflow-hidden">
                        <div class="p-6">
                            <div class="mb-6"><h3
                                    class="text-xl font-semibold mb-2">Обзор запасов</h3>
                                <p class="text-sm text-muted-foreground">Ежемесячный отчет состояния
                                    склада</p></div>
                            <div class="flex items-center justify-between mb-4"><span
                                    class="text-2xl font-bold">14&nbsp;772 ед.</span><span
                                    class="text-xs px-2 py-1 bg-primary/20 text-primary rounded">Текущий месяц</span>
                            </div>
                            <div class="space-y-4">
                                <div>
                                    <div class="flex justify-between text-sm mb-1"><span>Механические запчасти</span>
                                        <span>4&nbsp;306 ед.</span></div>
                                    <div aria-valuemax="100" aria-valuemin="0" role="progressbar"
                                         data-state="indeterminate"
                                         data-max="100"
                                         class="relative w-full overflow-hidden rounded-full bg-secondary h-2">
                                        <div data-state="indeterminate" data-max="100"
                                             class="h-full w-full flex-1 transition-all bg-primary"
                                             style="transform: translateX(-72%);"></div>
                                    </div>
                                </div>
                                <div>
                                    <div class="flex justify-between text-sm mb-1"><span>Электроника</span><span>2&nbsp;500 ед.</span>
                                    </div>
                                    <div aria-valuemax="100" aria-valuemin="0" role="progressbar"
                                         data-state="indeterminate"
                                         data-max="100"
                                         class="relative w-full overflow-hidden rounded-full bg-secondary h-2">
                                        <div data-state="indeterminate" data-max="100"
                                             class="h-full w-full flex-1 transition-all bg-[#FFE37C]"
                                             style="transform: translateX(-82%);"></div>
                                    </div>
                                </div>
                                <div>
                                    <div class="flex justify-between text-sm mb-1"><span>Гидравлика</span><span>1&nbsp;802 ед.</span>
                                    </div>
                                    <div aria-valuemax="100" aria-valuemin="0" role="progressbar"
                                         data-state="indeterminate"
                                         data-max="100"
                                         class="relative w-full overflow-hidden rounded-full bg-secondary h-2">
                                        <div data-state="indeterminate" data-max="100"
                                             class="h-full w-full flex-1 transition-all bg-[#FF7C4F]"
                                             style="transform: translateX(-88%);"></div>
                                    </div>
                                </div>
                                <div>
                                    <div class="flex justify-between text-sm mb-1"><span>Инструменты</span><span>3&nbsp;208 ед.</span>
                                    </div>
                                    <div aria-valuemax="100" aria-valuemin="0" role="progressbar"
                                         data-state="indeterminate"
                                         data-max="100"
                                         class="relative w-full overflow-hidden rounded-full bg-secondary h-2">
                                        <div data-state="indeterminate" data-max="100"
                                             class="h-full w-full flex-1 transition-all bg-blue-400"
                                             style="transform: translateX(-78%);"></div>
                                    </div>
                                </div>
                                <div>
                                    <div class="flex justify-between text-sm mb-1">
                                        <span>Расходные материалы</span><span>2&nbsp;956 ед.</span>
                                    </div>
                                    <div aria-valuemax="100" aria-valuemin="0" role="progressbar"
                                         data-state="indeterminate"
                                         data-max="100"
                                         class="relative w-full overflow-hidden rounded-full bg-secondary h-2">
                                        <div data-state="indeterminate" data-max="100"
                                             class="h-full w-full flex-1 transition-all bg-gray-400"
                                             style="transform: translateX(-80%);"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-6 text-center">
                                <button class="text-primary text-sm font-medium hover:underline">Просмотреть подробный
                                    отчет
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="lg:pl-8"><h2 class="text-3xl font-bold tracking-tight mb-6"><span
                                class="text-primary">Умная аналитика</span> для эффективного
                            управления запасами</h2>
                        <p class="text-muted-foreground mb-8">Наша интеллектуальная система отслеживает движение запасов
                            и
                            предоставляет аналитику для оптимизации складских процессов. Понимайте состояние склада и
                            принимайте обоснованные решения.</p>
                        <div class="space-y-6">
                            <div class="flex items-start">
                                <div class="mr-4 p-2 bg-primary/20 rounded-md">
                                    <svg class="h-5 w-5 text-primary"
                                         xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                         stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                         stroke-linejoin="round">
                                        <path d="m12 14 4-4"></path>
                                        <path d="M3.34 19a10 10 0 1 1 17.32 0"></path>
                                    </svg>
                                </div>
                                <div><h3 class="font-medium mb-1">Аналитика запасов</h3>
                                    <p class="text-muted-foreground text-sm">Получайте подробную
                                        статистику по категориям запчастей и их распределению на складах.</p></div>
                            </div>
                            <div class="flex items-start">
                                <div class="mr-4 p-2 bg-primary/20 rounded-md">
                                    <svg class="h-5 w-5 text-primary"
                                         xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                         stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                         stroke-linejoin="round">
                                        <path
                                            d="M19 5c-1.5 0-2.8 1.4-3 2-3.5-1.5-11-.3-11 5 0 1.8 0 3 2 4.5V20h4v-2h3v2h4v-4c1-.5 1.7-1 2-2h2v-7h-2c0-1-.5-1.5-1-2Z"></path>
                                        <path d="M2 9v1c0 1.1.9 2 2 2h1"></path>
                                        <path d="M16 11h0"></path>
                                    </svg>
                                </div>
                                <div><h3 class="font-medium mb-1">Рекомендации по закупкам</h3>
                                    <p class="text-muted-foreground text-sm">Получайте
                                        персонализированные рекомендации по оптимизации запасов и своевременному
                                        пополнению.</p></div>
                            </div>
                            <div class="flex items-start">
                                <div class="mr-4 p-2 bg-primary/20 rounded-md">
                                    <svg class="h-5 w-5 text-primary"
                                         xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                         stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                         stroke-linejoin="round">
                                        <path d="M2 12h10"></path>
                                        <path d="M9 4v16"></path>
                                        <path d="M14 9h3"></path>
                                        <path d="M17 6v6"></path>
                                        <path d="M22 12h-3"></path>
                                    </svg>
                                </div>
                                <div><h3 class="font-medium mb-1">Контроль лимитов</h3>
                                    <p class="text-muted-foreground text-sm">Устанавливайте и
                                        контролируйте минимальные остатки для каждой категории с уведомлениями в
                                        реальном
                                        времени.</p></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Features Section -->
        <section id="features" class="py-20 px-4 bg-white/5">
            <div class="container mx-auto">
                <h2 class="text-3xl font-bold text-center mb-16">Возможности Системы<br>
                    <span class="text-emerald-500">Управления Складом</span>
                </h2>
                <div class="grid md:grid-cols-3 gap-8">
                    <div class="p-6 rounded-xl bg-white/5 border border-white/10">
                        <div class="w-12 h-12 bg-emerald-500/20 rounded-lg flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-emerald-500" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                 stroke-width="2">
                                <path d="M4 7V4h16v3M9 20h6M12 4v16"/>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold mb-2">Учет Запасов</h3>
                        <p class="text-gray-400">Автоматический учет складских запасов в реальном времени с точностью до
                            единицы товара.</p>
                    </div>
                    <div class="p-6 rounded-xl bg-white/5 border border-white/10">
                        <div class="w-12 h-12 bg-emerald-500/20 rounded-lg flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-emerald-500" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                 stroke-width="2">
                                <path
                                    d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7m6 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7"/>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold mb-2">Управление Поставками</h3>
                        <p class="text-gray-400">Планирование и отслеживание поставок, автоматическое обновление
                            остатков.</p>
                    </div>
                    <div class="p-6 rounded-xl bg-white/5 border border-white/10">
                        <div class="w-12 h-12 bg-emerald-500/20 rounded-lg flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-emerald-500" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                 stroke-width="2">
                                <path d="M16 8v8m-8-8v8M4 4h16v16H4V4z"/>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold mb-2">Штрих-кодирование</h3>
                        <p class="text-gray-400">Маркировка и идентификация товаров с помощью штрих-кодов и
                            QR-кодов.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Analytics Overview -->
        <section id="analytics" class="py-20 px-4">
            <div class="container mx-auto">
                <div class="grid md:grid-cols-2 gap-12 items-center">
                    <div>
                        <h2 class="text-3xl font-bold mb-6">Аналитика Складских<br>
                            <span class="text-emerald-500">Операций</span>
                        </h2>
                        <div class="space-y-6">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 bg-emerald-500/20 rounded-lg flex items-center justify-center">
                                    <svg class="w-6 h-6 text-emerald-500" viewBox="0 0 24 24" fill="none"
                                         stroke="currentColor" stroke-width="2">
                                        <path d="M8 7v14m8-14v14M4 7h16M4 3h16"/>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="font-semibold mb-1">Оборачиваемость запасов</h3>
                                    <p class="text-gray-400">Анализ скорости движения товаров на складе</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 bg-emerald-500/20 rounded-lg flex items-center justify-center">
                                    <svg class="w-6 h-6 text-emerald-500" viewBox="0 0 24 24" fill="none"
                                         stroke="currentColor" stroke-width="2">
                                        <path d="M16 8v8M8 8v8M4 4h16v16H4V4z"/>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="font-semibold mb-1">Прогнозирование спроса</h3>
                                    <p class="text-gray-400">Автоматическое прогнозирование потребности в товарах</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white/5 rounded-xl p-6">
                        <img
                            src="https://images.unsplash.com/photo-1542744173-8e7e53415bb0?auto=format&fit=crop&w=800&q=80"
                            alt="Аналитика складских операций"
                            class="rounded-lg w-full">
                    </div>
                </div>
            </div>
        </section>

        <!-- Inventory Transfer System -->
        <section class="py-16 md:py-24 bg-secondary/30">
            <div class="container mx-auto px-4 md:px-6">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                    <div><h2 class="text-3xl font-bold tracking-tight mb-6">Перемещение товаров <span
                                class="text-primary">между складами</span></h2>
                        <p class="text-muted-foreground mb-8">Отправляйте и получайте запчасти между складами всего за
                            несколько кликов. Никаких сложностей с оформлением документов или учётом перемещений.</p>
                        <div class="space-y-4 mb-8">
                            <div class="flex items-center">
                                <div class="h-5 w-5 rounded-full bg-primary mr-3 flex items-center justify-center">
                                    <svg class="h-3 w-3 text-background"
                                         xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                         stroke="currentColor" stroke-width="3" stroke-linecap="round"
                                         stroke-linejoin="round">
                                        <polyline points="20 6 9 17 4 12"></polyline>
                                    </svg>
                                </div>
                                <span>Мгновенное оформление трансфера деталей</span></div>
                            <div class="flex items-center">
                                <div class="h-5 w-5 rounded-full bg-primary mr-3 flex items-center justify-center">
                                    <svg class="h-3 w-3 text-background"
                                         xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                         stroke="currentColor" stroke-width="3" stroke-linecap="round"
                                         stroke-linejoin="round">
                                        <polyline points="20 6 9 17 4 12"></polyline>
                                    </svg>
                                </div>
                                <span>Разделение партий деталей без усилий</span></div>
                            <div class="flex items-center">
                                <div class="h-5 w-5 rounded-full bg-primary mr-3 flex items-center justify-center">
                                    <svg class="h-3 w-3 text-background"
                                         xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                         stroke="currentColor" stroke-width="3" stroke-linecap="round"
                                         stroke-linejoin="round">
                                        <polyline points="20 6 9 17 4 12"></polyline>
                                    </svg>
                                </div>
                                <span>Запрос на пополнение склада с комментариями</span></div>
                            <div class="flex items-center">
                                <div class="h-5 w-5 rounded-full bg-primary mr-3 flex items-center justify-center">
                                    <svg class="h-3 w-3 text-background"
                                         xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                         stroke="currentColor" stroke-width="3" stroke-linecap="round"
                                         stroke-linejoin="round">
                                        <polyline points="20 6 9 17 4 12"></polyline>
                                    </svg>
                                </div>
                                <span>Автоматическое формирование накладных</span></div>
                        </div>
                        <div class="grid grid-cols-2 gap-4 mb-8">
                            <div class="text-center"><h3 class="text-4xl font-bold text-primary mb-2">100%</h3>
                                <p class="text-sm text-muted-foreground">Точность учёта</p></div>
                            <div class="text-center"><h3 class="text-4xl font-bold text-primary mb-2">12+</h3>
                                <p class="text-sm text-muted-foreground">Складов в сети</p></div>
                        </div>
                        <button
                            class="inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 [&amp;_svg]:pointer-events-none [&amp;_svg]:size-4 [&amp;_svg]:shrink-0 bg-primary text-primary-foreground hover:bg-primary/90 h-10 px-4 py-2 glow-effect undefined">
                            Начать перемещение
                        </button>
                    </div>
                    <div class="relative">
                        <div
                            class="rounded-lg border text-card-foreground bg-card border-muted overflow-hidden p-0 shadow-xl">
                            <div class="p-6">
                                <div class="flex items-center mb-6">
                                    <div
                                        class="w-10 h-10 rounded-full bg-primary/20 flex items-center justify-center mr-3">
                                        <svg class="h-5 w-5 text-primary"
                                             xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                             stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                             stroke-linejoin="round">
                                            <path d="m5 8 2 2 4-4"></path>
                                            <path d="M2 12h4"></path>
                                            <path d="M2 16h8"></path>
                                            <path d="M11 12h10"></path>
                                            <path d="M18 8l3 3-3 3"></path>
                                            <path d="M22 16H11"></path>
                                        </svg>
                                    </div>
                                    <h3 class="text-lg font-semibold">Отправить детали</h3></div>
                                <div class="mb-8">
                                    <div class="bg-secondary/50 p-4 rounded-lg mb-6">
                                        <div class="flex items-center mb-4">
                                            <div
                                                class="w-10 h-10 rounded-full bg-secondary text-center flex items-center justify-center mr-3">
                                                <span class="text-muted-foreground font-medium">ЦС</span>
                                            </div>
                                            <div><p class="font-medium">Центральный Склад</p>
                                                <p class="text-xs text-muted-foreground">
                                                    @centralsklad</p></div>
                                        </div>
                                        <div class="flex items-end justify-between mb-2">
                                            <div><label
                                                    class="text-xs text-muted-foreground mb-1 block">Количество</label>
                                                <div class="flex items-end"><span
                                                        class="text-2xl font-bold">25 шт.</span>
                                                </div>
                                            </div>
                                            <div class="text-right"><p class="text-xs text-muted-foreground">Доступно
                                                    на складе</p>
                                                <p class="text-sm">1 256 шт.</p></div>
                                        </div>
                                    </div>
                                    <div class="mb-6"><label
                                            class="text-xs text-muted-foreground mb-1 block">Наименование</label>
                                        <div class="p-3 bg-secondary/30 rounded-lg border border-muted"><p
                                                class="text-sm">Двигатель воздушного фильтра 🔧</p>
                                        </div>
                                    </div>
                                    <button
                                        class="inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 [&amp;_svg]:pointer-events-none [&amp;_svg]:size-4 [&amp;_svg]:shrink-0 bg-primary text-primary-foreground hover:bg-primary/90 h-10 px-4 py-2 glow-effect w-full">
                                        Отправить детали
                                    </button>
                                </div>
                                <div><h4 class="text-sm font-medium mb-3">Недавние склады</h4>
                                    <div class="flex space-x-4 overflow-x-auto pb-2">
                                        <div class="flex flex-col items-center">
                                            <div
                                                class="w-12 h-12 rounded-full bg-secondary text-center flex items-center justify-center mb-1">
                                                <span class="text-muted-foreground font-medium">СЗ</span>
                                            </div>
                                            <span class="text-xs">Север</span></div>
                                        <div class="flex flex-col items-center">
                                            <div
                                                class="w-12 h-12 rounded-full bg-secondary text-center flex items-center justify-center mb-1">
                                                <span class="text-muted-foreground font-medium">ЮГ</span>
                                            </div>
                                            <span class="text-xs">Южный</span></div>
                                        <div class="flex flex-col items-center">
                                            <div
                                                class="w-12 h-12 rounded-full bg-secondary text-center flex items-center justify-center mb-1">
                                                <span class="text-muted-foreground font-medium">ВС</span>
                                            </div>
                                            <span class="text-xs">Восток</span></div>
                                        <div class="flex flex-col items-center">
                                            <div
                                                class="w-12 h-12 rounded-full bg-secondary text-center flex items-center justify-center mb-1">
                                                <span class="text-muted-foreground font-medium">ЗС</span>
                                            </div>
                                            <span class="text-xs">Запад</span></div>
                                        <div class="flex flex-col items-center">
                                            <div
                                                class="w-12 h-12 rounded-full bg-secondary text-center flex items-center justify-center mb-1 border border-dashed border-muted-foreground">
                                                <svg class="h-5 w-5 text-muted-foreground"
                                                     xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                                     stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                                     stroke-linejoin="round">
                                                    <path d="M5 12h14"></path>
                                                    <path d="M12 5v14"></path>
                                                </svg>
                                            </div>
                                            <span class="text-xs">Добавить</span></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div
                            class="absolute -bottom-6 -left-6 bg-card border border-muted rounded-lg p-3 shadow-lg transform -rotate-6 hidden md:block">
                            <div class="flex items-center space-x-2">
                                <svg class="h-5 w-5 text-primary"
                                     xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                     stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                     stroke-linejoin="round">
                                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10"></path>
                                    <path d="m9 12 2 2 4-4"></path>
                                </svg>
                                <span class="text-sm">Защищено и надежно</span></div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Pricing Plans -->
        <section id="pricing" class="py-24">
            <div class="container mx-auto px-4 md:px-6">
                <div
                    class="text-center max-w-3xl mx-auto mb-16"><h2

                        class="text-3xl font-bold tracking-tight mb-4">Выберите тариф для эффективного <br
                        ><span

                            class="text-primary">управления вашим складом</span></h2>
                    <p
                        class="text-muted-foreground">Подберите тариф, который соответствует вашим потребностям. Все
                        тарифы
                        включают 14-дневный пробный период.</p></div>
                <div
                    class="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-6xl mx-auto">
                    <div
                        class="rounded-lg border bg-card text-card-foreground shadow-sm relative overflow-hidden border-muted hover:border-primary/30 transition-all duration-300">
                        <div class="flex flex-col space-y-1.5 p-6 pb-4"><h3

                                class="text-xl font-semibold mb-1">Базовый тариф</h3>
                            <p
                                class="text-muted-foreground text-sm mb-4">Для небольших складов с минимальной
                                номенклатурой</p>
                            <div class="flex items-end"><span
                                    class="text-4xl font-bold">0 ₽</span><span
                                    class="text-muted-foreground ml-2">/месяц</span></div>
                        </div>
                        <div class="p-6 pt-0">
                            <ul class="space-y-3 mb-8">
                                <li class="flex items-start">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                         fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                         stroke-linejoin="round"
                                         class="lucide lucide-check h-5 w-5 text-primary mr-2 mt-0.5 flex-shrink-0">
                                        <path d="M20 6 9 17l-5-5"></path>
                                    </svg>
                                    <span>Базовое управление складом</span></li>
                                <li class="flex items-start">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                         fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                         stroke-linejoin="round"
                                         class="lucide lucide-check h-5 w-5 text-primary mr-2 mt-0.5 flex-shrink-0">
                                        <path d="M20 6 9 17l-5-5"></path>
                                    </svg>
                                    <span>До 3 складских помещений</span></li>
                                <li class="flex items-start">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                         fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                         stroke-linejoin="round"
                                         class="lucide lucide-check h-5 w-5 text-primary mr-2 mt-0.5 flex-shrink-0">
                                        <path d="M20 6 9 17l-5-5"></path>
                                    </svg>
                                    <span>Ограниченное количество операций в месяц</span></li>
                                <li class="flex items-start">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                         fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                         stroke-linejoin="round"
                                         class="lucide lucide-check h-5 w-5 text-primary mr-2 mt-0.5 flex-shrink-0">
                                        <path d="M20 6 9 17l-5-5"></path>
                                    </svg>
                                    <span>Мобильное приложение</span></li>
                                <li class="flex items-start">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                         fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                         stroke-linejoin="round"
                                         class="lucide lucide-check h-5 w-5 text-primary mr-2 mt-0.5 flex-shrink-0">
                                        <path d="M20 6 9 17l-5-5"></path>
                                    </svg>
                                    <span>Поддержка по электронной почте</span></li>
                                <li class="flex items-start text-muted-foreground">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                         fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                         stroke-linejoin="round"
                                         class="lucide lucide-x h-5 w-5 mr-2 mt-0.5 flex-shrink-0">
                                        <path d="M18 6 6 18"></path>
                                        <path d="m6 6 12 12"></path>
                                    </svg>
                                    <span>Нет поддержки штрих-кодов</span></li>
                                <li class="flex items-start text-muted-foreground">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                         fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                         stroke-linejoin="round"
                                         class="lucide lucide-x h-5 w-5 mr-2 mt-0.5 flex-shrink-0">
                                        <path d="M18 6 6 18"></path>
                                        <path d="m6 6 12 12"></path>
                                    </svg>
                                    <span>Нет пользовательских категорий</span></li>
                                <li class="flex items-start text-muted-foreground">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                         fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                         stroke-linejoin="round"
                                         class="lucide lucide-x h-5 w-5 mr-2 mt-0.5 flex-shrink-0">
                                        <path d="M18 6 6 18"></path>
                                        <path d="m6 6 12 12"></path>
                                    </svg>
                                    <span>Нет аналитики запасов</span></li>
                            </ul>
                            <button
                                class="inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 [&amp;_svg]:pointer-events-none [&amp;_svg]:size-4 [&amp;_svg]:shrink-0 border border-input bg-background hover:bg-accent hover:text-accent-foreground h-10 px-4 py-2 w-full">
                                Начать использование
                            </button>
                        </div>
                    </div>
                    <div
                        class="rounded-lg border bg-card text-card-foreground relative overflow-hidden border-primary shadow-lg shadow-primary/10 transition-all duration-300">
                        <div data-replit-metadata="client/src/components/PricingPlans.tsx:86:12"
                             data-component-name="div"
                             class="absolute top-0 right-0 bg-primary text-background text-xs font-bold uppercase py-1 px-3">
                            Популярный
                        </div>
                        <div class="flex flex-col space-y-1.5 p-6 pb-4"><h3

                                class="text-xl font-semibold mb-1">Профессиональный</h3>
                            <p
                                class="text-muted-foreground text-sm mb-4">Для активного управления складскими
                                запасами</p>
                            <div class="flex items-end"><span
                                    class="text-4xl font-bold">20 ₽</span><span
                                    class="text-muted-foreground ml-2">/месяц</span></div>
                        </div>
                        <div class="p-6 pt-0">
                            <ul class="space-y-3 mb-8">
                                <li class="flex items-start">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                         fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                         stroke-linejoin="round"
                                         class="lucide lucide-check h-5 w-5 text-primary mr-2 mt-0.5 flex-shrink-0">
                                        <path d="M20 6 9 17l-5-5"></path>
                                    </svg>
                                    <span>Расширенное управление складом</span></li>
                                <li class="flex items-start">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                         fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                         stroke-linejoin="round"
                                         class="lucide lucide-check h-5 w-5 text-primary mr-2 mt-0.5 flex-shrink-0">
                                        <path d="M20 6 9 17l-5-5"></path>
                                    </svg>
                                    <span>Неограниченное количество помещений</span></li>
                                <li class="flex items-start">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                         fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                         stroke-linejoin="round"
                                         class="lucide lucide-check h-5 w-5 text-primary mr-2 mt-0.5 flex-shrink-0">
                                        <path d="M20 6 9 17l-5-5"></path>
                                    </svg>
                                    <span>Неограниченное количество операций</span></li>
                                <li class="flex items-start">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                         fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                         stroke-linejoin="round"
                                         class="lucide lucide-check h-5 w-5 text-primary mr-2 mt-0.5 flex-shrink-0">
                                        <path d="M20 6 9 17l-5-5"></path>
                                    </svg>
                                    <span>Мобильное приложение</span></li>
                                <li class="flex items-start">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                         fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                         stroke-linejoin="round"
                                         class="lucide lucide-check h-5 w-5 text-primary mr-2 mt-0.5 flex-shrink-0">
                                        <path d="M20 6 9 17l-5-5"></path>
                                    </svg>
                                    <span>Приоритетная поддержка</span></li>
                                <li class="flex items-start">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                         fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                         stroke-linejoin="round"
                                         class="lucide lucide-check h-5 w-5 text-primary mr-2 mt-0.5 flex-shrink-0">
                                        <path d="M20 6 9 17l-5-5"></path>
                                    </svg>
                                    <span>Поддержка штрих-кодов</span></li>
                                <li class="flex items-start">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                         fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                         stroke-linejoin="round"
                                         class="lucide lucide-check h-5 w-5 text-primary mr-2 mt-0.5 flex-shrink-0">
                                        <path d="M20 6 9 17l-5-5"></path>
                                    </svg>
                                    <span>Пользовательские категории</span></li>
                                <li class="flex items-start text-muted-foreground">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                         fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                         stroke-linejoin="round"
                                         class="lucide lucide-x h-5 w-5 mr-2 mt-0.5 flex-shrink-0">
                                        <path d="M18 6 6 18"></path>
                                        <path d="m6 6 12 12"></path>
                                    </svg>
                                    <span>Нет расширенной аналитики</span></li>
                            </ul>
                            <button
                                class="inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 [&amp;_svg]:pointer-events-none [&amp;_svg]:size-4 [&amp;_svg]:shrink-0 bg-primary text-primary-foreground hover:bg-primary/90 h-10 px-4 py-2 glow-effect w-full">
                                Начать использование
                            </button>
                        </div>
                    </div>
                    <div
                        class="rounded-lg border bg-card text-card-foreground shadow-sm relative overflow-hidden border-muted hover:border-primary/30 transition-all duration-300">
                        <div class="flex flex-col space-y-1.5 p-6 pb-4"><h3 class="text-xl font-semibold mb-1">
                                Корпоративный</h3>
                            <p class="text-muted-foreground text-sm mb-4">Для производственных предприятий и сетей
                                складов</p>
                            <div class="flex items-end"><span
                                    class="text-4xl font-bold">50 ₽</span><span
                                    class="text-muted-foreground ml-2">/месяц</span></div>
                        </div>
                        <div class="p-6 pt-0">
                            <ul class="space-y-3 mb-8">
                                <li class="flex items-start">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                         fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                         stroke-linejoin="round"
                                         class="lucide lucide-check h-5 w-5 text-primary mr-2 mt-0.5 flex-shrink-0">
                                        <path d="M20 6 9 17l-5-5"></path>
                                    </svg>
                                    <span>Все функции Профессионального тарифа</span></li>
                                <li class="flex items-start">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                         fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                         stroke-linejoin="round"
                                         class="lucide lucide-check h-5 w-5 text-primary mr-2 mt-0.5 flex-shrink-0">
                                        <path d="M20 6 9 17l-5-5"></path>
                                    </svg>
                                    <span>Несколько учетных записей пользователей</span></li>
                                <li class="flex items-start">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                         fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                         stroke-linejoin="round"
                                         class="lucide lucide-check h-5 w-5 text-primary mr-2 mt-0.5 flex-shrink-0">
                                        <path d="M20 6 9 17l-5-5"></path>
                                    </svg>
                                    <span>Расширенная аналитика запасов</span></li>
                                <li class="flex items-start">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                         fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                         stroke-linejoin="round"
                                         class="lucide lucide-check h-5 w-5 text-primary mr-2 mt-0.5 flex-shrink-0">
                                        <path d="M20 6 9 17l-5-5"></path>
                                    </svg>
                                    <span>Отслеживание расходов на содержание</span></li>
                                <li class="flex items-start">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                         fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                         stroke-linejoin="round"
                                         class="lucide lucide-check h-5 w-5 text-primary mr-2 mt-0.5 flex-shrink-0">
                                        <path d="M20 6 9 17l-5-5"></path>
                                    </svg>
                                    <span>Доступ к API</span></li>
                                <li class="flex items-start">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                         fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                         stroke-linejoin="round"
                                         class="lucide lucide-check h-5 w-5 text-primary mr-2 mt-0.5 flex-shrink-0">
                                        <path d="M20 6 9 17l-5-5"></path>
                                    </svg>
                                    <span>Выделенный менеджер по работе с клиентами</span></li>
                                <li class="flex items-start">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                         fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                         stroke-linejoin="round"
                                         class="lucide lucide-check h-5 w-5 text-primary mr-2 mt-0.5 flex-shrink-0">
                                        <path d="M20 6 9 17l-5-5"></path>
                                    </svg>
                                    <span>Индивидуальные интеграции</span></li>
                            </ul>
                            <button
                                class="inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 [&amp;_svg]:pointer-events-none [&amp;_svg]:size-4 [&amp;_svg]:shrink-0 border border-input bg-background hover:bg-accent hover:text-accent-foreground h-10 px-4 py-2 w-full">
                                Начать использование
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Testimonials -->
        <section id="testimonials" class="py-20 px-4">
            <div class="container mx-auto">
                <h2 class="text-3xl font-bold text-center mb-16">Что Говорят<br>
                    <span class="text-emerald-500">Наши Клиенты</span>
                </h2>
                <div class="grid md:grid-cols-3 gap-8">
                    <div class="p-6 rounded-xl bg-white/5 border border-white/10">
                        <div class="flex items-center gap-4 mb-4">
                            <img
                                src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?auto=format&fit=crop&w=100&h=100&q=80"
                                alt="Александр Петров"
                                class="w-12 h-12 rounded-full">
                            <div>
                                <h4 class="font-semibold">Александр Петров</h4>
                                <p class="text-gray-400">Директор по логистике</p>
                            </div>
                        </div>
                        <p class="text-gray-400">"SemixPro полностью изменил наш подход к управлению складом. Теперь все
                            процессы автоматизированы и прозрачны."</p>
                    </div>
                    <div class="p-6 rounded-xl bg-white/5 border border-white/10">
                        <div class="flex items-center gap-4 mb-4">
                            <img
                                src="https://images.unsplash.com/photo-1438761681033-6461ffad8d80?auto=format&fit=crop&w=100&h=100&q=80"
                                alt="Елена Смирнова"
                                class="w-12 h-12 rounded-full">
                            <div>
                                <h4 class="font-semibold">Елена Смирнова</h4>
                                <p class="text-gray-400">Руководитель склада</p>
                            </div>
                        </div>
                        <p class="text-gray-400">"Удобный интерфейс и мощная аналитика помогают принимать верные решения
                            по
                            управлению запасами."</p>
                    </div>
                    <div class="p-6 rounded-xl bg-white/5 border border-white/10">
                        <div class="flex items-center gap-4 mb-4">
                            <img
                                src="https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&w=100&h=100&q=80"
                                alt="Михаил Иванов"
                                class="w-12 h-12 rounded-full">
                            <div>
                                <h4 class="font-semibold">Михаил Иванов</h4>
                                <p class="text-gray-400">Генеральный директор</p>
                            </div>
                        </div>
                        <p class="text-gray-400">"Внедрение SemixPro позволило нам сократить издержки на складское
                            хранение
                            на 30%."</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Company Stats -->
        <section class="py-20 px-4 bg-white/5">
            <div class="container mx-auto">
                <div class="grid md:grid-cols-4 gap-8 text-center">
                    <div>
                        <div class="text-4xl font-bold text-emerald-500 mb-2">22+</div>
                        <div class="text-gray-400">Года на рынке</div>
                    </div>
                    <div>
                        <div class="text-4xl font-bold text-emerald-500 mb-2">76K+</div>
                        <div class="text-gray-400">Клиентов</div>
                    </div>
                    <div>
                        <div class="text-4xl font-bold text-emerald-500 mb-2">5.2M</div>
                        <div class="text-gray-400">Операций в день</div>
                    </div>
                    <div>
                        <div class="text-4xl font-bold text-emerald-500 mb-2">35.8M+</div>
                        <div class="text-gray-400">Товаров в базе</div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Contact Form -->
        <section data-replit-metadata="client/src/components/ContactSection.tsx:59:4" data-component-name="section"
                 id="contact" class="py-24 relative overflow-hidden">
            <div data-replit-metadata="client/src/components/ContactSection.tsx:61:6" data-component-name="div"
                 class="absolute top-1/4 right-1/4 w-64 h-64 bg-primary/10 rounded-full filter blur-3xl"></div>
            <div data-replit-metadata="client/src/components/ContactSection.tsx:62:6" data-component-name="div"
                 class="absolute bottom-1/4 left-1/4 w-96 h-96 bg-primary/10 rounded-full filter blur-3xl"></div>
            <div data-replit-metadata="client/src/components/ContactSection.tsx:64:6" data-component-name="div"
                 class="container mx-auto px-4 md:px-6 relative">
                <div data-replit-metadata="client/src/components/ContactSection.tsx:65:8" data-component-name="div"
                     class="text-center max-w-3xl mx-auto mb-16"><h2
                        data-replit-metadata="client/src/components/ContactSection.tsx:66:10" data-component-name="h2"
                        class="text-3xl font-bold tracking-tight mb-4">Начните управлять <br
                            data-replit-metadata="client/src/components/ContactSection.tsx:67:30"
                            data-component-name="br"><span
                            data-replit-metadata="client/src/components/ContactSection.tsx:68:12"
                            data-component-name="span"
                            class="text-primary">вашими складами</span></h2>
                    <p data-replit-metadata="client/src/components/ContactSection.tsx:70:10" data-component-name="p"
                       class="text-muted-foreground">Свяжитесь с нами для получения дополнительной информации или начала
                        работы с InventoryPro.</p></div>
                <div data-replit-metadata="client/src/components/ContactSection.tsx:75:8" data-component-name="div"
                     class="max-w-3xl mx-auto">
                    <form data-replit-metadata="client/src/components/ContactSection.tsx:77:12"
                          data-component-name="form"
                          class="space-y-6">
                        <div data-replit-metadata="client/src/components/ContactSection.tsx:78:14"
                             data-component-name="div"
                             class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div data-replit-metadata="client/src/components/ContactSection.tsx:83:18"
                                 data-component-name="FormItem" class="space-y-2"><label
                                    data-replit-metadata="client/src/components/ContactSection.tsx:84:22"
                                    data-component-name="FormLabel"
                                    class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70"
                                    for=":r3:-form-item">Ваше имя</label><input
                                    data-replit-metadata="client/src/components/ContactSection.tsx:86:24"
                                    data-component-name="Input"
                                    class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium file:text-foreground placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                                    placeholder="Иван Петров" name="name" id=":r3:-form-item"
                                    aria-describedby=":r3:-form-item-description" aria-invalid="false" value=""></div>
                            <div data-replit-metadata="client/src/components/ContactSection.tsx:97:18"
                                 data-component-name="FormItem" class="space-y-2"><label
                                    data-replit-metadata="client/src/components/ContactSection.tsx:98:22"
                                    data-component-name="FormLabel"
                                    class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70"
                                    for=":r4:-form-item">Email адрес</label><input
                                    data-replit-metadata="client/src/components/ContactSection.tsx:100:24"
                                    data-component-name="Input"
                                    class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium file:text-foreground placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                                    placeholder="ivan@example.com" name="email" id=":r4:-form-item"
                                    aria-describedby=":r4:-form-item-description" aria-invalid="false" value=""></div>
                        </div>
                        <div data-replit-metadata="client/src/components/ContactSection.tsx:112:16"
                             data-component-name="FormItem" class="space-y-2"><label
                                data-replit-metadata="client/src/components/ContactSection.tsx:113:20"
                                data-component-name="FormLabel"
                                class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70"
                                for=":r5:-form-item">Сообщение</label><textarea
                                data-replit-metadata="client/src/components/ContactSection.tsx:115:22"
                                data-component-name="Textarea"
                                class="flex w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 min-h-[150px]"
                                placeholder="Расскажите, как мы можем помочь с управлением вашим складом..."
                                name="message"
                                id=":r5:-form-item" aria-describedby=":r5:-form-item-description"
                                aria-invalid="false"></textarea></div>
                        <div data-replit-metadata="client/src/components/ContactSection.tsx:126:14"
                             data-component-name="div" class="flex justify-center">
                            <button data-replit-metadata="client/src/components/ContactSection.tsx:127:16"
                                    data-component-name="ButtonGlow"
                                    class="inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 [&amp;_svg]:pointer-events-none [&amp;_svg]:size-4 [&amp;_svg]:shrink-0 bg-primary text-primary-foreground hover:bg-primary/90 h-10 px-4 py-2 glow-effect undefined"
                                    type="submit">Отправить сообщение
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </section>
    </main>

    <footer class="bg-white/5 border-t border-white/10 py-12">
        <div class="container mx-auto px-4">
            <div class="grid md:grid-cols-4 gap-8">
                <div>
                    <div class="flex items-center space-x-2 mb-6">
                        <svg class="w-8 h-8 text-emerald-500" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                             stroke-width="2">
                            <path d="M20 7L12 3L4 7M20 7L12 11M20 7V17L12 21M12 11L4 7M12 11V21M4 7V17L12 21"/>
                        </svg>
                        <span class="font-bold text-xl">SemixPro</span>
                    </div>
                    <p class="text-gray-400">Эффективное управление складом и запчастями для вашего бизнеса</p>
                    <div class="flex space-x-4 mt-2"><a data-replit-metadata="client/src/components/Footer.tsx:77:14"
                                                        data-component-name="a" href="#"
                                                        class="text-muted-foreground hover:text-brand-accent transition-colors">
                            <svg data-replit-metadata="client/src/components/Footer.tsx:78:16" data-component-name="svg"
                                 class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path data-replit-metadata="client/src/components/Footer.tsx:79:18"
                                      data-component-name="path" fill-rule="evenodd"
                                      d="M22 12c0-5.523-4.477-10-10-10S2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.878v-6.987h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.89h-2.33v6.988C18.343 21.128 22 16.991 22 12z"
                                      clip-rule="evenodd"></path>
                            </svg>
                        </a><a data-replit-metadata="client/src/components/Footer.tsx:82:14" data-component-name="a"
                               href="#" class="text-muted-foreground hover:text-brand-accent transition-colors">
                            <svg data-replit-metadata="client/src/components/Footer.tsx:83:16" data-component-name="svg"
                                 class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path data-replit-metadata="client/src/components/Footer.tsx:84:18"
                                      data-component-name="path"
                                      d="M8.29 20.251c7.547 0 11.675-6.253 11.675-11.675 0-.178 0-.355-.012-.53A8.348 8.348 0 0022 5.92a8.19 8.19 0 01-2.357.646 4.118 4.118 0 001.804-2.27 8.224 8.224 0 01-2.605.996 4.107 4.107 0 00-6.993 3.743 11.65 11.65 0 01-8.457-4.287 4.106 4.106 0 001.27 5.477A4.072 4.072 0 012.8 9.713v.052a4.105 4.105 0 003.292 4.022 4.095 4.095 0 01-1.853.07 4.108 4.108 0 003.834 2.85A8.233 8.233 0 012 18.407a11.616 11.616 0 006.29 1.84"></path>
                            </svg>
                        </a><a data-replit-metadata="client/src/components/Footer.tsx:87:14" data-component-name="a"
                               href="#" class="text-muted-foreground hover:text-brand-accent transition-colors">
                            <svg data-replit-metadata="client/src/components/Footer.tsx:88:16" data-component-name="svg"
                                 class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path data-replit-metadata="client/src/components/Footer.tsx:89:18"
                                      data-component-name="path" fill-rule="evenodd"
                                      d="M12.315 2c2.43 0 2.784.013 3.808.06 1.064.049 1.791.218 2.427.465a4.902 4.902 0 011.772 1.153 4.902 4.902 0 011.153 1.772c.247.636.416 1.363.465 2.427.048 1.067.06 1.407.06 4.123v.08c0 2.643-.012 2.987-.06 4.043-.049 1.064-.218 1.791-.465 2.427a4.902 4.902 0 01-1.153 1.772 4.902 4.902 0 01-1.772 1.153c-.636.247-1.363.416-2.427.465-1.067.048-1.407.06-4.123.06h-.08c-2.643 0-2.987-.012-4.043-.06-1.064-.049-1.791-.218-2.427-.465a4.902 4.902 0 01-1.772-1.153 4.902 4.902 0 01-1.153-1.772c-.247-.636-.416-1.363-.465-2.427-.047-1.024-.06-1.379-.06-3.808v-.63c0-2.43.013-2.784.06-3.808.049-1.064.218-1.791.465-2.427a4.902 4.902 0 011.153-1.772A4.902 4.902 0 015.45 2.525c.636-.247 1.363-.416 2.427-.465C8.901 2.013 9.256 2 11.685 2h.63zm-.081 1.802h-.468c-2.456 0-2.784.011-3.807.058-.975.045-1.504.207-1.857.344-.467.182-.8.398-1.15.748-.35.35-.566.683-.748 1.15-.137.353-.3.882-.344 1.857-.047 1.023-.058 1.351-.058 3.807v.468c0 2.456.011 2.784.058 3.807.045.975.207 1.504.344 1.857.182.466.399.8.748 1.15.35.35.683.566 1.15.748.353.137.882.3 1.857.344 1.054.048 1.37.058 4.041.058h.08c2.597 0 2.917-.01 3.96-.058.976-.045 1.505-.207 1.858-.344.466-.182.8-.398 1.15-.748.35-.35.566-.683.748-1.15.137-.353.3-.882.344-1.857.048-1.055.058-1.37.058-4.041v-.08c0-2.597-.01-2.917-.058-3.96-.045-.976-.207-1.505-.344-1.858a3.097 3.097 0 00-.748-1.15 3.098 3.098 0 00-1.15-.748c-.353-.137-.882-.3-1.857-.344-1.023-.047-1.351-.058-3.807-.058zM12 6.865a5.135 5.135 0 110 10.27 5.135 5.135 0 010-10.27zm0 1.802a3.333 3.333 0 100 6.666 3.333 3.333 0 000-6.666zm5.338-3.205a1.2 1.2 0 110 2.4 1.2 1.2 0 010-2.4z"
                                      clip-rule="evenodd"></path>
                            </svg>
                        </a><a data-replit-metadata="client/src/components/Footer.tsx:92:14" data-component-name="a"
                               href="#" class="text-muted-foreground hover:text-brand-accent transition-colors">
                            <svg data-replit-metadata="client/src/components/Footer.tsx:93:16" data-component-name="svg"
                                 class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path data-replit-metadata="client/src/components/Footer.tsx:94:18"
                                      data-component-name="path" fill-rule="evenodd"
                                      d="M12 2C6.477 2 2 6.484 2 12.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.531 1.032 1.531 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.202 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.943.359.309.678.92.678 1.855 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.019 10.019 0 0022 12.017C22 6.484 17.522 2 12 2z"
                                      clip-rule="evenodd"></path>
                            </svg>
                        </a>
                    </div>
                </div>
                <div>
                    <h3 class="font-semibold mb-4">Компания</h3>
                    <ul class="space-y-2 text-gray-400">
                        <li><a href="#" class="hover:text-emerald-500 transition-colors">О нас</a></li>
                        <li><a href="#" class="hover:text-emerald-500 transition-colors">Карьера</a></li>
                        <li><a href="#" class="hover:text-emerald-500 transition-colors">Контакты</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="font-semibold mb-4">Ресурсы</h3>
                    <ul class="space-y-2 text-gray-400">
                        <li><a href="#" class="hover:text-emerald-500 transition-colors">Документация</a></li>
                        <li><a href="#" class="hover:text-emerald-500 transition-colors">Блог</a></li>
                        <li><a href="#" class="hover:text-emerald-500 transition-colors">Поддержка</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="font-semibold mb-4">Правовая информация</h3>
                    <ul class="space-y-2 text-gray-400">
                        <li><a href="#" class="hover:text-emerald-500 transition-colors">Условия использования</a></li>
                        <li><a href="#" class="hover:text-emerald-500 transition-colors">Конфиденциальность</a></li>
                    </ul>
                </div>
            </div>
            <div class="mt-12 pt-8 border-t border-white/10 text-center text-gray-400">
                <p>&copy; 2024 SemixPro. Все права защищены.</p>
            </div>
        </div>
    </footer>
</div>
@livewireScripts
</body>
</html>
