<div class="p-4 bg-white shadow-md rounded">
    <h2 class="text-lg font-bold mb-2">История назначений складов</h2>

    <table class="w-full border-collapse border">
        <thead>
        <tr class="bg-gray-200">
            <th class="border p-2">Менеджер</th>
            <th class="border p-2">Техник</th>
            <th class="border p-2">Склад</th>
            <th class="border p-2">Дата назначения</th>
        </tr>
        </thead>
        <tbody>
        @foreach($logs as $log)
            <tr>
                <td class="border p-2">{{ $log->manager->name }}</td>
                <td class="border p-2">{{ $log->technician->name }}</td>
                <td class="border p-2">{{ $log->warehouse->name }}</td>
                <td class="border p-2">{{ $log->assigned_at }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
