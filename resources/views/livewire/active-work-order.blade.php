<div class="bg-[#18242D] rounded-2xl p-6 w-full flex flex-col gap-4">
    <div class="flex items-center justify-between mb-2">
        <h2 class="text-white text-lg font-bold">Active work order</h2>
        <a href="#" class="text-[#00DD7F] text-sm font-semibold hover:underline flex items-center gap-1">VIEW MORE <span>&rarr;</span></a>
    </div>
    <div class="flex flex-col gap-4">
        @foreach($orders as $order)
            <div class="bg-[#212F39] rounded-xl p-4 flex flex-col gap-2">
                <div class="flex items-center justify-between">
                    <span class="text-white font-bold">{{ $order['name'] }}</span>
                    <span class="text-gray-400 text-xs">Total finished: {{ $order['finished'] }}pcs</span>
                    <span class="text-gray-400 text-xs">Required quantity: {{ $order['required'] }}pcs</span>
                </div>
                <div class="w-full h-2 bg-[#22323E] rounded-lg overflow-hidden my-1">
                    <div class="h-2 bg-[#00DD7F]" style="width: {{ intval($order['finished'] / $order['required'] * 100) }}%"></div>
                </div>
                <div class="flex items-center gap-3 text-xs text-gray-400 mt-1">
                    <span class="flex items-center gap-1">
                        <svg class="w-4 h-4 text-[#00DD7F]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M8 7V3m8 4V3M3 11h18M5 19h14a2 2 0 002-2v-5a2 2 0 00-2-2H5a2 2 0 00-2 2v5a2 2 0 002 2z"></path>
                        </svg>
                        EXPECTED FINISHED DATE <span class="text-white ml-1">{{ $order['date'] }}</span>
                    </span>
                    <span class="flex items-center gap-1">
                        <svg class="w-4 h-4 text-[#00DD7F]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <circle cx="12" cy="12" r="10" />
                            <path d="M12 6v6l4 2" />
                        </svg>
                        WORK ORDER COST <span class="text-white ml-1">${{ number_format($order['cost'], 0, '', ' ') }}</span>
                    </span>
                </div>
            </div>
        @endforeach
    </div>
</div>
