<div class="max-h-[400px] overflow-y-auto text-sm space-y-2">
    @foreach($log->items as $item)
        <div class="border-b pb-2">
            <strong>{{ $item['name'] }}</strong>
            <ul class="list-disc ml-4 text-gray-700">
                @foreach($item['changes'] as $field => $value)
                    <li><strong>{{ $field }}</strong>: {{ $value }}</li>
                @endforeach
            </ul>
        </div>
    @endforeach
</div>
