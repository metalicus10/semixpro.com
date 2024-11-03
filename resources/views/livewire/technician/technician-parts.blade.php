<div class="p-8 bg-white dark:bg-gray-900 shadow-md rounded-lg overflow-hidden">
    <!-- Сообщения об ошибке -->
    @if (session()->has('warning'))
        <div
            class="bg-yellow-500 text-white p-4 rounded-lg mb-6 transition-opacity duration-1000"
            x-data="{ show: true }"
            x-init="setTimeout(() => show = false, 3500)"
            x-show="show"
            x-transition:enter="opacity-0"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="opacity-100"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
        >
            {{ session('warning') }}
        </div>
    @endif
    <!-- Сообщения об успехе -->
    @if (session()->has('message'))
        <div
            class="bg-green-500 text-white p-4 rounded-lg mb-6 transition-opacity duration-1000"
            x-data="{ show: true }"
            x-init="setTimeout(() => show = false, 3500)"
            x-show="show"
            x-transition:enter="opacity-0"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="opacity-100"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
        >
            {{ session('message') }}
        </div>
    @endif

    <!-- Заголовок страницы -->
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-500 dark:text-gray-400">Parts</h1>

        <!-- Фильтр по категориям -->
        <div>
            <label for="category" class="text-sm font-medium text-gray-500 dark:text-gray-400">Filter by Cat:</label>
            <select wire:model.live="selectedCategory" id="category"
                    class="ml-2 p-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                <option value="">All cats</option>
                @foreach ($categories as $cat)
                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                @endforeach
            </select>

        </div>
        <!-- Фильтр по брендам -->
        <div>
            <label for="brand" class="text-sm font-medium text-gray-500 dark:text-gray-400">Filter by Brand:</label>
            <select wire:model.live="selectedBrand" id="brand"
                    class="ml-2 p-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                <option value="">All brands</option>
                @foreach ($brands as $brand)
                    <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                @endforeach
            </select>

        </div>
    </div>

    <hr class="h-px my-8 bg-gray-200 border-0 dark:bg-gray-700">

    <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg overflow-hidden">
        <table class="table-auto w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
            <tr>
                <th scope="col" class="px-5 py-3">SKU</th>
                <th scope="col" class="px-5 py-3">Name</th>
                <th scope="col" class="px-5 py-3">Quantity</th>
                <th scope="col" class="px-5 py-3">Brand</th>
                <th scope="col" class="px-5 py-3">Category</th>
                <th scope="col" class="px-5 py-3">Image</th>
                <th scope="col" class="px-5 py-3">Action</th>
            </tr>
            </thead>
            <tbody>
            @forelse ($parts as $transfer)
                <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-[#162033]">
                    <td class="px-5 py-5">{{ $transfer->part->sku }}</td>
                    <td class="px-5 py-5">{{ $transfer->part->name }}</td>
                    <td class="px-5 py-5">{{ $transfer->quantity }}</td>
                    @if(!empty($transfer->part->brands))
                        <td class="px-5 py-5 truncate whitespace-nowrap overflow-hidden flex flex-col">
                            @foreach($transfer->part->brands as $brand)
                                <span>{{ $brand->name }}</span>
                            @endforeach
                        </td>
                    @else
                        <td class="px-5 py-5 w-32 truncate whitespace-nowrap overflow-hidden"></td>
                    @endif
                    @if(!empty($transfer->part->category))
                        <td class="px-5 py-5">{{ $transfer->part->category->name }}</td>
                    @else
                        <td class="px-5 py-5"></td>
                    @endif
                    <td class="px-5 py-5">
                        <div x-data class="gallery h-12 w-12">
                            <img src="@if ($transfer->part->image == null) @else {{ Storage::disk('s3')->url($transfer->part->image) }}@endif" alt="{{ $transfer->part->name }}"
                                 @click="$dispatch('lightbox', '@if ($transfer->part->image === null) @click.stop @endif')"
                                 @click.stop
                                 class="object-cover rounded cursor-zoom-in">
                        </div>
                    </td>
                    <td class="px-5 py-5">
                        <button
                            wire:click="usePart({{ $transfer->id }})"
                            class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600"
                            @if($transfer->quantity == 0) disabled @endif
                        >
                            Use
                        </button>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7"
                        class="px-5 py-5 text-sm text-center bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                        No data
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <!-- Общий Lightbox для всех изображений -->
    <div class="lightbox fixed inset-0 z-50 bg-black bg-opacity-75 flex items-center justify-center"
         x-data="{ lightboxOpen: false, imgSrc: '' }"
         x-show="lightboxOpen"
         x-transition
         @lightbox.window="lightboxOpen = true; imgSrc = $event.detail;"
         style="display: none;">

        <!-- Фон для закрытия -->
        <div class="absolute inset-0 bg-black opacity-75" @click="lightboxOpen = false"></div>

        <!-- Контейнер для изображения -->
        <div class="lightbox-container relative z-10" @click.stop>
            <!-- Полное изображение -->
            <img :src="imgSrc" class="object-contain max-w-full max-h-full">
        </div>
    </div>
</div>
