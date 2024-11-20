<?php

namespace App\Livewire;

use App\Models\Category;
use App\Models\Part;
use App\Models\Pn;
use App\Models\Supplier;
use App\Models\PartTransfer;
use App\Models\Shipment;
use App\Models\Technician;
use App\Models\TechnicianPart;
use App\Models\TechnicianPartUsage;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Intervention\Image\Drivers\Imagick\Driver;
use Intervention\Image\ImageManager;
use Livewire\Component;
use Illuminate\Support\Facades\Storage;
use Livewire\WithFileUploads;

class ManagerParts extends Component
{
    use WithFileUploads;

    public $brands;
    public $technicians;
    public $selectedTechnicians;
    public $categories;
    public $suppliers;
    public $partQuantities = [];
    public $selectedTechnician = null;
    public $selectedPartId = null;
    public $selectedCategory = null;
    public $selectedBrand = null;
    public $selectedSupplier = null;
    public $selectedBrands;
    public array $transferQuantities = [];
    public bool $openPriceModal = false;
    public string $search = '';
    public $newPrice = '';
    public $quantityToAdd = 1;
    public $operation = null;
    public $showQuantityModal = false;
    public $isPriceHistoryModalOpen = false;
    public $errorMessage = '';
    public $fullImage;
    public $imgUrl;
    public $startDate, $endDate;
    public $managerPartUrlModalVisible = false;
    public $managerPartSupplier = '';
    public $managerPartUrl = '';
    public $loaded = false;
    public $selectedPns = [];
    public $availablePns = [];
    public $selectedPartNames = [];
    public $selectedParts = [];

    public $partId;
    public $newImage;
    public $showImageModal = false;
    public $newPn;
    public $searchPn = '';
    public $partPns;

    protected $rules = [
        'newPn' => 'required|string|max:255|unique:pns,number',
    ];

    protected $listeners = [
        'categoryUpdated' => 'refreshComponent',
        'partUpdated' => 'refreshComponent',
        'brandUpdated' => 'refreshComponent',
        'update-part-quantities' => 'updatePartQuantities',
        'open-price-modal' => 'openPriceModal',
        'pnsAdded' => 'handlePnsUpdated',
        'refreshParts' => '$refresh'
    ];

    public function mount()
    {
        $this->loadSuppliers();
    }

    public function loadComponent()
    {
        $this->loaded = true;
    }

    public function loadSuppliers()
    {
        $this->suppliers = Supplier::where('manager_id', Auth::id())->get();
    }

    public function refreshComponent()
    {
        $this->render();
    }

    public function getPartPns($partId)
    {
        // Получить все PN для запчасти с указанным ID
        $pns = Pn::where('part_id', $partId)->get();

        // Вернуть массив или использовать данные по необходимости
        return $pns;
    }

    public function deletePns($partId, $selectedPns)
    {
        $part = Part::find($partId);

        if (!$part) {
            $this->dispatch('showNotification', 'error', 'Part not found');
            return;
        }

        if (!empty($selectedPns)) {
            // Удаляем выбранные PN из таблицы pns
            $part->pns()->whereIn('id', $selectedPns)->get()->each->delete();

            // Обновляем JSON в колонке parts.pns
            $this->updatePartPnsJson($part);

            $this->dispatch('showNotification', 'success', 'PNs deleted successfully');
        } else {
            $this->dispatch('showNotification', 'error', 'No PNs selected for deletion');
        }
    }

    public function addPn()
    {
        $this->validate();

        // Проверяем существование PN
        if (Pn::where('number', $this->newPn)->exists()) {
            $this->dispatch('showNotification', 'error', 'PN already exists');
            return;
        }

        $part = Part::find($this->partId);
        $this->updatePartPnsJson($part);

        // Добавляем новый PN
        Pn::create([
            'number' => $this->newPn,
            'part_id' => $this->partId,
            'manager_id' => auth()->id(),
        ]);

        $this->dispatch('pn-added');
        $this->dispatch('showNotification', 'success', 'PN added successfully');
        $this->newPn = null;
    }

