<div class="p-1 md:p-4 bg-white dark:bg-gray-900 shadow-md rounded-lg overflow-hidden">
    <h2 class="text-lg font-semibold dark:text-white">Add Technicians</h2>

    <!-- Кнопка добавления нового техника -->
    <button wire:click="showAddTechnicianModal" class="px-4 py-2 bg-green-500 text-white rounded-md hover:bg-green-600">
        Add technician
    </button>

    <!-- Модальное окно для добавления техника -->
    @if ($addTechnicianModal)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white p-6 rounded-lg shadow-lg w-1/3">
                <h3 class="text-lg font-medium mb-4">Add technician</h3>

                <div class="mb-4">
                    <label for="name" class="block text-gray-700">Technician's Name:</label>
                    <input type="text" wire:model="name" class="border rounded-md p-2 w-full">
                    @error('name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div class="mb-4">
                    <label for="email" class="block text-gray-700">Technician's Email:</label>
                    <input type="email" wire:model="email" class="border rounded-md p-2 w-full">
                    @error('email') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div class="mb-4">
                    <label for="password" class="block text-gray-700">Password:</label>
                    <input type="password" wire:model="password" class="border rounded-md p-2 w-full">
                    @error('password') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div class="flex justify-end">
                    <button wire:click="hideAddTechnicianModal" class="bg-gray-400 text-white px-4 py-2 rounded-md mr-2">
                        Cancel
                    </button>
                    <button wire:click="addTechnician" class="bg-blue-500 text-white px-4 py-2 rounded-md">
                        Save
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- Таблица с техниками -->
    <table class="table-auto w-full mt-4">
        <thead>
        <tr class="bg-gray-200">
            <th class="px-4 py-2">Name</th>
            <th class="px-4 py-2">Email</th>
            <th class="px-4 py-2">Status</th>
            <th class="px-4 py-2">Actions</th>
        </tr>
        </thead>
        <tbody>
        @foreach ($technicians as $technician)
            <tr>
                <td class="border px-4 py-2 dark:text-white">{{ $technician->name }}</td>
                <td class="border px-4 py-2 dark:text-white">{{ $technician->email }}</td>
                <td class="border px-4 py-2 dark:text-white">
                    {{ $technician->is_active ? 'Активен' : 'Заблокирован' }}
                </td>
                <td class="border px-4 py-2">
                    @if ($technician->is_active)
                        <button wire:click="blockTechnician({{ $technician->id }})" class="bg-red-500 text-white px-2 py-1 rounded">
                            Block
                        </button>
                    @else
                        <button wire:click="unblockTechnician({{ $technician->id }})" class="bg-green-500 text-white px-2 py-1 rounded">
                            Unblock
                        </button>
                    @endif
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
