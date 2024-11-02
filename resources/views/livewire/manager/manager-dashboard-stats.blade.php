<div class="bg-white shadow-md rounded-lg p-6 mb-6">
    <h3 class="text-xl font-bold mb-4">Статистика отгрузок</h3>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Отгрузки за день -->
        <div class="bg-gray-100 p-4 rounded-lg">
            <p class="text-sm font-semibold">Отгрузки за день</p>
            <p class="text-3xl font-bold">{{ $dailyShipments }}</p>
        </div>

        <!-- Отгрузки за неделю -->
        <div class="bg-gray-100 p-4 rounded-lg">
            <p class="text-sm font-semibold">Отгрузки за неделю</p>
            <p class="text-3xl font-bold">{{ $weeklyShipments }}</p>
        </div>

        <!-- Отгрузки за месяц -->
        <div class="bg-gray-100 p-4 rounded-lg">
            <p class="text-sm font-semibold">Отгрузки за месяц</p>
            <p class="text-3xl font-bold">{{ $monthlyShipments }}</p>
        </div>
    </div>

    <!-- Топ запчастей -->
    <h4 class="text-lg font-bold mt-6">Топ отгружаемых запчастей</h4>
    <ul class="list-disc list-inside">
        @foreach($topParts as $part)
            <li>{{ $part->part_id }} {{ $part->part->name }} — {{ $part->total }} отгрузок</li>
        @endforeach
    </ul>
</div>
