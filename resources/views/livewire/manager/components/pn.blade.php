<div class="flex relative">

    <!-- Список существующих PNs -->
    <div class="flex z-30 items-center" x-cloak>
        <!-- Кнопка для открытия поповера -->
        <div
            class="w-4 h-4 md:w-6 md:h-6 flex items-center justify-center bg-blue-500 text-white rounded-full cursor-pointer mr-2 uppercase font-bold text-[8px] md:text-[10px]"
            @click="showPnPopover = !showPnPopover">
            PN
        </div>

        <!-- Поповер для редактирования PNs -->
        <div x-show="showPnPopover" x-transition
             @click.away="showPnPopover = false"
             class="flex absolute z-40 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg shadow-lg w-56 p-1">

            <!-- Оверлей -->
            <div
                x-show="deletePn || addingPn || showPnPopover || showingPn"
                class="flex fixed inset-0 bg-black bg-opacity-50 z-30"
                @click="deletePn = false; showEditMenu = false; showingPn = false; showPnsList = false; addingPn = false; showPnPopover = false;"
                x-cloak>
            </div>

            <div
                class="flex flex-row w-full cursor-pointer z-50"
                x-cloak>
                <div
                    @click="addingPn = true; deletePn = false; showEditMenu = false; showingPn = false; showPnsList = false; showPnPopover = false;"
                    class="w-1/3 text-center py-1 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-gray-600 rounded">
                    Add PN
                </div>
                <div
                    @click="deletePn = true; showEditMenu = false; showingPn = false; showPnsList = false; addingPn = false; showPnPopover = false;"
                    class="w-1/3 text-center py-1 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-gray-600 rounded">
                    Del PN
                </div>
                <div
                    @click="showingPn = true; deletePn = false; showEditMenu = false; showPnsList = false; addingPn = false; showPnPopover = false;"
                    class="w-1/3 text-center py-1 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-gray-600 rounded">
                    Show PN
                </div>
            </div>


        </div>
    </div>

    <!-- Список PN запчасти -->
    <div x-show="showingPn"
         class="flex flex-col absolute z-50 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg shadow-lg w-52 p-1"
         x-transition
         @click.away="showingPn = false;"
    >
        <div class="flex flex-row w-full">
            <div
                class="w-full flex justify-center items-center">
                <ul class="py-1 text-sm text-gray-700 dark:text-gray-300 max-h-28 overflow-y-auto w-52"
                    x-ref="pnList"

                >
                    @php $pnsArray = json_decode($part->pns, true); @endphp
                    @if (!empty($pnsArray))
                        @foreach ($pnsArray as $pn)
                            <li class="flex items-center px-2 cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600"
                                @click.stop>
                                <span class="ml-2">{{ $pn }}</span>
                            </li>
                        @endforeach
                    @else
                        <p>No PN's</p>
                    @endif
                </ul>
            </div>
        </div>
    </div>

    <!-- Поле ввода нового PN -->
    <div x-show="addingPn"
         class="absolute z-50 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg shadow-lg w-56 p-1"
         x-transition x-cloak
         @click.away="addingPn = false; newPn = ''; errorMessage = '';"
    >
        <div class="flex flex-row w-full">
            <div class="flex justify-center items-center">
                <!-- Поле ввода -->
                <input type="text" wire:model="newPn"
                       placeholder="Enter new PN"
                       class="border border-gray-300 rounded-md text-sm px-2 py-1 w-3/4 mr-2">

                <!-- Кнопки действия -->
                <button wire:click="addPn"
                        class="bg-green-500 text-white px-2 py-1 rounded-full w-1/4">
                    ✓
                </button>
            </div>
        </div>
    </div>

    <!-- Режим удаления PN -->
    <div x-show="deletePn"
         class="absolute z-50 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg shadow-lg w-56 p-1"
         x-cloak x-transition
         @click.away="deletePn = false; searchPn='';"
    >
        <!-- Поле поиска -->
        <div class="mb-2" @click.stop>
            <input type="text" x-model="searchPn"
                   placeholder="Search brands..."
                   class="w-full p-1 border border-gray-500 rounded focus:outline-none focus:ring-2 focus:ring-blue-500 text-gray-700 dark:bg-gray-700 dark:text-gray-300"/>
        </div>

        <!-- PN -->
        <div
            class="flex flex-row justify-evenly items-center"
            @click.stop>
            <!-- Список PN с мульти-выбором -->
            <ul class="py-1 text-sm text-gray-700 dark:text-gray-300 max-h-28 overflow-y-auto">
                @php $pns = $this->getPartPns($part->id); @endphp
                @if (!empty($pns))
                    @foreach ($pns as $pn)
                        <template
                            x-if="!searchPn || '{{ strtolower($pn->number) }}'.includes(searchPn.toLowerCase())">
                            <li class="flex items-center px-2 cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600"
                                @click.stop>
                                <input type="checkbox"
                                       value="{{ $pn->id }}"
                                       x-model="selectedPns"
                                       class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                                       @click.stop>
                                <label
                                    class="ml-2">{{ $pn->number }}</label>
                            </li>
                        </template>
                    @endforeach
                @else
                    <p>No PN's</p>
                @endif
            </ul>

            <!-- Кнопка подтверждения -->
            <div
                class="justify-self-center inline-flex self-center items-center">
                <button @click="$wire.set('selectedPns', selectedPns).then(() => {
                                                                                        $wire.deletePns({{ $part->id }}, selectedPns);
                                                                                        deletePn = false;
                                                                                    });"
                        class="bg-green-500 text-white px-2 py-1 rounded-full hover:bg-green-600">
                    ✓
                </button>
            </div>
        </div>
    </div>
</div>
