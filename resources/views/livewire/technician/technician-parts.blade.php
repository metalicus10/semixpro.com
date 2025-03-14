<div class="bg-white dark:bg-gray-900 shadow-md rounded-lg overflow-hidden">
    <!-- Индикатор загрузки -->
    <div wire:loading.flex class="absolute inset-0 flex items-center justify-center bg-gray-900 opacity-50 z-50">
        <div class="animate-spin rounded-full h-10 w-10 border-t-4 border-orange-500"></div>
    </div>

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


    <div x-data="{ activeTab: 'tab-1' }">
        <!-- Tabs -->
        <div class="">
            <ul class="flex flex-wrap -mb-px text-sm font-medium text-center">
                @foreach($this->technicianWarehouses as $key => $warehouse)
                <li class="me-2" role="presentation">
                    <button
                        @click="activeTab = 'tab-{{ $key }}'"
                        :class="activeTab === 'tab-{{ $key }}' ? 'border-blue-500 text-blue-600' : 'hover:text-gray-600 hover:border-gray-300 dark:hover:text-gray-300'"
                        class="inline-block p-4 border-b-4 rounded-t-lg"
                    >{{ !empty($warehouse) ? $warehouse : 'Без склада' }}</button>
                </li>
                @endforeach
            </ul>
        </div>
        <hr class="h-[1px] mb-8 bg-gray-200 border-0 dark:bg-gray-700">

        <!-- Content -->
        @foreach($allParts as $techPart)
        <div x-show="activeTab === 'tab-{{ $techPart->warehouse_id }}'" x-cloak class="p-4 rounded-lg bg-gray-50 dark:bg-gray-800">
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

                    <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-[#162033]">
                        <td class="px-5 py-5">@if(!empty($techPart->part->sku)){{ $techPart->part->sku }}@else --- @endif</td>
                        <td class="px-5 py-5">@if(!empty($techPart->part->name)){{ $techPart->part->name }}@else --- @endif</td>
                        <td class="px-5 py-5">{{ $techPart->quantity }}</td>
                        @if(!empty($techPart->nomenclatures->brands))
                            <td class="px-5 py-5 truncate whitespace-nowrap overflow-hidden">
                                @foreach($techPart->nomenclatures->brands as $brand)
                                    <span>{{ $brand->name }}</span>
                                @endforeach
                            </td>
                        @else
                            <td class="px-5 py-5 w-32 truncate whitespace-nowrap overflow-hidden">---</td>
                        @endif
                        @if(!empty($techPart->part->category))
                            <td class="px-5 py-5">@if(!empty($techPart->part->category->name)){{ $techPart->part->category->name }}@else {{ $techPart->category->name }} @endif</td>
                        @else
                            <td class="px-5 py-5">---</td>
                        @endif
                        <td class="px-5 py-5">
                            <div x-data class="gallery h-12 w-12">
                                @if(!empty($techPart->part->image) || !empty($techPart->nomenclatures->image))
                                    @if($techPart->part->image && $techPart->nomenclatures->image || $techPart->part->image && $techPart->nomenclatures->image=="")
                                        <img src="{{ asset('storage') . $techPart->part->image }}" alt="{{ $techPart->part->name }}"
                                             @click="$dispatch('lightbox', '{{ asset('storage') . $techPart->part->image }}')"
                                             @click.stop
                                             class="object-cover rounded cursor-zoom-in">
                                    @elseif($techPart->nomenclatures->image && $techPart->part->image=="")
                                        <img src="{{ asset('storage') . $techPart->nomenclatures->image }}" alt="{{ $techPart->part->name }}"
                                             @click="$dispatch('lightbox', '{{ asset('storage') . $techPart->nomenclatures->image }}')"
                                             @click.stop
                                             class="object-cover rounded cursor-zoom-in">
                                    @else
                                        <livewire:components.empty-image/>
                                    @endif
                                @endif
                            </div>
                        </td>
                        <td class="px-5 py-5">
                            <button
                                wire:click="usePart({{ $techPart->id }})"
                                class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600"
                                @if($techPart->quantity == 0) disabled @endif
                            >
                                Use
                            </button>
                        </td>
                    </tr>

                </tbody>
            </table>
        </div>
        </div>
        @endforeach
    </div>
</div>
