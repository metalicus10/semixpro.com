<div x-data="{
    createJobModal() {
        return {
            open: true,
            showCustomerModal: false,
            customerError: '',
            form: {
                customer_query: '',
                schedule_from: '',
                schedule_to: '',
                dispatch: '',
                notify_customer: false,
                items: [],
                message: null,
                new_customer: {
                    name: '',
                    email: '',
                    phone: '',
                    address: ''
                }
            },
            init() {
                window.addEventListener('customer-validation-error', event => {
                    this.customerError = Object.values(event.detail.errors)[0][0] ?? 'Validation error';
                });

                window.addEventListener('customer-created', event => {
                    this.form.customer_query = event.detail.name;
                    this.showCustomerModal = false;
                    this.customerError = '';
                });
            },
            saveNewCustomer() {
                this.customerError = '';
                const customer = this.form.new_customer;
                if (!customer.name || (!customer.email && !customer.phone)) {
                    this.customerError = 'Name and either email or phone are required.';
                    return;
                }

                Livewire.dispatch('createCustomer', customer);
            },
            prefill(data) {
                this.form.schedule_from = data.schedule_from;
                this.form.schedule_to = data.schedule_to;
                this.form.dispatch = data.dispatch;
                this.open = true;
            },
            addItem(type) {
                this.form.items.push({
                    name: '', qty: 1, unit_price: 0, unit_cost: 0, tax: false, description: '', type
                });
            },
            subtotal() {
                return '$' + this.form.items.reduce((acc, i) => acc + (i.qty * i.unit_price), 0).toFixed(2);
            },
            taxTotal() {
                return '$' + this.form.items.reduce((acc, i) => acc + (i.tax ? i.qty * i.unit_price * 0.1 : 0), 0).toFixed(2);
            },
            total() {
                const sub = this.form.items.reduce((acc, i) => acc + (i.qty * i.unit_price), 0);
                const tax = this.form.items.reduce((acc, i) => acc + (i.tax ? i.qty * i.unit_price * 0.1 : 0), 0);
                return '$' + (sub + tax).toFixed(2);
            }
        }
    }
}" x-init="init()">
    <div x-show="jobModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40">
        <div class="bg-white rounded-lg shadow-lg w-full max-w-6xl p-6 overflow-y-auto max-h-[95vh]">
            <div class="flex justify-between items-center border-b pb-4 mb-6">
                <h2 class="text-xl font-semibold">New job</h2>
                <button @click="jobModalOpen = false" class="text-gray-500 hover:text-red-500 text-2xl">&times;</button>
            </div>

            <div class="flex flex-col lg:flex-row gap-6">
                <!-- Left Column -->
                <div class="w-full lg:w-1/3 space-y-6">
                    <div class="bg-gray-50 rounded-lg border p-4">
                        <div class="font-medium text-sm mb-1 flex items-center gap-1">
                            <svg class="w-4 h-4"/>
                            Customer
                        </div>
                        <input type="text" x-model="form.customer_query"
                               class="w-full rounded px-2 py-1 text-sm border"
                               placeholder="Name, email, phone, or address"/>
                        <button type="button" class="text-blue-600 text-xs mt-2" @click="showAddCustomerModal = true">+
                            New customer
                        </button>
                    </div>

                    <!-- Schedule -->
                    <div class="border p-4 rounded space-y-4">
                        <label class="block text-sm font-medium">Schedule</label>
                        <div class="flex flex-col gap-2">
                            <div>
                                <label class="text-xs text-gray-500">From</label>
                                <input type="datetime-local" x-model="form.schedule_from"
                                       class="w-full border rounded px-3 py-2 text-sm">
                            </div>
                            <div>
                                <label class="text-xs text-gray-500">To</label>
                                <input type="datetime-local" x-model="form.schedule_to"
                                       class="w-full border rounded px-3 py-2 text-sm">
                            </div>
                            <div class="text-xs text-gray-500">Timezone: EDT</div>
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500">Dispatch</label>
                            <input type="text" x-model="form.dispatch" placeholder="Dispatch by name or tag"
                                   class="w-full border rounded px-3 py-2 text-sm">
                        </div>
                        <div class="flex items-center gap-2">
                            <input type="checkbox" x-model="form.notify_customer" class="form-checkbox">
                            <label class="text-sm">Notify customer</label>
                        </div>
                    </div>
                </div>

                <!-- Center Column -->
                <div class="w-full lg:w-2/3 space-y-6">
                    <div class="border p-4 rounded">
                        <label class="block text-sm font-medium mb-2">Line items</label>
                        <template x-for="(item, index) in form.items" :key="index">
                            <div class="mb-4 space-y-2 border-b pb-2">
                                <div class="flex flex-wrap gap-2">
                                    <input x-model="item.name" type="text" placeholder="Item name"
                                           class="flex-1 border rounded px-3 py-2 text-sm">
                                    <input x-model="item.qty" type="number" step="0.01" min="0"
                                           class="w-20 border rounded px-2 py-1 text-sm" placeholder="Qty">
                                    <input x-model="item.unit_price" type="number" step="0.01" min="0"
                                           class="w-24 border rounded px-2 py-1 text-sm" placeholder="Unit price">
                                    <input x-model="item.unit_cost" type="number" step="0.01" min="0"
                                           class="w-24 border rounded px-2 py-1 text-sm" placeholder="Unit cost">
                                    <label class="flex items-center gap-1 text-sm">
                                        <input type="checkbox" x-model="item.tax" class="form-checkbox">
                                        Tax
                                    </label>
                                </div>
                                <textarea x-model="item.description" placeholder="Description (optional)"
                                          class="w-full border rounded px-3 py-2 text-sm"></textarea>
                            </div>
                        </template>
                        <div class="flex gap-4">
                            <button @click="addItem('service')" class="text-blue-600 text-sm">+ Services item</button>
                            <button @click="addItem('material')" class="text-blue-600 text-sm">+ Materials item</button>
                        </div>
                    </div>

                    <div class="border p-4 rounded space-y-4">
                        <div class="flex justify-between text-sm">
                            <span>Subtotal</span>
                            <span x-text="subtotal()"></span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span>Tax</span>
                            <span x-text="taxTotal()"></span>
                        </div>
                        <div class="flex justify-between font-semibold text-base">
                            <span>Total</span>
                            <span x-text="total()"></span>
                        </div>
                    </div>

                    <div>
                        <button @click="form.message = ''" class="text-blue-600 text-sm">+ Message</button>
                        <template x-if="form.message !== null">
                            <textarea x-model="form.message" class="w-full border rounded mt-2 px-3 py-2 text-sm"
                                      placeholder="Add a message..."></textarea>
                        </template>
                    </div>
                </div>
            </div>

            <div class="flex justify-end mt-6">
                <button @click="$wire.call('saveJob', form); jobModalOpen = false"
                        class="bg-blue-600 text-white px-6 py-2 rounded">Save job
                </button>
            </div>
        </div>
    </div>
</div>
