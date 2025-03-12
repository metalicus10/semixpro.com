@php
    $isEditing = fn($id) => $editingWarehouseId === $id;
@endphp
<div>
<div class="overflow-x-auto whitespace-nowrap border-b">
    <ul class="flex space-x-2 pb-2">
        @foreach ($warehouses as $warehouse)
            <li class="relative flex items-center cursor-pointer p-2 rounded-md"
                wire:click="selectWarehouse({{ $warehouse['id'] }})"
                draggable="true"
                @dragstart="event.dataTransfer.setData('warehouseId', '{{ $warehouse['id'] }}')"
                @drop="reorderWarehouses($event.dataTransfer.getData('warehouseId'), '{{ $warehouse['id'] }}')"
                @dragover.prevent>

                @if ($isEditing($warehouse['id']))
                    <input type="text" wire:model.defer="newName" class="border p-1 rounded" autofocus
                           wire:keydown.enter="saveWarehouseName"
                           wire:keydown.escape="$set('editingWarehouseId', null)"/>
                @else
                    <span class="pr-2">{{ $warehouse['name'] }}</span>
                    <button wire:click="startEditingWarehouse({{ $warehouse['id'] }})" class="text-blue-500">✏️</button>
                @endif
            </li>
        @endforeach
    </ul>
</div>

<div class="p-4 border rounded mt-2">
    @if (isset($selectedWarehouseId) && isset($parts))
        <h2 class="text-lg font-semibold mb-2">Запчасти склада {{ $warehouses->where('id', $selectedWarehouseId)->first()?->name }}</h2>
        <table class="w-full border">
            <thead>
            <tr class="border-b">
                <th class="p-2">Название</th>
                <th class="p-2">Количество</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($parts as $part)
                <tr class="border-b">
                    <td class="p-2">{{ $part->name }}</td>
                    <td class="p-2">{{ $part->quantity }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    @else
        <p class="text-gray-500">Выберите склад для отображения запчастей.</p>
    @endif
</div>
</div>
