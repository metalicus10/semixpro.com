<div
     class="bg-gray-100 dark:bg-gray-800 p-4 rounded-lg shadow max-h-128 overflow-y-auto"
>

    <h2 class="text-lg font-bold mb-2 text-gray-600 dark:text-gray-300">Назначение складов технику</h2>

    <label class="block dark:text-gray-300">Выберите техника:</label>
    <select wire:model.live="selectedTechnician" class="w-full p-2 my-4 border rounded dark:text-gray-300 bg-gray-100 dark:bg-gray-800">
        <option value="">-- Выберите техника --</option>
        @foreach($technicians as $tech)
            <option value="{{ $tech->id }}">{{ $tech->name }}</option>
        @endforeach
    </select>

    @if(!empty($selectedTechnician))
        <div class="mb-4 dark:text-gray-300">
            <label class="block">Выберите склады:</label>
            <div class="flex flex-col space-y-2 mt-2">
                @foreach($warehouses as $warehouse)
                    <div class="flex items-center space-x-4 border p-2 rounded">
                        <input type="checkbox" wire:model.live="selectedWarehouses" value="{{ $warehouse->id }}">
                        <span class="flex-1">{{ $warehouse->name }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <button wire:click="assignWarehouses" class="px-4 py-2 bg-green-500 text-white rounded-md hover:bg-green-600">
        Назначить склады
    </button>
</div>
