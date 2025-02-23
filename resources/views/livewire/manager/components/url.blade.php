<div
    class="w-full md:w-1/12 mb-2 md:mb-0 cursor-pointer font-semibold"
    x-data="{ clickCount: 0 }"
    @click="
        clickCount++;
        setTimeout(() => {
        if (clickCount === 1) {
            // Одиночный клик - проверка на наличие ссылки
            if ('{{ $urlData['url'] ?? '' }}') {
                window.open('{{ $urlData['url'] ?? '' }}', '_blank');
            }
        } else if (clickCount === 2) {
            // Двойной клик - открытие модального окна для редактирования
                $wire.openManagerPartUrlModal({{ $part->id }});
            }
            clickCount = 0; // Сброс счетчика
        }, 300); // Таймаут для определения двойного клика
    "
>
    @if(isset($urlData['text']) && $urlData['text'] !== '')
        <!-- Отображение текста, если он есть -->
        <span class="md:hidden font-semibold">URL:</span>
        {{ $urlData['text'] }}
    @elseif(isset($urlData['url']) && $urlData['url'] !== '')
        <!-- Отображение URL, если текст отсутствует, но есть URL -->
        <span class="md:hidden font-semibold">URL:</span>
        {{ $urlData['url'] }}
    @else
        <!-- Отображение иконки, если URL пуст -->
        <span class="text-gray-500" title="Edit URL">
        <svg xmlns="http://www.w3.org/2000/svg"
            class="h-5 w-5 inline-block" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M15.232 5.232l3.536 3.536M9 13h.01M6 9l5 5-3 3h6l-1.293-1.293a1 1 0 010-1.414l7.42-7.42a2.828 2.828 0 10-4-4l-7.42 7.42a1 1 0 01-1.414 0L6 9z"
            />
        </svg>
        </span>
    @endif
</div>
