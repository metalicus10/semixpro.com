@php
    function transliterate($string) {
    $translit_table = [
        'А' => 'A', 'Б' => 'B', 'В' => 'V', 'Г' => 'G', 'Д' => 'D', 'Е' => 'E', 'Ё' => 'E', 'Ж' => 'Zh', 'З' => 'Z',
        'И' => 'I', 'Й' => 'Y', 'К' => 'K', 'Л' => 'L', 'М' => 'M', 'Н' => 'N', 'О' => 'O', 'П' => 'P', 'Р' => 'R',
        'С' => 'S', 'Т' => 'T', 'У' => 'U', 'Ф' => 'F', 'Х' => 'Kh', 'Ц' => 'Ts', 'Ч' => 'Ch', 'Ш' => 'Sh', 'Щ' => 'Shch',
        'Ы' => 'Y', 'Э' => 'E', 'Ю' => 'Yu', 'Я' => 'Ya',
        'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd', 'е' => 'e', 'ё' => 'e', 'ж' => 'zh', 'з' => 'z',
        'и' => 'i', 'й' => 'y', 'к' => 'k', 'л' => 'l', 'м' => 'm', 'н' => 'n', 'о' => 'o', 'п' => 'p', 'р' => 'r',
        'с' => 's', 'т' => 't', 'у' => 'u', 'ф' => 'f', 'х' => 'kh', 'ц' => 'ts', 'ч' => 'ch', 'ш' => 'sh', 'щ' => 'shch',
        'ы' => 'y', 'э' => 'e', 'ю' => 'yu', 'я' => 'ya'
    ];
    return strtr($string, $translit_table);
}
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ transliterate('Остатки на складе') }}</title>

    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }

        th, td {
            padding: 8px;
            border: 1px solid black;
            text-align: left; /* Выровнять по левому краю */
        }

        th {
            background-color: #f2f2f2;
        }

        .table-bordered {
            border: 1px solid black;
        }
    </style>
</head>
<body>
<h1>{{ transliterate('Остатки на складе') }}</h1>
<table class="table-bordered" style="width:100%; border-collapse:collapse;">
    <thead>
    <tr>
        <th>#</th>
        <th>Name</th>
        <th>SKU</th>
        <th>Brand</th>
        <th>Remainder</th>
    </tr>
    </thead>
    <tbody>

    @foreach($dataFromDatabase as $item)
        <tr>
            <td>{{ $item['id'] }}</td>
            <td>{{ transliterate($item['name']) }}</td>
            <td>{{ transliterate($item['sku']) }}</td>
            <td>{{ transliterate($item['brand']) }}</td>
            <td>{{ $item['quantity'] }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
</body>
</html>
