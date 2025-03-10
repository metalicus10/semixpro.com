<div class="bg-white dark:bg-gray-900 shadow-md rounded-lg overflow-hidden">

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
        <div class="mb-4">
            <ul class="flex flex-wrap -mb-px text-sm font-medium text-center">
                @php
                    $uniqueWarehouses = $partsWithWarehouse->pluck('warehouse')->unique();
                @endphp
                @foreach($uniqueWarehouses as $warehouse)
                <li class="me-2" role="presentation">
                    <button
                        @click="activeTab = 'tab-{{ $warehouse->id }}'"
                        :class="activeTab === 'tab-'+ {{ $warehouse->id }} ? 'border-blue-500 text-blue-600' : 'hover:text-gray-600 hover:border-gray-300 dark:hover:text-gray-300'"
                        class="inline-block p-4 border-b-2 rounded-t-lg"
                    >{{ !empty($warehouse) ? $warehouse->name : 'Без склада' }}</button>
                </li>
                @endforeach
            </ul>
        </div>
        <hr class="h-px my-8 bg-gray-200 border-0 dark:bg-gray-700">

        <!-- Content -->
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
                @forelse($allParts as $part)
                    @dd($allParts)
                <div x-show="activeTab === 'tab-{{ $part->warehouse_id }}'" x-cloak class="p-4 rounded-lg bg-gray-50 dark:bg-gray-800">
                    <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-[#162033]">
                        <td class="px-5 py-5">@if(!empty($part->sku)){{ $part->sku }}@else --- @endif</td>
                        <td class="px-5 py-5">@if(!empty($part->name)){{ $part->name }}@else --- @endif</td>
                        <td class="px-5 py-5">{{ $part->quantity }}</td>
                        @if(!empty($part->part->nomenclatures->brands))
                            <td class="px-5 py-5 truncate whitespace-nowrap overflow-hidden">
                                @foreach($part->part->nomenclatures->brands as $brand)
                                    <span>{{ $brand->name }}</span>
                                @endforeach
                            </td>
                        @else
                            <td class="px-5 py-5 w-32 truncate whitespace-nowrap overflow-hidden">---</td>
                        @endif
                        @if(!empty($part->part->category) || !empty($part->category))
                            <td class="px-5 py-5">@if(!empty($part->part->category->name)){{ $part->part->category->name }}@else {{ $part->category->name }} @endif</td>
                        @else
                            <td class="px-5 py-5">---</td>
                        @endif
                        <td class="px-5 py-5">
                            <div x-data class="gallery h-12 w-12">
                                @if(!empty($part->part->image) || !empty($part->part->nomenclatures->image))
                                    @if($part->part->image && $part->part->nomenclatures->image || $part->part->image && $part->part->nomenclatures->image=="")
                                        <img src="{{ asset('storage') . $part->part->image }}" alt="{{ $part->part->name }}"
                                             @click="$dispatch('lightbox', '{{ asset('storage') . $part->part->image }}')"
                                             @click.stop
                                             class="object-cover rounded cursor-zoom-in">
                                    @elseif($part->part->nomenclatures->image && $part->part->image=="")
                                        <img src="{{ asset('storage') . $part->part->nomenclatures->image }}" alt="{{ $part->part->name }}"
                                             @click="$dispatch('lightbox', '{{ asset('storage') . $part->part->nomenclatures->image }}')"
                                             @click.stop
                                             class="object-cover rounded cursor-zoom-in">
                                    @else
                                        <livewire:components.empty-image/>
                                    @endif
                                @else
                                    @if($part->image && $part->nomenclatures->image || $part->image && $part->nomenclatures->image=="")
                                        <img src="{{ asset('storage') . $part->image }}" alt="{{ $part->name }}"
                                             @click="$dispatch('lightbox', '{{ asset('storage') . $part->image }}')"
                                             @click.stop
                                             class="object-cover rounded cursor-zoom-in">
                                    @elseif($part->nomenclatures->image && $part->image=="")
                                        <img src="{{ asset('storage') . $part->nomenclatures->image }}" alt="{{ $part->nomenclatures->name }}"
                                             @click="$dispatch('lightbox', '{{ asset('storage') . $part->nomenclatures->image }}')"
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
                                wire:click="usePart({{ $part->id }})"
                                class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600"
                                @if($part->quantity == 0) disabled @endif
                            >
                                Use
                            </button>
                        </td>
                    </tr>
                </div>
                @empty
                    <div x-show="activeTab === 'tab-'+ {{ $part->warehouse_id }}" x-cloak class="p-4 rounded-lg bg-gray-50 dark:bg-gray-800">
                        <tr>
                            <td colspan="7"
                                class="px-5 py-5 text-sm text-center bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                                No data
                            </td>
                        </tr>
                    </div>
                @endforelse

                </tbody>
            </table>
        </div>
    </div>
</div>
