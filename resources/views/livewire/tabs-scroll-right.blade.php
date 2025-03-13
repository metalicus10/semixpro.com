<div>
    <!-- Кнопка прокрутки вправо -->
    <button @click="scrollBy(100)" @dblclick="scrollToEnd()"
            class="absolute right-0 top-1/2 transform -translate-y-1/2 bg-gray-100 dark:bg-gray-700 p-2 rounded-full shadow z-10"
            x-show="canScrollRight"
            x-transition
    >
        &rarr;
    </button>
</div>
