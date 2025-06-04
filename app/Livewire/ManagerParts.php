<?php

namespace App\Livewire;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Nomenclature;
use App\Models\Part;
use App\Models\Pn;
use App\Models\Supplier;
use App\Models\PartTransfer;
use App\Models\Shipment;
use App\Models\Technician;
use App\Models\Warehouse;
use App\Models\TechnicianPart;
use App\Models\TechnicianPartUsage;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use Livewire\Attributes\On;
use Livewire\Component;
use Illuminate\Support\Facades\Storage;
use Livewire\WithFileUploads;
use Ramsey\Uuid\Type\Integer;

class ManagerParts extends Component
{
    use WithFileUploads;

    public $warehouses = [];
    public ?int $selectedWarehouseId = null; // ID активного склада
    public ?int $selectedWarehouse = null;
    public $parts;           // Запчасти по складам
    public bool $isEditing = false;     // Флаг редактирования названия склада
    public array $draggingTab = [];     // Перемещаемый склад (drag-and-drop)
    public ?int $editingWarehouseId = null;

    public $brands, $categories;
    public $technicians;
    public $selectedTechnician, $selectedTechnicians;
    public $suppliers, $selectedId;
    public $partQuantities = [];
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
    //public $fullImage;
    public $imgUrl, $fullImage;
    public $startDate, $endDate;
    public $managerUrlModalVisible = false;
    public $managerSupplier, $managerUrl, $managerUrlText;
    public $loaded = false;
    public $selectedPns = [];
    public $availablePns = [];
    public $selectedPartNames = [];
    public $selectedParts = [];
    public $selectedPartPns = null;
    public $urlData, $activeTab = null;

    public $nomenclatures;
    public $partId;
    public $newImage;
    public $showImageModal = false;
    public $newPn;
    public $searchPn = '', $globalImageFileName = '';
    public $partPns;
    public $clickTimers = [];
    public bool $isLoading = false;

    public $warehousesWithParts;

    protected $rules = [
        'newPn' => 'required|string|max:255|unique:pns,number',
    ];

    protected $listeners = [
        'categoryUpdated' => 'refreshComponent',
        'partUpdated' => 'refreshComponent',
        'brandUpdated' => 'refreshComponent',
        'update-part-quantities' => 'updatePartQuantities',
        'open-price-modal' => 'openPriceModal',
        'open-image-modal' => 'openImageModal',
        'pnsAdded' => 'handlePnsUpdated',
        'refreshParts' => '$refresh',
        'defaultWarehouseUpdated' => 'refreshComponent',
        'setPart',
        'urlChanged' => '$refresh',
        'warehouseTabsUpdated' => 'loadWarehouses',
        'nomenclature-updated' => 'loadWarehouses',
        'image-updated' => 'loadWarehouses',
    ];

    public function mount()
    {
        if (empty($this->selectedWarehouseId) && !empty($this->warehouses)) {
            $this->selectedWarehouseId = $this->warehouses[0]['id'];
        }
        $this->loadWarehouses();
        $this->loadCategories();
        $this->loadBrands();
        $this->loadSuppliers();
        $this->loadTechnicians();
    }

    /*public function refreshWarehouses()
    {
        $this->warehouses = Warehouse::all(); // Обновляем список складов
    }*/

    public function switchTab($tab)
    {
        // Просто заглушка, чтобы Livewire понимал, что выполняется обновление
        $this->dispatch('tabSwitched', $tab);
    }

    /**
     * Загружает список складов и их запчастей
     */
    public function loadWarehouses()
    {
        $this->warehouses = Warehouse::where('manager_id', Auth::id())->orderBy('position')
            ->get()->toArray();

        // Устанавливаем активный склад по умолчанию (первый в списке)
        if (empty($this->selectedWarehouseId) && !empty($this->warehouses)) {
            $this->selectedWarehouseId = $this->warehouses[0]['id'];
        }

        // Загружаем запчасти по складам
        $this->loadParts($this->selectedWarehouseId);
    }

    public function loadCategories()
    {
        $this->categories = Category::where('manager_id', Auth::id())->get()->toArray();
    }

    public function loadBrands()
    {
        $this->brands = Brand::where('manager_id', Auth::id())->get()->toArray();
    }

    public function loadTechnicians()
    {
        $this->technicians = Technician::where('manager_id', Auth::id())->get();
    }

    public function loadSuppliers()
    {
        $this->suppliers = Supplier::where('manager_id', Auth::id())->get()->toArray();
    }

