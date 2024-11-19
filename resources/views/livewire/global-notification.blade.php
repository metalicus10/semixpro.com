<div x-data="{ visible: @entangle('visible'), type: @entangle('type'), message: @entangle('message') }"
    x-show="visible"
    @hide-notification.window="setTimeout(() => visible = false, $event.detail.timeout || 5000)"
    class="fixed flex justify-center left-1/2 transform -translate-x-1/3 text-white text-center p-4 rounded-lg mb-6 transition-opacity duration-1000 z-50 top-[15%] w-1/2"
    :class="{
        'bg-green-500 text-white': type === 'success',
        'bg-red-500 text-white': type === 'error',
        'bg-yellow-500 text-white': type === 'warning',
        'bg-blue-500 text-white': type === 'info'
    }"
    x-transition:enter="opacity-0"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="opacity-100"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    style="display: none;"
>
    {{ $message }}
</div>