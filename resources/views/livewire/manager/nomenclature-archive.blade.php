<div x-data="{ showArchiveModal: @entangle('showModal'), archivedNomenclatures: @entangle('archivedNomenclatures') ?? []  }"
     @nomenclature-updated.window="archivedNomenclatures = $wire.archivedNomenclatures"
     x-effect="if (archivedNomenclatures.length === 0) showArchiveModal = false"
>
    <!-- Кнопка для открытия модального окна -->
    <button x-show="archivedNomenclatures.length > 0" @click="showArchiveModal = true"
            class="px-4 py-2 bg-gray-600 text-white rounded">
        Архив номенклатур
    </button>

    <!-- Модальное окно -->
    <template x-show="showArchiveModal">
        <!-- Оверлей -->
        <div x-show="showArchiveModal"
             class="flex fixed inset-0 bg-black opacity-50 z-30"
             @click="showArchiveModal = false"
             x-cloak>
        </div>
        <div class="fixed inset-0 flex items-center justify-center z-50">
            <div class="bg-white p-6 rounded-lg shadow-lg w-1/2">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-lg font-bold">Архивные номенклатуры</h2>
                    <button @click="showArchiveModal = false" class="text-gray-500 hover:text-gray-700">✕</button>
                </div>

                <div class="space-y-3">
                    <template x-for="nomenclature in archivedNomenclatures" :key="nomenclature.id">
                        <div class="flex justify-between items-center p-2 border rounded">
                            <span x-text="nomenclature.name"></span>
                            <button @click="$wire.restoreNomenclature(nomenclature.id)"
                                    class="px-3 py-1 bg-green-500 text-white rounded">
                                Восстановить
                            </button>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </template>
</div>