    /**
     * Выбирает активный склад
     */
    #[\Livewire\Attributes\Locked]
    public function selectWarehouse($warehouseId)
    {
        $this->selectedWarehouseId = $warehouseId;
        $this->parts = $this->loadParts($warehouseId);
        return ['parts' => $this->parts];
    }

    /**
     * Загружает запчасти для указанного склада
     */
    public function loadParts($warehouseId)
    {
        return $this->parts = Part::where('manager_id', Auth::id())->where('warehouse_id', $warehouseId)->with('nomenclatures', 'warehouse')
            ->get()->toArray();
    }

    /**
     * Переименовываем склад
     */
    public function updateWarehouseName(int $warehouseId, string $newName)
    {
        Warehouse::where('manager_id', Auth::id())->where('id', $warehouseId)->update(['name' => $newName]);

        // Обновляем локальный список
        foreach ($this->warehouses as &$warehouse) {
            if ($warehouse['id'] == $warehouseId) {
                $warehouse['name'] = $newName;
                break;
            }
        }
    }

    /**
     * Запоминаем склад, который начали перетаскивать
     */
    public function startDragging(int $warehouseId, int $index)
    {
        $this->draggingTab = ['id' => $warehouseId, 'index' => $index];
    }

    /**
     * Перемещаем склад в новый порядок
     */
    public function reorderWarehouses(int $warehouseId, int $newIndex)
    {
        // Получаем текущий порядок складов
        $orderedWarehouses = collect($this->warehouses)->sortBy('position')->values();

        // Обновляем порядок складов
        foreach ($orderedWarehouses as $index => $warehouse) {
            if ($warehouse['id'] == $warehouseId) {
                $orderedWarehouses[$index]['position'] = $newIndex;
            } else {
                $orderedWarehouses[$index]['position'] = $index;
            }
        }

        // Сохраняем изменения в БД
        DB::transaction(function () use ($orderedWarehouses) {
            foreach ($orderedWarehouses as $warehouse) {
                Warehouse::where('id', $warehouse['id'])->update(['position' => $warehouse['position']]);
            }
        });

        // Обновляем локальный список складов
        $this->warehouses = $orderedWarehouses->toArray();
    }

    public function startEditingWarehouse(int $warehouseId)
    {
        $this->editingWarehouseId = $warehouseId;
    }

    public function stopEditingWarehouse()
    {
        $this->editingWarehouseId = null;
    }

    public function refreshComponent()
    {
        $this->render();
    }

    public function updateTabOrder(array $newOrder)
    {
        foreach ($newOrder as $order) {
            Warehouse::where('id', $order['id'])->update(['position' => $order['position']]);
        }

        $userId = auth()->id();

        // Обновляем список складов и запчастей
        $managerData = User::with([
            'warehouses' => function ($query) {
                $query->with('parts')->orderBy('position');
            }
        ])->find($userId);

        $this->warehouses = $managerData->warehouses;

        $this->dispatch('tabsUpdated', [
            'tabs' => $this->warehouses->toArray()
        ]);

        $this->dispatch('showNotification', 'success', 'Tab order and parts updated successfully');
    }

