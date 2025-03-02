<div x-data="{
        showEditMenu: false,
        editingName: false,
        newName: '{{ $part->name }}',
        originalName: '{{ $part->name }}',
        errorMessage: '',
        showPnPopover: false,
        deletePn: false,
        showingPn: false,
        searchPn: '',
        newPn: '',
        addingPn: false,
        //availablePns: Object.keys(@entangle('availablePns') || {}).length ? @entangle('availablePns') : {},
        //selectedPns: @entangle('selectedPns'),
   }"
     @pn-added.window="addingPn = false; newPn = ''; errorMessage = ''"
     class="flex-[1] flex flex-row px-4 py-2 md:mb-0 cursor-pointer relative"
>

    <!-- PN -->


    <span class="flex items-center md:hidden font-semibold">Name:</span>

    <!-- Название с подменю -->
    <div class="flex items-center w-full">
        <!-- Оверлей -->
        <div x-show="editingName || deletePn || addingPn"
             class="flex fixed inset-0 bg-black bg-opacity-50 z-30"
             @click="editingName = false, deletePn = false, addingPn = false;"
             x-cloak>
        </div>

        <!-- Основное отображение -->
        <span x-show="!editingName" @click="editingName = true"
              class="flex z-35 items-center cursor-pointer hover:underline min-h-[30px]">
              {{ $part->name }}
        </span>
    </div>
    <!-- Режим редактирования Name -->
    <div x-show="editingName"
         class="flex justify-center items-center w-full relative z-40"
         x-cloak>
        <input type="text" x-model="newName"
               class="border border-gray-300 rounded-md text-sm px-2 py-1 w-[180px] mr-2"
               @keydown.enter="if (newName !== originalName) { $wire.updateName({{ $part->id }}, newName); originalName = newName; } editingName = false;"
               @keydown.escape="editingName = false">
        <button
            @click="if (newName !== originalName) { $wire.updateName({{ $part->id }}, newName); originalName = newName; } editingName = false;"
            class="bg-green-500 text-white px-2 py-1 rounded-full w-1/4 w-[28px]">
            ✓
        </button>
    </div>
</div>
