<div>
    <!-- Кнопка для прокрутки влево -->
    <button @click="scrollBy(-100)" @dblclick="scrollToStart()"
            class="absolute left-0 top-1/2 transform -translate-y-1/2 bg-gray-100 dark:bg-gray-700 p-2 rounded-full shadow z-10"
            x-show="canScrollLeft"
            x-transition
    >
        &larr;
    </button>
</div>
