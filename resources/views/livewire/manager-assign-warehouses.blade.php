<div x-data="{
        selectedTechnician: @entangle('selectedTechnician'),
        selectedWarehouses: @entangle('selectedWarehouses').defer,
        technicians: @js($technicians),
        warehouses: @js($warehouses),
        fetchAssignedWarehouses() {
            let technician = this.technicians.find(t => t.id == this.selectedTechnician);
            if (technician && technician.assigned_warehouses) {
                this.selectedWarehouses = [...technician.assigned_warehouses];
            } else {
                this.selectedWarehouses = [];
            }
        },
    }" x-init="$watch('selectedTechnician', () => fetchAssignedWarehouses())"
     class="bg-gray-100 dark:bg-gray-800 p-4 rounded-lg shadow max-h-128 overflow-y-auto"
>

    <h2 class="text-lg font-bold mb-2 text-gray-600 dark:text-gray-300">Назначение складов технику</h2>

    <label class="block dark:text-gray-300">Выберите техника:</label>
    <select x-model="selectedTechnician" @change="fetchAssignedWarehouses" class="w-full p-2 my-4 border rounded dark:text-gray-300 bg-gray-100 dark:bg-gray-800">
        <option value="">-- Выберите техника --</option>
        <template x-for="tech in technicians" :key="tech.id">
            <option :value="tech.id" x-text="tech.name"></option>
        </template>
    </select>

    <template x-if="selectedTechnician">
        <div class="mb-4 dark:text-gray-300">
            <label class="block">Выберите склады:</label>
            <div class="flex flex-col space-y-2 mt-2">
                <template x-for="warehouse in warehouses" :key="warehouse.id">
                    <div class="flex items-center space-x-4 border p-2 rounded">
                        <input type="checkbox" x-model="selectedWarehouses" :value="warehouse.id">
                        <span x-text="warehouse.name" class="flex-1"></span>
                    </div>
                </template>
            </div>
        </div>
    </template>

    <button @click="fetchAssignedWarehouses" wire:click="assignWarehouses" class="px-4 py-2 bg-green-500 text-white rounded-md hover:bg-green-600">
        Назначить склады
    </button>
</div>
