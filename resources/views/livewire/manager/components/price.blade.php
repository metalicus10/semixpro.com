<div
    class="flex flex-row w-[120px] px-4 py-2 md:mb-0 cursor-pointer relative parent-container"
    x-data="{ showPopover: false, editing: false, newPrice: '', popoverX: 0, popoverY: 0 }">

    <!-- Кликабельная ссылка с ценой запчасти -->
    <span class="md:hidden font-semibold">Price:</span>
    <a id="price-item-{{ $part['id'] }}"
        @click="
            $nextTick(() => {
                editing = false; // Сбрасываем редактирование при открытии
                newPrice = '{{ $part['price'] }}'; // Устанавливаем текущее значение
                const parent = $el.closest('.parent-container');
                const elementOffsetLeft = $el.offsetLeft;
                const elementOffsetTop = $el.offsetTop;

                popoverX = elementOffsetLeft / parent.offsetWidth;
                popoverY = elementOffsetTop / parent.offsetHeight;

                showPopover = true;
            });
       "
       class="cursor-pointer text-sm text-blue-600 hover:underline dark:text-blue-400">
        <span>${{ $part['price'] }}</span>
    </a>

    <!-- Поповер с динамическим позиционированием -->
    <div x-show="showPopover" x-transition role="tooltip"
         class="absolute z-50 bg-white dark:bg-gray-800 rounded-lg shadow-lg w-56 p-1"
         :style="'top: ' + popoverY + 'px; left: ' + popoverX + 'px;'"
         @click.away="showPopover = false">

        <!-- Оверлей -->
        <div x-show="editing"
             class="flex fixed inset-0 bg-black opacity-50 z-30"
             @click="editing = false"
             x-cloak>
        </div>

        <div class="flex flex-row w-full">
            <!-- Кнопка Edit -->
            <button x-show="!editing"
                    @click.prevent="editing = true; $nextTick(() => { $refs.priceInput.focus() })"
                    class="w-1/2 text-center py-1 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-gray-600 rounded">
                Edit
            </button>

            <!-- Поле ввода новой цены и кнопка подтверждения -->
            <div x-show="editing"
                 class="flex justify-center items-center z-40"
                 x-transition>
                <input type="number" x-ref="priceInput"
                       x-model="newPrice"
                       class="border border-gray-300 rounded-md text-sm px-2 py-1 w-[180px] mr-2 focus:outline-none focus:outline-offset-[0px] focus:outline-violet-900"
                       placeholder="{{ $part['price'] }}">
                <button @click="
                                                                        if (newPrice !== '{{ $part['price'] }}') {
                                                                            $wire.set('newPrice', newPrice)
                                                                            .then(() => {
                                                                                $wire.updatePartPrice({{ $part['id'] }}, newPrice);
                                                                            });
                                                                        }
                                                                        showPopover = false;
                                                                        editing = false;
                                                                    "
                        class="bg-green-500 text-white px-2 py-1 rounded-full w-1/4 w-[28px]">
                    ✓
                </button>
            </div>

            <!-- Кнопка для открытия истории цен -->
            <button x-show="!editing"
                    @click="$dispatch('open-price-modal', { partId: {{ $part['id'] }} })"
                    class="w-1/2 text-center py-1 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-gray-600 rounded">
                History
            </button>
        </div>
    </div>
</div>
