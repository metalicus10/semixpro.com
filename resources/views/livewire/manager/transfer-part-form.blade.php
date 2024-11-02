<div class="container mx-auto py-8">
    @if (session()->has('message'))
        <div class="bg-green-500 text-white p-4 rounded-lg mb-6">
            {{ session('message') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="bg-red-500 text-white p-4 rounded-lg mb-6">
            {{ session('error') }}
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