    public function updatePartPnsJson($part)
    {
        $pns = Pn::where('part_id', $part->id)->where('manager_id', auth()->id())->pluck('number')->toArray();

        $json = json_encode((object)$pns);

        $part->update([
            'pns' => $json,
        ]);
    }

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
        })->with('category', 'brands', 'pns');

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
                    ->orWhere('pns', 'like', '%' . $this->search . '%')
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
            $this->dispatch('showNotification', 'warning', 'The spare part will be depleted, replenishment is required');
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
        if (empty($this->selectedTechnicians)) {
            $this->dispatch('showNotification', 'error', 'Не выбраны техники для передачи запчастей');
            return;
        }

        foreach ($this->selectedTechnicians as $technicianId) {
            // Находим техника по идентификатору
            $technician = Technician::find($technicianId);

            if (!$technician) {
                $this->dispatch('showNotification', 'error', 'Техник с ID'.$technicianId.' не найден');
                continue; // Переходим к следующему технику
            }

            // Проверяем, есть ли выбранные запчасти с указанными количествами
            foreach ($this->partQuantities as $partId => $quantity) {
                $part = Part::find($partId);

                if (!$part) {
                    $this->dispatch('showNotification', 'error', 'Запчасть с ID'.$partId.' не найдена');
                    continue; // Переходим к следующей запчасти
                }

                $quantity = $this->partQuantities[$partId] ?? 1;

                // Убедимся, что запрашиваемое количество не превышает количество на складе
                if ($quantity > $part->quantity) {
                    $quantity = $part->quantity;
                }

                if ($quantity <= 0) {
                    continue; // Пропускаем, если количество равно 0 или отрицательное
                }

                // Уменьшаем количество запчастей на складе
                $part->update(['quantity' => $part->quantity - $quantity]);

                // Проверяем, есть ли у техника уже эта запчасть
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
        }

        // Закрываем модальное окно и сбрасываем выбранные значения
        $this->dispatch('modal-close');
        $this->dispatch('showNotification', 'success', 'Запчасти успешно переданы выбранным техникам');
        $this->reset(['selectedTechnicians', 'partQuantities', 'selectedPartId']);
    }

    public function getSelectedPartNames()
    {
        return Part::whereIn('id', $this->selectedParts)->pluck('name')->toArray();
    }

    public function updatedSelectedParts()
    {
        $this->selectedPartNames = Part::whereIn('id', $this->selectedParts)->pluck('name')->toArray();
    }

    public function deleteParts()
    {
        if (!empty($this->selectedParts)) {
            $parts = Part::whereIn('id', $this->selectedParts)->get();

            foreach($parts as $part){
                if ($part->image && Storage::disk('s3')->exists($part->image)) {
                    Storage::disk('s3')->delete($part->image);
                }

                // Обновляем запись в базе данных
                $part->update(['image' => null]);
            }

            // Удаление записей в technician_parts
            TechnicianPart::whereIn('part_id', $this->selectedParts)->delete();

            // Удаляем запчасти
            Part::whereIn('id', $this->selectedParts)->delete();

            $this->selectedParts = [];
            $this->dispatch('showNotification', 'success', 'Selected parts deleted successfully!');
        } else {
            $this->dispatch('showNotification', 'warning', 'No parts selected for deletion!');
        }
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
        //$this->dispatch('partUpdated');
        $this->dispatch('part-updated', ['partId' => $part->id, 'newQuantity' => $part->quantity]);

    }

    public function updateName($partId, $newName)
    {
        $part = Part::find($partId);

        if (!$part) {
            $this->dispatch('showNotification', 'error', 'Part not found');
            return;
        }

        // Обновляем название
        $part->name = $newName;
        $part->save();

        $this->dispatch('showNotification', 'success', 'Part name updated successfully');
    }

    public function savePns($partId, $selectedPns)
    {
        // Найти запчасть по ID
        $part = Part::find($partId);

        if (!$part) {
            $this->dispatch('showNotification', 'error', 'Part not found');
            return;
        }

        // Удаляем существующие PN, которые отсутствуют в выбранном списке
        $part->pns()->whereNotIn('number', $selectedPns)->delete();

        // Проверяем и добавляем новые PN
        foreach ($selectedPns as $pn) {
            // Проверяем существование PN для данной запчасти
            $exists = Pn::where('number', $pn)->where('part_id', $part->id)->exists();

            if (!$exists) {
                // Добавляем только новые PN
                Pn::create([
                    'number' => $pn,
                    'part_id' => $part->id,
                ]);
            }
        }

        // Уведомление об успешной операции
        $this->dispatch('showNotification', 'success', 'PNs updated successfully!');
    }

    public function updatePartPrice($partId, $newPrice)
    {
        // Находим запчасть
        $part = Part::find($partId);

        if ($part && $this->newPrice) {
            // Проверяем, отличается ли новая цена от текущей
            if ($part->price == $newPrice) {
                $this->dispatch('showNotification', 'info', 'Цена не изменена');
                return; // Выходим из метода, если цена не изменилась
            }

            // Записываем текущую цену в таблицу истории
            DB::table('part_price_history')->insert([
                'part_id' => $part->id,
                'price' => $part->price,
                'changed_at' => now(),
            ]);

            // Обновляем цену запчасти
            $part->update(['price' => $newPrice]);

            $this->dispatch('showNotification', 'success', 'Цена успешно обновлена и записана в историю');
        } else {
            $this->dispatch('showNotification', 'info', 'Цена не была введена');
        }
    }

    public function openPriceHistoryModal($partId)
    {
        $this->selectedPartId = $partId;
        $this->isPriceHistoryModalOpen = true;
    }

    public function openManagerPartUrlModal($partId)
    {
        $this->selectedPartId = $partId;
        $part = Part::find($partId);

        $data = json_decode($part->url, true) ?? [];
        $this->managerPartSupplier = $data['text'] ?? '';
        $this->managerPartUrl = $data['url'] ?? '';
        $this->managerPartUrlModalVisible = true;
    }

    public function saveManagerPartUrl()
    {
        $part = Part::find($this->selectedPartId);
        //$part->url = json_encode(['text' => '', 'url' => $this->url]);
        $part->url = json_encode([
            'text' => $this->managerPartSupplier,
            'url' => $this->managerPartUrl,
        ]);
        $part->save();

        $this->managerPartUrlModalVisible = false;
        $this->refreshComponent();
    }

    public function updatePartBrands($partId, $selectedBrands)
    {
        $part = Part::find($partId);
        $part->brands()->sync($selectedBrands);

        // Обновляем данные в представлении
        $this->dispatch('brandsUpdated');
    }

    public function openImageModal($partId)
    {
        $this->partId = $partId;
        $this->showImageModal = true;
    }

    public function closeImageModal()
    {
        $this->reset(['showImageModal', 'selectedPartId', 'newImage']);
    }

    public function uploadImage()
    {
        $this->validate([
            'newImage' => 'required|image|max:5200',
        ]);

        $userId = auth()->id();
        $path = 'partsImages/' . $userId;
        // Получаем запчасть
        $part = Part::find($this->partId);

        if (!$part) {
            $this->dispatch('showNotification', 'error', 'Part not found');
            return;
        }

        // Удаляем старое изображение, если оно есть
        if ($part->image) {
            Storage::disk('s3')->delete($part->image);
        }

        if ($this->newImage)
        {
            $tempPath = $this->newImage->getRealPath();
            $tempImg = Storage::disk('s3')->get($tempPath);
            $manager = new ImageManager(Driver::class);
            $processedImage = $manager->read($tempImg)
                ->resize(1024, 1024, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                })
                ->toWebp(quality: 60);
            $fileName = $path. '/' . uniqid() . '.webp';
            Storage::disk('s3')->put($fileName, $processedImage);
            $this->imgUrl = Storage::disk('s3')->url($fileName);
        }

        // Обновляем модель
        $part->update(['image' => $this->imgUrl]);
        $this->closeImageModal();

        $this->dispatch('showNotification', 'success', 'Image updated successfully!');
        $this->dispatch('imageUpdated', ['partId' => $this->partId]);

        // Сбрасываем состояние
        $this->reset('newImage');
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
