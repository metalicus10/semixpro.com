<div x-data="{
        warehouses: @entangle('warehouses'),
        warehouseParts: @entangle('warehouseParts'),
        unassignedParts: @entangle('unassignedParts'),
        active: 'warehouse',
    }"
    class="bg-white dark:bg-gray-900 shadow-md rounded-lg overflow-hidden">

    <!-- Заголовок страницы -->
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-500 dark:text-gray-400">Parts</h1>

        <!-- Фильтр по категориям -->
        <div>
            <label for="category" class="text-sm font-medium text-gray-500 dark:text-gray-400">Filter by Cat:</label>
            <select wire:model.live="selectedCategory" id="category"
                    class="ml-2 p-2 text-gray-400 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                <option value="">All cats</option>
                @foreach ($categories as $cat)
                    <option class="text-gray-400" value="{{ $cat->id }}">{{ $cat->name }}</option>
                @endforeach
            </select>

        </div>
        <!-- Фильтр по брендам -->
        <div>
            <label for="brand" class="text-sm font-medium text-gray-500 dark:text-gray-400">Filter by Brand:</label>
            <select wire:model.live="selectedBrand" id="brand"
                    class="ml-2 p-2 text-gray-400 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                <option value="">All brands</option>
                @foreach ($brands as $brand)
                    <option class="text-gray-400" value="{{ $brand->id }}">{{ $brand->name }}</option>
                @endforeach
            </select>

        </div>
    </div>

    <ul class="flex border-b">
        <li class="mr-1">
            <template x-for="warehouse in warehouses" :key="warehouse.id">
                <button @click="active = 'warehouse-' + warehouse.id"
                        :class="{'border-blue-500 text-blue-500': active === 'warehouse-' + warehouse.id,
                        'border-transparent text-gray-500': active !== 'warehouse-' + warehouse.id}"
                        class="inline-block py-2 px-4 font-semibold border-b-2">
                    <span x-text="warehouse.name"></span>
                </button>
            </template>
            <button @click="active = 'without-warehouse'"
                    :class="{'border-blue-500 text-blue-500': active === 'without-warehouse', 'border-transparent text-gray-500': active !== 'without-warehouse'}"
                    class="inline-block py-2 px-4 font-semibold border-b-2">
                Без склада
            </button>
        </li>
    </ul>

    <div>
        <template x-for="(warehouse, index) in warehouses" :key="warehouse.id">
            <div x-show="active === 'warehouse-' + warehouse.id">
                <table class="w-full border-collapse border border-gray-300">
                    <thead>
                    <tr>
                        <th class="border border-gray-300 px-4 py-2">SKU</th>
                        <th class="border border-gray-300 px-4 py-2">Название</th>
                        <th class="border border-gray-300 px-4 py-2">Количество</th>
                        <th class="border border-gray-300 px-4 py-2">Категория</th>
                        <th class="border border-gray-300 px-4 py-2">Бренды</th>
                        <th class="border border-gray-300 px-4 py-2">Изображение</th>
                        <th class="border border-gray-300 px-4 py-2">Действия</th>
                    </tr>
                    </thead>
                    <tbody>
                    <template x-for="part in warehouseParts" :key="part.id" >
                        <template x-if="part.warehouse_id == warehouse.id" :key="part.id" x-init="console.log(part);">
                        <tr>
                            <td class="border border-gray-300 px-4 py-2" x-text="part.sku"></td>
                            <td class="border border-gray-300 px-4 py-2" x-text="part.name"></td>
                            <td class="border border-gray-300 px-4 py-2" x-text="part.quantity"></td>
                            <td class="border border-gray-300 px-4 py-2" x-text="part.category ? part.category.name : ''"></td>
                            <td class="border border-gray-300 px-4 py-2">
                                <template x-for="brand in part.brands" :key="brand.id">
                                    <span x-text="brand.name"></span>
                                </template>
                            </td>
                            <td class="border border-gray-300 px-4 py-2">
                                <img :src="part.image ? '/storage/' + part.image : '/default-image.jpg'" class="h-12 w-12 rounded">
                            </td>
                            <td class="border border-gray-300 px-4 py-2">
                                <button @click="$wire.usePart(part.id)" class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600"
                                        :disabled="part.quantity == 0">
                                    Использовать
                                </button>
                            </td>
                        </tr>
                        </template>
                    </template>
                    </tbody>
                </table>
            </div>
        </template>

        <div x-show="active === 'without-warehouse'">
            <table class="w-full border-collapse border border-gray-300">
                <thead>
                <tr>
                    <th class="border border-gray-300 px-4 py-2">SKU</th>
                    <th class="border border-gray-300 px-4 py-2">Название</th>
                    <th class="border border-gray-300 px-4 py-2">Количество</th>
                    <th class="border border-gray-300 px-4 py-2">Категория</th>
                    <th class="border border-gray-300 px-4 py-2">Бренды</th>
                    <th class="border border-gray-300 px-4 py-2">Изображение</th>
                    <th class="border border-gray-300 px-4 py-2">Действия</th>
                </tr>
                </thead>
                <tbody>
                <template x-for="part in this.unassignedParts" :key="part.id">
                    <tr>
                        <td class="border border-gray-300 px-4 py-2" x-text="part.sku"></td>
                        <td class="border border-gray-300 px-4 py-2" x-text="part.name"></td>
                        <td class="border border-gray-300 px-4 py-2" x-text="part.quantity"></td>
                        <td class="border border-gray-300 px-4 py-2" x-text="part.category ? part.category.name : ''"></td>
                        <td class="border border-gray-300 px-4 py-2">
                            <template x-for="brand in part.brands" :key="brand.id">
                                <span x-text="brand.name"></span>
                            </template>
                        </td>
                        <td class="border border-gray-300 px-4 py-2">
                            <img :src="part.image ? '/storage/' + part.image : '/default-image.jpg'" class="h-12 w-12 rounded">
                        </td>
                        <td class="border border-gray-300 px-4 py-2">
                            <button @click="$wire.usePart(part.id)" class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600" :disabled="part.quantity == 0">
                                Использовать
                            </button>
                        </td>
                    </tr>
                </template>
                </tbody>
            </table>
        </div>
    </div>
</div>