    public function getWarehousesWithParts()
    {
        $managerId = auth()->id();
        return Warehouse::where('manager_id', $managerId)
            ->with('parts') // Загружаем связанные запчасти
            ->get();
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

    public function addPn($partId)
    {
        $this->validate();

        // Проверяем существование PN
        if (Pn::where('number', $this->newPn)->exists()) {
            $this->dispatch('showNotification', 'error', 'PN already exists');
            return;
        }

        $part = Part::find($partId);

        // Добавляем новый PN
        Pn::create([
            'number' => $this->newPn,
            'part_id' => $partId,
            'manager_id' => auth()->id(),
            'nomenclature_id ' => $part->nomenclature_id,
        ]);
        $this->updatePartPnsJson($part);

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

    public function getFilteredNomenclatures()
    {
        // Начальный запрос для выборки запчастей, связанный с пользователем
        $userId = auth()->user()->id;
        $partsQuery = Nomenclature::where(function ($query) use ($userId) {
            // Учитываем запчасти, которые связаны с категорией текущего менеджера
            $query->whereHas('category', function ($query) use ($userId) {
                $query->where('manager_id', $userId);
            });
        })
            /*->where(function ($query) use ($userId) {
                // Учитываем запчасти без склада или запчасти, привязанные к складам текущего менеджера
                $query->whereNull('warehouse_id')
                    ->orWhereHas('warehouse', function ($query) use ($userId) {
                        $query->where('manager_id', $userId);
                    });
            })*/
            ->with('parts', 'category', 'brands', 'pns', 'warehouse');

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
        return $partsQuery->get();
    }

    public function setPart($part)
    {
        $this->selectedPartPns = $part;
    }

    public function updatedTransferQuantities($value, $partId)
    {
        $part = Part::find($partId);
        if ($value > $part->quantity) {
            $this->transferQuantities[$partId] = $part->quantity;
            $this->dispatch('showNotification', 'warning', 'The spare part will be depleted, replenishment is required');

            \App\Models\Notification::create([
                'user_id' => auth()->id(),
                'type'    => 'part_moved',
                'message' => "Запчасть '{$part->name}' была перемещена.",
                'payload' => ['part_id' => $part->id],
            ]);
            $this->dispatch('notificationAdded')->to('global-notification');
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
        $technicianIds = $this->selectedTechnicians;

        if (!$technicianIds) {
            $this->dispatch('showNotification', 'error', 'Не выбраны техники для передачи запчастей');
            return;
        }

        foreach ($this->selectedTechnicians as $technicianId) {
            // Находим техника по идентификатору
            $technician = Technician::find($technicianId);

            if (!$technician) {
                $this->dispatch('showNotification', 'error', 'Техник с ID' . $technicianId . ' не найден');
                continue; // Переходим к следующему технику
            }

            $movedPartsNames = [];
            $movedPartsIds = [];

            // Проверяем, есть ли выбранные запчасти с указанными количествами
            foreach ($this->partQuantities as $partId => $quantity) {
                $part = Part::find($partId);

                if (!$part) {
                    $this->dispatch('showNotification', 'error', 'Запчасть с ID' . $partId . ' не найдена');
                    continue;
                }

                $quantity = $this->partQuantities[$partId] ?? 0;

                // Убедимся, что запрашиваемое количество не превышает количество на складе
                if ($quantity > $part->quantity) {
                    $quantity = $part->quantity;
                }

                if ($quantity <= 0) {
                    continue;
                }

                // Уменьшаем количество запчастей на складе
                $part->update(['quantity' => $part->quantity - $quantity]);
                $movedPartsNames[] = $part->name;
                $movedPartsIds[] = $part->id;

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
                        'nomenclature_id' => $part->nomenclature_id,
                    ]);
                }
                $minQuantity = Auth::user()->default_min_quantity;
                if ($part->quantity <= $minQuantity) {
                    \App\Models\Notification::create([
                        'user_id' => auth()->id(),
                        'type' => 'low_stock',
                        'message' => "Осталось мало запчастей '{$part->name}' на складе!",
                        'payload' => [
                            'part_ids' => $movedPartsIds,
                            'warehouse_id' => $part->warehouse_id,
                        ],
                    ]);
                }
            }
            if (count($movedPartsIds)) {
                // Записать уведомление технику
                \App\Models\Notification::create([
                    'user_id' => $technician->user_id,
                    'type'    => 'parts_received',
                    'message' => "Вам были переданы новые запчасти: '".implode(", ", $movedPartsNames)."'",
                    'payload' => [
                        'part_ids' => $movedPartsIds,
                        'warehouse_id' => $this->selectedWarehouseId,
                    ],
                ]);
                \App\Models\Notification::create([
                    'user_id' => auth()->id(),
                    'type'    => 'parts_moved',
                    'message' => "Запчасти '".implode(", ", $movedPartsNames)."' были перемещены.",
                    'payload' => [
                        'part_ids' => $movedPartsIds,
                        'warehouse_id' => $this->selectedWarehouseId,
                    ],
                ]);
                $this->dispatch('notificationAdded')->to('global-notification');
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

            foreach ($parts as $part) {
                if ($part->image && Storage::disk('public')->exists($part->image)) {
                    Storage::disk('public')->delete($part->image);
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

    /*public function handleClick($partId)
    {
        if (isset($this->clickTimers[$partId])) {
            $this->handleDoubleClick($partId);
            unset($this->clickTimers[$partId]);
        } else {
            $this->clickTimers[$partId] = now()->addMilliseconds(300);

            // Запуск отложенного действия (если не будет второго клика)
            $this->dispatch('delayed-click', ['partId' => $partId]);
        }
    }

    public function handleDoubleClick($partId)
    {
        unset($this->clickTimers[$partId]);
        $this->openManagerUrlModal($partId);
    }*/

    public function openManagerUrlModal($partId)
    {
        $this->selectedId = $partId;
        $part = Part::find($partId);
        $data = json_decode($part->url, true) ?? ['text' => '', 'url' => ''];

        $this->managerUrlText = $data['text'] ?? '';

        $this->managerUrl = $data['url'] ?? '';
        $this->dispatch('modal-open', ['partId' => $partId]); // Теперь передаём данные в Alpine
    }

    public function updatePartURL($partId, $supplier, $url)
    {
        $part = Part::find($partId);

        if ($part) {
            $part->url = json_encode([
                'text' => $supplier,
                'url' => $url
            ]);
            $part->save();
        }

        // Обновление данных в AlpineJS
        $this->dispatch('urlUpdated', ['partId' => $partId]);
    }

    public function getUrlData($partId)
    {
        $part = Part::find($partId);
        return json_decode($part->url, true);
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
                    'manager_id' => auth()->id(),
                    'nomenclature_id ' => json_encode([$part->nomenclature_id]),
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

    public function uploadImage($id)
    {
        $this->validate([
            'newImage' => 'required|image|max:5200',
        ]);

        // Получаем запчасть
        $part = Part::find($id);

        if (!$part) {
            $this->dispatch('showNotification', 'error', 'Part not found');
            return;
        }

        // Удаляем старое изображение, если оно есть
        if ($part->image) {
            Storage::disk('public')->delete($part->image);
        }

        if ($this->newImage) {
            $manager = new ImageManager(Driver::class);

            $processedImage = $manager->read($this->newImage)
                ->resize(null, null)
                ->toWebp(quality: 60);

            $imagePath = '/images/parts/' . Auth::id();
            // Генерируем уникальное имя для файла
            $fileName = $imagePath . '/' . uniqid() . '.webp';
            $this->globalImageFileName = $fileName;

            // Сохраняем закодированное изображение в local storage
            Storage::disk('public')->put($fileName, $processedImage);
            $part->update(['image' => $fileName]);
        }

        $this->closeImageModal();

        $this->dispatch('showNotification', 'success', 'PartImage updated successfully!');
        $this->dispatch('image-updated', ['imageUrl' => $this->globalImageFileName]);

        // Сбрасываем состояние
        $this->reset('newImage');
    }

    /*#[On('image-updated')]
    public function updatePartImage($partId)
    {
        $updatedPart = Part::with('nomenclatures', 'warehouse')->find($partId);

        if ($updatedPart) {
            // Находим запчасть в массиве и обновляем её данные
            foreach ($this->parts as &$part) {
                if ($part['id'] == $partId) {
                    $part['image'] = $updatedPart->image;
                    $part['nomenclatures'] = $updatedPart->nomenclatures;
                    $part['warehouse'] = $updatedPart[0]->warehouse;
                    break;
                }
            }
        }

        //$this->dispatch('refreshParts');
        $this->dispatch('update-part-image');
    }*/

    public function showImage($imageUrl)
    {
        $this->fullImage = $imageUrl;
    }

    public function closeImage()
    {
        $this->fullImage = null;
    }

    public function openImageModal($partId)
    {
        $this->selectedPartId = $partId;
        $this->showImageModal = true;
    }

    public function closeImageModal()
    {
        $this->reset(['showImageModal', 'selectedPartId', 'newImage']);
    }

    public function updatedNewImage()
    {
        if ($this->newImage) {
            $this->showImageModal = true;
        }
    }

    public function renameWarehouse($warehouseId, $newName)
    {
        $warehouse = Warehouse::find($warehouseId);
        if ($warehouse) {
            $warehouse->update(['name' => $newName]);
            $this->dispatch('partUpdated');
        }
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
        /*$userId = Auth::id();
        $user = Auth::user();
        $this->technicians = Technician::where('manager_id', $userId)->where('is_active', true)->get();
        $this->nomenclatures = $this->getFilteredNomenclatures();

        if ($user->inRole('technician')) {
            // Показываем только запчасти, связанные со складами техника
            $this->parts = Part::whereIn('warehouse_id', $user->warehouses()->pluck('id'))
                ->get();
        }

        $managerData = User::with([
            'categories',
            'brands',
            'managedWarehouses' => function ($query) {
                $query->with('parts')->orderBy('position');
            }
        ])->find($userId);
        $this->categories = $managerData->categories;
        $this->brands = $managerData->brands;
        $this->warehouses = $managerData->warehouses;

        if (!empty($this->nomenclatures->warehouse)) {
            foreach ($this->nomenclatures->warehouse as $warehouse) {
                if ($warehouse->is_default === 1) {
                    $this->activeTab = $warehouse->id;
                }
            }
        }
        if (!empty($this->nomenclatures->parts)) {
            foreach ($this->nomenclatures->parts as $part) {
                $this->urlData[$part->id] = json_decode($part->url, true) ?? [];
            }
        }*/

        return view('livewire.manager.manager-parts')
            ->layout('layouts.app');
    }
}
