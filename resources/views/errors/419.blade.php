{{-- resources/views/errors/419.blade.php --}}
    <!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>419 — Page Expired</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite(['resources/css/app.css','resources/js/app.js'])
</head>
<body class="h-full bg-[#0f172a] text-slate-200 antialiased">
<div class="min-h-full flex flex-col items-center justify-center px-6 py-12">
    <div class="w-full max-w-xl">
        <div class="text-center">
            <p class="text-8xl font-black tracking-tight text-slate-500 select-none">419</p>
            <h1 class="mt-6 text-3xl font-bold">Page Expired</h1>
            <p class="mt-3 text-slate-400">
                Сессия истекла или не совпал CSRF токен. Это нормально после долгого простоя или при открытии старой вкладки.
            </p>
        </div>

        <div class="mt-8 grid gap-3 sm:grid-cols-2">
            <a href="{{ url()->previous() }}"
               class="inline-flex items-center justify-center rounded-2xl border border-slate-700 px-4 py-3 text-sm font-medium hover:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                ← Вернуться назад
            </a>

            <a href="{{ route('login') }}"
               class="inline-flex items-center justify-center rounded-2xl bg-indigo-600 px-4 py-3 text-sm font-semibold hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                Войти заново
            </a>
        </div>

        <div class="mt-6 text-center text-xs text-slate-500">
            <button onclick="window.location.reload()"
                    class="underline underline-offset-4 hover:text-slate-300">
                Обновить страницу
            </button>
        </div>
    </div>
</div>
</body>
</html>
