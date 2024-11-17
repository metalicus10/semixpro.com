<div class="container mx-auto py-8">
    @if ($notificationMessage)
            <div
                class="flex justify-center left-1/3 text-white text-center p-4 rounded-lg mb-6 transition-opacity duration-1000 z-50 absolute top-[10%] w-1/2"
                x-data="{ show: true }"
                x-init="
            setTimeout(() => show = false, 3500);
            setTimeout(() => $wire.clearNotification(), 3500);
        "
                x-show="show"
                x-transition:enter="opacity-0"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="opacity-100"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                :class="{
            'bg-blue-700': '{{ $notificationType }}' === 'info',
            'bg-green-500': '{{ $notificationType }}' === 'success',
            'bg-yellow-500': '{{ $notificationType }}' === 'warning'
        }"
            >
                {{ $notificationMessage }}
            </div>
    @endif

    <div class="mb-8">
        <h2 class="text-xl font-bold mb-4">Transferring the spare part to the technician</h2>

        <div class="mb-4">
            <label for="part" class="block text-sm font-medium text-gray-700">Spare part</label>
            <select wire:model="partId" id="part" class="w-full p-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">Select a spare part</option>
                @foreach ($parts as $part)
                    <option value="{{ $part->id }}">{{ $part->name }} ({{ $part->quantity }} in stock)</option>
                @endforeach
            </select>
            @error('partId') <span class="text-red-500">{{ $message }}</span> @enderror
        </div>

        <div class="mb-4">
            <label for="technician" class="block text-sm font-medium text-gray-700">Technician</label>
            <select wire:model="technicianId" id="technician" class="w-full p-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">Select a technician</option>
                @foreach ($technicians as $technician)
                    <option value="{{ $technician->id }}">{{ $technician->name }}</option>
                @endforeach
            </select>
            @error('technicianId') <span class="text-red-500">{{ $message }}</span> @enderror
        </div>

        <div class="mb-4">
            <label for="quantity" class="block text-sm font-medium text-gray-700">Quantity</label>
            <input type="number" wire:model="quantity" id="quantity" class="w-full p-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            @error('quantity') <span class="text-red-500">{{ $message }}</span> @enderror
        </div>

        <button wire:click="transferPart" class="px-4 py-2 bg-green-500 text-white rounded-md hover:bg-green-600">Transfer spare part</button>
    </div>
</div>
