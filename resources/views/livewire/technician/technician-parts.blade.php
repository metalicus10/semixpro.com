<div class="bg-white dark:bg-gray-900 shadow-md rounded-lg overflow-hidden"
     x-data="{
        parts: @js($allParts ?? []),
        brands: @js($brands ?? []),
        categories: @js($categories ?? []),
        lightbox(partImage){
            $dispatch('lightbox', '/storage/' + partImage);
        },
    }"
>
    <!-- Индикатор загрузки -->
    <div wire:loading.flex class="absolute inset-0 flex items-center justify-center bg-gray-900 opacity-50 z-50">
        <div class="animate-spin rounded-full h-10 w-10 border-t-4 border-orange-500"></div>
    </div>

    <!-- Заголовок страницы -->
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-500 dark:text-gray-400">Parts</h1>

        <div x-data="{
                selectedBrand: '',
                selectedCategory: '',
                filteredParts: [],
                init() {
                    this.filterParts();
                },
                filterParts() {
                    if (!Array.isArray(this.parts)) {
                        this.filteredParts = [];
                        return;
                    }
                    this.filteredParts = this.parts.filter(part => {
                        let brandMatch = this.selectedBrand ? part.brand_id == this.selectedBrand : true;
                        let categoryMatch = this.selectedCategory ? part.category_id == this.selectedCategory : true;
                        return brandMatch && categoryMatch;
                    });
                },
            }" x-init="init"
        >
            <!-- Фильтр по категориям -->
            <label for="category" class="text-sm font-medium text-gray-500 dark:text-gray-400">Filter by Cat:</label>
            <select id="category" x-model="selectedCategory" @change="filterParts"
                    class="ml-2 p-2 text-gray-400 border border-gray-300 rounded-md shadow-sm
                    focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                <option value="">All cats</option>
                <template x-for="cat in categories" :key="cat.id">
                    <option class="text-gray-400" :value="cat.id" x-text="cat.name"></option>
                </template>
            </select>

            <!-- Фильтр по брендам -->
            <label for="brand" class="text-sm font-medium text-gray-500 dark:text-gray-400">Filter by Brand:</label>
            <select id="brand" x-model="selectedBrand" @change="filterParts"
                    class="ml-2 p-2 text-gray-400 border border-gray-300 rounded-md shadow-sm focus:outline-none
                    focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                <option value="">All brands</option>
                <template x-for="brand in brands" :key="brand.id">
                    <option class="text-gray-400" :value="brand.id" x-text="brand.name"></option>
                </template>
            </select>
        </div>
    </div>

    <!-- Content -->
    <div

    >
        <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg overflow-hidden">
            <table class="table-auto w-full text-sm text-left text-gray-500 dark:text-gray-400">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                <tr>
                    <th class="px-5 py-3">SKU</th>
                    <th class="px-5 py-3">Name</th>
                    <th class="px-5 py-3">Quantity</th>
                    <th class="px-5 py-3">Brand</th>
                    <th class="px-5 py-3">Category</th>
                    <th class="px-5 py-3">Image</th>
                    <th class="px-5 py-3">Action</th>
                </tr>
                </thead>
                <tbody>
                <template x-for="part in parts" :key="part.id">
                    <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                        <!-- SKU -->
                        <td class="px-5 py-5">
                            <template x-if="part.parts && part.parts.sku">
                                <span x-text="part.parts.sku"></span>
                            </template>
                            <template x-if="!part.parts || !part.parts.sku">
                                <span>---</span>
                            </template>
                        </td>
                        <!-- Name -->
                        <td class="px-5 py-5">
                            <template x-if="part.parts && part.parts.name">
                                <span x-text="part.parts.name"></span>
                            </template>
                            <template x-if="!part.parts || !part.parts.name">
                                <span>---</span>
                            </template>
                        </td>
                        <!-- Quantity -->
                        <td class="px-5 py-5" x-text="part.quantity ?? '---'"></td>
                        <!-- Brand -->
                        <td class="px-5 py-5">
                            <template x-if="part.nomenclatures && part.nomenclatures.brands && part.nomenclatures.brands.length">
                                <div class="flex flex-wrap gap-1">
                                    <template x-for="brand in part.nomenclatures.brands" :key="brand.id">
                                        <span x-text="brand.name" class="truncate whitespace-nowrap"></span>
                                    </template>
                                </div>
                            </template>
                            <template x-if="!part.nomenclatures || !part.nomenclatures.brands || !part.nomenclatures.brands.length">
                                <span>---</span>
                            </template>
                        </td>
                        <!-- Category -->
                        <td class="px-5 py-5">
                            <template x-if="part.parts && part.parts.category && part.parts.category.name">
                                <span x-text="part.parts.category.name"></span>
                            </template>
                            <template x-if="!part.parts || !part.parts.category || !part.parts.category.name">
                                <span>---</span>
                            </template>
                        </td>
                        <!-- Image -->
                        <td class="px-5 py-5 w-12">
                            <div x-data="{ gallery: false }" class="gallery h-12 w-12">
                                <template x-if="(part.parts && part.parts.image) || (part.nomenclatures && part.nomenclatures.image)">
                                    <img
                                        :src="part.parts && part.parts.image
                                                ? '/storage/' + part.parts.image
                                                : (part.nomenclatures && part.nomenclatures.image ? '/storage/' + part.nomenclatures.image : '')"
                                        :alt="part.parts && part.parts.name ? part.parts.name : 'Part image'"
                                        @click="part.parts && part.parts.image
                                            ? lightbox(part.parts.image)
                                            : (part.nomenclatures && part.nomenclatures.image
                                                ? lightbox(part.nomenclatures.image)
                                                : null)"
                                        @click.stop
                                        class="object-cover rounded cursor-zoom-in w-12 h-12"
                                    />
                                </template>
                                <template x-if="!( (part.parts && part.parts.image) || (part.nomenclatures && part.nomenclatures.image) )">
                                    <span class="text-gray-300">—</span>
                                </template>
                            </div>
                        </td>
                        <!-- Action -->
                        <td class="px-5 py-5">
                            <button
                                @click="$wire.usePart(part.id)"
                                class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600"
                                :disabled="part.quantity == 0"
                                :class="part.quantity == 0 ? 'opacity-50 cursor-not-allowed' : ''"
                            >
                                Use
                            </button>
                        </td>
                    </tr>
                </template>
                </tbody>
            </table>
        </div>
    </div>
</div>
