<?php

namespace App\Livewire;

use App\Models\Category;
use App\Models\Part;
use App\Models\PartTransfer;
use App\Models\Shipment;
use App\Models\Technician;
use App\Models\TechnicianPart;
use App\Models\TechnicianPartUsage;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class ManagerParts extends Component
{
    public $brands;
    public $technicians;
    public $categories;
    public $selectedParts = [];
    public $partQuantities = [];
    public $selectedTechnician = null;
    public $selectedPartId = null;
    public $selectedCategory = null;
    public $selectedBrand = null;
    public array $transferQuantities = [];
    public ?int $technicianId = null;
    public bool $openPriceModal = false;
    public bool $isSendButtonDisabled = true;
    public string $search = '';
    public $selectedRows = [];
    public array $newPrice = [];
    public $quantityToAdd = 1;
    public $operation = null;
    public $showQuantityModal = false;
    public $isPriceHistoryModalOpen = false;
    public $errorMessage = '';
    public $fullImage;
    public $startDate, $endDate;

    protected $listeners = [
        'categoryUpdated' => 'refreshComponent',
        'partUpdated' => 'refreshComponent',
        'brandUpdated' => 'refreshComponent',
        'update-part-quantities' => 'updatePartQuantities',
        'open-price-modal' => 'openPriceModal'
    ];

    public function showImage($imageUrl)
    {
        $this->fullImage = $imageUrl;
    }

    public function closeImage()
    {
        $this->fullImage = null;
    }

    public function getFilteredParts()
    {
        // Начальный запрос для выборки запчастей, связанный с пользователем
        $userId = auth()->user()->id;
        $partsQuery = Part::whereHas('category', function ($query) use ($userId) {
            $query->where('manager_id', $userId);
        })->with('category', 'brands');

        // Фильтр по категории
        if ($this->selectedCategory) {
            $partsQuery->where('category_id', $this->selectedCategory);
        }

        // Фильтр по бренду
        if ($this->selectedBrand) {
            $partsQuery->whereHas('brands', function ($query) {
                $query->where('brands.id', $this->selectedBrand);
            });
        }

        // Добавляем фильтрацию по поисковому запросу
        if ($this->search) {
            $partsQuery->where(function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('sku', 'like', '%' . $this->search . '%')
                    ->orWhereHas('category', function ($q) {
                        $q->where('name', 'like', '%' . $this->search . '%');
                    })
                    ->orWhereHas('brands', function ($q) {
                        $q->where('name', 'like', '%' . $this->search . '%');
                    });
            });
        }

        // Возвращаем пагинированный результат
        return $partsQuery->paginate(30);
    }

    public function updatedTransferQuantities($value, $partId)
    {
        $part = Part::find($partId);
        if ($value > $part->quantity) {
            $this->transferQuantities[$partId] = $part->quantity;
            session()->flash('warning', 'Запчасть будет исчерпана, требуется пополнение.');
        }
        $this->updateSendButtonState();
    }

    public function openPriceModal($partId)
    {
        $this->selectedPartId = $partId;
        $this->openPriceModal = true;
    }

    public function closePriceModal()
    {
        $this->selectedPartId = null;
        $this->openPriceModal = false;
    }

    public function updatePartQuantities($quantities)
    {
        if (is_array($quantities)) {
            $this->partQuantities = $quantities;
        } else {
            $this->partQuantities = [];
        }
    }

    public function sendParts()
    {
        // Находим техника по идентификатору
        $technician = Technician::find($this->selectedTechnician);

        if (!$technician) {
            session()->flash('error', 'Техник не найден.');
            return;
        }
        // Проверяем, есть ли выбранные запчасти с указанными количествами
        foreach ($this->partQuantities as $partId => $quantity) {
            $part = Part::find($partId);

            $quantity = $this->partQuantities[$partId] ?? 1;

            // Убедимся, что запрашиваемое количество не превышает количество на складе
            if ($quantity > $part->quantity) {
                $quantity = $part->quantity;
            }

            // Уменьшаем количество запчастей на складе
            $part->update(['quantity' => $part->quantity - $quantity]);

            // Проверяем, есть ли у техника уже эта запчасть, если да - обновляем
            $technicianPart = TechnicianPart::where('technician_id', $technician->user_id)
                ->where('part_id', $part->id)
                ->first();

            if ($technicianPart) {
                // Увеличиваем количество запчастей у техника и общее количество переданных
                $technicianPart->increment('quantity', $quantity);
                $technicianPart->increment('total_transferred', $quantity);
            } else {
                // Создаем запись для новой запчасти, если её ещё нет у техника
                TechnicianPart::create([
                    'technician_id' => $technician->user_id,
                    'part_id' => $part->id,
                    'quantity' => $quantity,
                    'total_transferred' => $quantity,
                    'manager_id' => Auth::id(),
                ]);
            }
        }

        // Закрываем модальное окно и сбрасываем выбранные значения
        $this->dispatch('modal-close');
        session()->flash('message', 'Запчасти успешно переданы.');
        $this->isPriceHistoryModalOpen = false;
        $this->reset(['selectedTechnician', 'partQuantities', 'selectedPartId']);
    }

    // Метод для увеличения на единицу
    public function incrementPart($partId)
    {
        $part = Part::find($partId);
        $part->quantity += 1;
        $part->save();
        $this->dispatch('partUpdated'); // Для обновления списка в шаблоне
    }

    // Метод для меньшения на единицу
    public function decrementPart($partId)
    {
        $part = Part::find($partId);
        $part->quantity -= 1;
        $part->save();
        $this->dispatch('partUpdated'); // Для обновления списка в шаблоне
    }

    // Открытие модального окна для добавления или удаления количества
    public function openQuantityModal($partId, $operation = 'add')
    {
        $this->resetErrorMessage();
        $this->selectedPartId = $partId;
        $this->operation = $operation; // Устанавливаем операцию
        $this->showQuantityModal = true;
    }

    // Метод для добавления или вычитания количества
    public function modifyQuantity()
    {
        $part = Part::find($this->selectedPartId);
        if ($part) {
            if ($this->operation === 'add') {
                $part->quantity += $this->quantityToAdd;
            } elseif ($this->operation === 'subtract') {
                if ($this->quantityToAdd > $part->quantity) {
                    $this->errorMessage = 'Количество для уменьшения не может превышать текущий запас!';
                    return;
                }
                if ($part->quantity >= $this->quantityToAdd) {
                    $part->quantity -= $this->quantityToAdd;
                }
            }
            $part->save();
        }

        $this->resetQuantityModal();
        $this->dispatch('partUpdated');
    }

    public function updatePartPrice($partId)
    {
        // Находим запчасть
        $part = Part::where('id', $partId)->get();

        if ($part && isset($this->newPrice[$partId])) {
            // Записываем текущую цену в таблицу истории
            DB::table('part_price_history')->insert([
                'part_id' => $part->id,
                'price' => $part->price,
                'changed_at' => now(),
            ]);

            // Обновляем цену запчасти
            $part->update(['price' => $this->newPrice[$partId]]);

            session()->flash('message', 'Цена успешно обновлена и записана в историю.');
        } else {
            session()->flash('error', 'Запчасть не найдена.');
        }
    }

    public function openPriceHistoryModal($partId)
    {
        $this->selectedPartId = $partId;
        $this->isPriceHistoryModalOpen = true;
    }

    // Сброс модального окна
    public function resetQuantityModal()
    {
        $this->selectedPartId = null;
        $this->quantityToAdd = 1;
        $this->showQuantityModal = false;
        $this->resetErrorMessage();
    }

    // Сброс ошибки
    public function resetErrorMessage()
    {
        $this->errorMessage = '';
    }

    public function render()
    {
        $userId = Auth::id();
        $this->technicians = Technician::where('manager_id', $userId)->where('is_active', true)->get();
        $parts = $this->getFilteredParts();
        $managerData = User::with(['categories', 'brands'])->find($userId);
        $this->categories = $managerData->categories;
        $this->brands = $managerData->brands;

        return view('livewire.manager.manager-parts', [
            'parts' => $parts, 'categories' => $this->categories, 'technicians' => $this->technicians,
            'isPriceHistoryModalOpen' => $this->isPriceHistoryModalOpen,
            'selectedPartId' => $this->selectedPartId
        ])
            ->layout('layouts.app');
    }
}
