<?php

namespace App\Livewire;

use App\Exports\PartsExport;
use App\Models\Part;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;

class ManagerInventoryStats extends Component
{
    public $inventory;

    public function mount()
    {
        $userId = Auth::id();
        // Получить последние 10 записей
        $this->inventory = Part::select('name', 'sku', 'quantity')->whereHas('category', function ($query) use ($userId) {
            $query->where('manager_id', $userId);
        })
            ->orderBy('updated_at', 'desc')
            ->take(10)
            ->get();
    }

    public function export($format = 'xlsx')
    {
        $userId = Auth::id();
        if ($format === 'xlsx') {
            return Excel::download(new PartsExport, 'inventory.xlsx');
        } elseif ($format === 'pdf') {

            $dataFromDatabase = Part::whereHas('category', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })->get();

            // Применяем транслитерацию к каждому элементу
            foreach ($dataFromDatabase as &$item) {
                $item->name = $this->transliterate($item->name);
                $item->sku = $this->transliterate($item->sku);
                $item->brand = $this->transliterate($item->brand);
            }

            $pdf = PDF::loadView('exports.parts', compact('dataFromDatabase'));
            return response()->streamDownload(function () use ($pdf) {
                echo $pdf->stream();
            }, 'inventory.pdf');
        }
    }

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


    public function render()
    {
        return view('livewire.manager.manager-inventory-stats')->layout('layouts.app');
    }
}
