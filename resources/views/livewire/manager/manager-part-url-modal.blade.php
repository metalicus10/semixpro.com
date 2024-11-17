<div x-data="{ isOpen: @entangle('isUrlModalOpen') }">
    <div x-show="isOpen" class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center z-50">
        <div class="bg-white p-6 rounded shadow-md w-1/3">
            <h2 class="text-xl font-semibold mb-4">Редактировать ссылку</h2>
            @dd($suppliers)

            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="text">Supplier:</label>
                <input wire:model="text" type="text" id="text" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                <select wire:model="selectedSupplier"
                        class="w-full p-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-gray-500">
                        <option value="">Select Supplier</option>
                        @foreach ($suppliers as $supplier)
                            <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                        @endforeach
                </select>
                @error('selectedSupplier') <span class="text-red-500">{{ $message }}</span> @enderror
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="url">URL:</label>
                <input wire:model="url" type="text" id="url" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            </div>

            <div class="flex justify-end">
                <button @click="isUrlModalOpen = false" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded mr-2">Отмена</button>
                <button wire:click="save" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">OK</button>
            </div>
        </div>
    </div>
</div>
