<?php

namespace App\Livewire;

use App\Models\ActionLog;
use App\Models\Brand;
use App\Models\BulkLog;
use App\Models\Category;
use App\Models\NomenclatureVersion;
use App\Models\Part;
use App\Models\Supplier;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use Livewire\Component;
use App\Models\Nomenclature;
use Illuminate\Support\Facades\Auth;
use Livewire\WithFileUploads;
use Illuminate\Validation\ValidationException;

class ManagerNomenclatures extends Component
{
    use WithFileUploads;

    public array $nomenclatures = [], $archived_nomenclatures = [], $selectedNomenclatures = [], $selectedBrands = [];
    public $manager_id, $nn, $name, $category_id, $supplier_id, $image, $version, $categories, $brands, $suppliers, $allNns;
    public $editingNomenclature = null, $idToDelete;
    public bool $showArchived, $managerUrlModalVisible = false;
    public $managerUrl, $selectedId, $managerSupplier, $nomenclatureImage, $nomenclature;

    public int $nomenclatureCount = 0;

    protected $listeners = [
        'update-categories' => 'updateCategories',
        'nomenclature-restore' => 'updateNomenclatures',
        'nomenclature-updated' => 'updateNomenclatures',
        'image-updated' => 'updateNomenclatures',
    ];

    // Массив для добавления новой номенклатуры
    public $newNomenclature = [
        'nn' => '',
        'name' => '',
        'category_id' => null,
        'supplier_id' => null,
        'brand_id' => null,
        'manager_id' => '',
        'image' => '',
    ];

    public function mount()
    {
        $this->allNns = Nomenclature::pluck('nn')->where('manager_id', Auth::id())->toArray();
        $this->categories = Category::where('manager_id', Auth::id())->get()->toArray();
        $this->suppliers = Supplier::where('manager_id', Auth::id())->get()->toArray();
        $this->brands = Brand::where('manager_id', Auth::id())->get()->toArray();

        $this->nomenclatureCount = Nomenclature::where('manager_id', Auth::id())->count();
        $this->loadNomenclatures();

        $this->archived_nomenclatures = Nomenclature::where('is_archived', true)
            ->where('manager_id', $this->manager_id)
            ->with('category', 'suppliers')->get()->toArray();
    }

    /**
     * Загружает номенклатуры менеджера
     */
    public function loadNomenclatures()
    {
        if ($this->nomenclatureCount <= 500) {
            // Мало записей: грузим ВСЕ для Alpine
            $this->nomenclatures = Nomenclature::where('manager_id', Auth::id())
                ->with('category', 'suppliers', 'brands')
                ->get()
                ->toArray();
        } else {
            // Много записей: грузим ПУСТОЙ список, будем подгружать через запросы
            $this->nomenclatures = [];
        }
    }

    public function updateNomenclatures()
    {
        $this->nomenclatures = Nomenclature::where('manager_id', Auth::id())
            ->with('category', 'suppliers', 'brands')->get()->toArray();
    }

    public function updateCategories()
    {
        $this->categories = Category::where('manager_id', Auth::id())->get()->toArray();
        $this->dispatch('refreshCategorySelect');
    }

    public function updateSuppliers()
    {
        $this->suppliers = Supplier::where('manager_id', Auth::id())->get()->toArray();
        $this->dispatch('refreshSupplierSelect');
    }

    public function updateBrands()
    {
        $this->suppliers = Brand::where('manager_id', Auth::id())->get()->toArray();
        $this->dispatch('refreshBrandSelect');
    }

    public function addNomenclature()
    {
        //dd($this->newNomenclature);
        logger('Вызван метод addNomenclature');
        $validatedData = $this->validate([
            'newNomenclature.nn' => 'required|string|max:10|unique:nomenclatures,nn',
            'newNomenclature.name' => 'required|string|max:191|unique:nomenclatures,name',
            'newNomenclature.supplier_id' => 'nullable|exists:suppliers,id',
            'newNomenclature.brand_id' => 'nullable|exists:brands,id',
            'newNomenclature.category_id' => 'required|exists:categories,id',

        ]);

        $nn = $validatedData['newNomenclature']['nn'];
        $name = $validatedData['newNomenclature']['name'];

        // Проверка дубликата nn
        if (Nomenclature::where('nn', $nn)->exists()) {
            $this->dispatch('nomenclature-nn-duplicate', [
                'nn' => $nn
            ]);
            return;
        }

        // Проверка дубликата name
        if (Nomenclature::where('name', $name)->exists()) {
            $this->dispatch('nomenclature-name-duplicate', [
                'name' => $name
            ]);
            return;
        }

        $validatedData['newNomenclature']['manager_id'] = Auth::id();

        foreach (['supplier_id', 'brand_id'] as $field) {
            $validatedData['newNomenclature'][$field] = $validatedData['newNomenclature'][$field] ?: 1;
        }

        if ($this->image) {
            //$tempPath = $this->image->getRealPath();
            //$tempImg = Storage::disk('public')->get($tempPath);

            $manager = new ImageManager(Driver::class);

            $processedImage = $manager->read($this->image)
                ->resize(1024, null)
                ->toWebp(quality: 60);

            $imagePath = '/images/nomenclatures/' . Auth::id();
            // Генерируем уникальное имя для файла
            $fileName = $imagePath. '/' . uniqid() . '.webp';

            // Сохраняем закодированное изображение в local storage
            Storage::disk('public')->put($fileName, $processedImage);
            //$this->image = Storage::disk('public')->url($fileName);

            $validatedData['newNomenclature']['image'] = $fileName;
        }else{
            $validatedData['newNomenclature']['image'] = '';
        }

        // Создаём запись в БД
        $nomenclature = Nomenclature::create($validatedData['newNomenclature']);

        // Добавляем в локальный массив для отображения
        $this->nomenclatures = Nomenclature::where('manager_id', Auth::id())->get()->toArray();
        $this->dispatch('nomenclature-updated');
        $this->reset('newNomenclature');
        $this->dispatch('showNotification', 'success', 'New nomenclature created successfully');
        $this->WriteActionLog('add', 'nomenclature', $nomenclature->id, $validatedData['newNomenclature']);
    }

    public function clearValidationErrors()
    {
        $this->resetErrorBag(); // внутренний метод уже тут доступен
    }

    public function updatedNomenclature()
    {
        $this->editingNomenclature->update([
            'name' => $this->name,
        ]);

        // Логируем изменения
        $changes = $this->editingNomenclature->getChanges();
        unset($changes['updated_at']); // Убираем техническое поле

        if (!empty($changes)) {
            $nomenclature = NomenclatureVersion::create([
                'nomenclature_id' => $this->editingNomenclature->id,
                'changes' => json_encode($changes),
                'user_id' => auth()->id(),
            ]);
            $this->WriteActionLog('update', 'nomenclature', $this->editingNomenclature->id, $this->editingNomenclature->name);
        }
        $this->dispatch('nomenclature-updated');
    }

    public function updateNomenclature($id, $newName)
    {
        $nomenclature = Nomenclature::find($id);

        if (!$nomenclature) {
            session()->flash('error', 'Номенклатура не найдена.');
            return;
        }

        // Обновляем только имя
        $nomenclature->update(['name' => $newName]);

        // Логируем изменения имени
        NomenclatureVersion::create([
            'nomenclature_id' => $nomenclature->id,
            'changes' => json_encode(['name' => $newName]),
            'user_id' => auth()->id(),
        ]);
        $this->dispatch('nomenclature-updated');

        $this->WriteActionLog('update', 'nomenclature', $nomenclature->id, is_array($newName) ? ($newName['name'] ?? '') : $newName);
        $this->dispatch('showNotification', 'success', 'Название номенклатуры обновлено.');
    }

    public function updateNomenclatureField($id, $field, $value)
    {
        $nomenclature = Nomenclature::find($id);

        if (!$nomenclature || !in_array($field, ['name', 'nn', 'category_id', 'supplier_id'])) {
            return;
        }

        // Обновляем поле
        $nomenclature->update([$field => $value]);

        // Логируем изменение
        NomenclatureVersion::create([
            'nomenclature_id' => $nomenclature->id,
            'changes' => json_encode([$field => $value]),
            'user_id' => auth()->id(),
        ]);

        $this->WriteActionLog('update', 'nomenclature', $nomenclature->id, [
            [
                $field => $value
            ]
        ]);
        $this->dispatch('nomenclature-updated');
        $this->showUpdateNotification('showNotification', 'success', $field);
    }

    public function updateNomenclatureNn($id, $newNn)
    {
        $nomenclature = Nomenclature::find($id);

        if (!$nomenclature) {
            abort(404, 'Номенклатура не найдена.');
        }

        if (Nomenclature::where('nn', $newNn)->where('id', '!=', $id)->exists()) {
            throw ValidationException::withMessages([
                'nn' => 'Номер номенклатуры уже существует.',
            ]);
        }

        $nomenclature->update(['nn' => $newNn]);

        NomenclatureVersion::create([
            'nomenclature_id' => $nomenclature->id,
            'changes' => json_encode(['nn' => $newNn]),
            'user_id' => auth()->id(),
        ]);

        $this->dispatch('nomenclature-updated');
        $this->WriteActionLog('update', 'nomenclature', $nomenclature->id, $nomenclature->name);
        $this->dispatch('showNotification', 'success', 'Номер номенклатуры обновлен.');
    }

    public function bulkUpdateNomenclatures($nomenclatures)
    {
        $logChanges = [];
        $categories = Category::pluck('name', 'id');
        $suppliers  = Supplier::pluck('name', 'id');

        foreach ($nomenclatures as $data) {
            $nomenclature = Nomenclature::find($data['id']);

            if ($nomenclature) {
                $changedFields = [];
                if ($nomenclature->name !== $data['name']) {
                    $changedFields['name'] = $data['name'];
                }
                if ($nomenclature->nn !== $data['nn']) {
                    $changedFields['nn'] = $data['nn'];
                }
                if ($nomenclature->category_id != $data['category_id']) {
                    $changedFields['category'] = $categories[$data['category_id']] ?? 'N/A';
                }
                if ($nomenclature->supplier_id != $data['supplier_id']) {
                    $changedFields['supplier'] = $suppliers[$data['supplier_id']] ?? 'N/A';
                }

                $existingBrandIds = $nomenclature->brands->pluck('id')->sort()->values();
                $newBrandIds = collect($data['brands'] ?? [])->pluck('id')->sort()->values();

                if ($existingBrandIds->toJson() !== $newBrandIds->toJson()) {
                    $brandNames = collect($data['brands'])->pluck('name')->filter()->implode(', ');
                    $changedFields['brands'] = $brandNames !== '' ? $brandNames : '(none)';
                }

                $nomenclature->update([
                    'nn' => $data['nn'],
                    'name' => $data['name'],
                    'category_id' => $data['category_id'],
                    'supplier_id' => $data['supplier_id'],
                ]);

                if (array_key_exists('brands', $data)) {
                    $ids = collect($data['brands'])->pluck('id')->filter()->values();
                    $nomenclature->brands()->sync($ids);
                }

                if (!empty($changedFields)) {
                    $logChanges[] = [
                        'name' => $nomenclature->name,
                        'changes' => $changedFields,
                    ];

                    // Применяем изменения
                    $nomenclature->update($data);
                }
            }
        }

        if (count($logChanges) > 50) {
            $summary = "[Bulk] update: " . count($logChanges) . " номенклатур";

            BulkLog::create([
                'action_type' => 'update',
                'target_type' => 'nomenclature',
                'user_id' => auth()->id(),
                'items' => $logChanges,
                'summary' => $summary,
            ]);
        } else {
            $this->WriteActionLog('update', 'nomenclature', null, $logChanges);
        }

        $this->dispatch('bulk-nomenclature-updated', $nomenclatures);
        $this->showUpdateNotification('showNotification', 'success', null);
    }

    public function archiveNomenclature($id)
    {
        $nomenclature = Nomenclature::findOrFail($id);
        $nomenclature->update([
            'is_archived' => true,
            'archived_at' => now(),
        ]);

        \App\Models\Notification::create([
            'user_id' => auth()->id(),
            'type'    => 'nomenclature_archived',
            'message' => "Номенклатура '{$nomenclature->name}' была архивирована.",
            'payload' => ['nomenclature_id' => $nomenclature->id],
        ]);
        $this->dispatch('notificationAdded')->to('global-notification');

        $this->nomenclatures = collect($this->nomenclatures)
            ->reject(fn ($n) => $n['id'] == $id)
            ->values()
            ->toArray();

        $this->dispatch('nomenclature-updated', $id);
        $this->dispatch('showNotification', 'success', 'Номенклатура заархивирована.');

        $actionType = $nomenclature->is_archived ? 'archive' : 'restore';
        $this->WriteActionLog($actionType, 'nomenclature', $nomenclature->id, [ 'name' => $nomenclature->name ]);
    }

    public function confirmDeleteNomenclature($id)
    {
        $this->idToDelete = $id;
    }

    public function deleteNomenclature()
    {
        if ($this->idToDelete) {
            $nomenclature = Nomenclature::find($this->idToDelete)->first();

            if ($nomenclature->image && Storage::disk('public')->exists($nomenclature->image)) {
                Storage::disk('public')->delete($nomenclature->image);
            }

            Nomenclature::find($this->idToDelete)->delete();
            $this->dispatch('showNotification', 'info', 'Номенклатура удалена!');

            $this->idToDelete = null;
            $this->dispatch('nomenclature-updated');
            $this->WriteActionLog('delete', 'nomenclature', $nomenclature->id, $nomenclature->name);
        }
    }

    public function uploadImage($id)
    {
        $this->validate([
            'nomenclatureImage' => 'required|image|max:5200',
        ]);

        // Получаем номенклатуру
        $nomenclature = Nomenclature::find($id);

        if (!$nomenclature) {
            $this->dispatch('showNotification', 'error', 'Nomenclature not found');
            return;
        }

        // Удаляем старое изображение, если оно есть
        if ($nomenclature->image) {
            Storage::disk('public')->delete($nomenclature->image);
        }

        if ($this->nomenclatureImage)
        {
            $manager = new ImageManager(Driver::class);

            $processedImage = $manager->read($this->nomenclatureImage)
                ->resize(null, null)
                ->toWebp(quality: 60);

            $imagePath = '/images/nomenclatures/' . Auth::id();
            // Генерируем уникальное имя для файла
            $fileName = $imagePath. '/' . uniqid() . '.webp';

            // Сохраняем закодированное изображение в local storage
            Storage::disk('public')->put($fileName, $processedImage);
            $nomenclature->update(['image' => $fileName]);
        }

        $this->dispatch('showNotification', 'success', 'Nomenclature Image updated successfully!');
        //$this->dispatch('image-updated');
        $this->dispatch('nomenclature-image-updated', id: $nomenclature->id, image: $fileName);

        // Сбрасываем состояние
        $this->reset('nomenclatureImage');
    }

    public function openManagerUrlModal($partId)
    {
        $this->selectedId = $partId;
        $nomenclature = Nomenclature::find($partId);

        $data = json_decode($nomenclature->url, true) ?? [];
        $this->managerSupplier = $data['text'] ?? '';
        $this->managerUrl = $data['url'] ?? '';
        $this->managerUrlModalVisible = true;
    }

    public function saveManagerUrl()
    {
        $part = Nomenclature::find($this->selectedId);
        //$part->url = json_encode(['text' => '', 'url' => $this->url]);
        $part->url = json_encode([
            'text' => $this->managerSupplier,
            'url' => $this->managerUrl,
        ]);
        $part->save();

        $this->managerUrlModalVisible = false;
        $this->refreshComponent();
    }

    public function updateNomenclatureBrands($nomenclatureId, $selectedBrands)
    {
        $nomenclature = Nomenclature::find($nomenclatureId);

        if (!$nomenclature) return;

        $oldBrandIds = $nomenclature->brands->pluck('id')->sort()->values();
        $newBrandIds = collect($selectedBrands)->filter()->sort()->values();

        if ($oldBrandIds->toJson() !== $newBrandIds->toJson()) {
            // Получаем имена брендов
            $brandNames = Brand::whereIn('id', $newBrandIds)->pluck('name')->implode(', ');
            $log = [
                'name' => $nomenclature->name,
                'changes' => [
                    'brands' => $brandNames !== '' ? $brandNames : '(отсутствует)'
                ]
            ];

            $this->WriteActionLog(
                'update',
                'nomenclature',
                $nomenclatureId,
                [$log] // универсальный формат
            );
        }

        // Безопасный sync
        $idsToSync = $newBrandIds->all();
        $nomenclature->brands()->sync($idsToSync ?? []);


        $this->updateSelectedBrands($nomenclatureId);

        $this->dispatch('brandsUpdated', $nomenclatureId);
    }

    public function updateSelectedBrands($nomenclatureId)
    {
        $nomenclature = Nomenclature::where('id', $nomenclatureId)->firstOrFail();

        $oldBrandIds = $nomenclature->brands->pluck('id')->sort()->values();
        $newBrandIds = collect($this->selectedBrands)->filter()->sort()->values();

        if ($oldBrandIds->toJson() !== $newBrandIds->toJson()) {
            $brandNames = Brand::whereIn('id', $newBrandIds)->pluck('name')->implode(', ');

            $this->WriteActionLog(
                'update',
                'nomenclature',
                $nomenclatureId,
                [[
                    'name' => $nomenclature->name,
                    'changes' => [
                        'brands' => $brandNames !== '' ? $brandNames : '(отсутствует)',
                    ],
                ]]
            );
        }

        // безопасное обновление брендов
        $nomenclature->brands()->sync($newBrandIds ?? []);


        $this->dispatch('brandsUpdated', $nomenclatureId);
        $this->dispatch('showNotification', 'success', 'Brands updated successfully!');
    }

    public function getUpdatedBrands($nomenclatureId)
    {
        return Nomenclature::find($nomenclatureId)->brands()->pluck('brands.id')->toArray();
    }

    public function getAllNns()
    {
        return Nomenclature::where('manager_id', Auth::id())->pluck('nn')->toArray();
    }

    public function WriteActionLog($actionType, $target_type, $target_id, array $items)
    {
        $count = count($items);
        $description = $count > 1
            ? "[Bulk] {$actionType}: {$count} номенклатур\n"
            : ucfirst($target_type) . " {$actionType}:\n";

        foreach ($items as $item) {
            $changes = collect($item['changes'] ?? [])
                ->map(fn($val, $key) => "$key: \"$val\"")
                ->implode(', ');

            $line = "- " . ($item['name'] ?? 'N/A');

            if ($changes) {
                $line .= " → {$changes}";
            }

            $description .= $line . "\n";
        }

        /*foreach ($items as $item) {
            $changes = collect($item['changes'])
                ->map(fn($val, $key) => "$key: \"$val\"")
                ->implode(', ');

            $description .= "• {$item['name']} → {$changes}\n";
        }*/

        ActionLog::create([
            'action_type' => $actionType,
            'target_type' => $target_type,
            'target_id' => $target_id,
            'description' => trim($description),
            'user_id' => auth()->id(),
        ]);
    }

    public function showUpdateNotification($event, $status, $field)
    {
        $message = match ($field) {
            'name' => 'Название номенклатуры обновлено.',
            'nn' => 'Номер номенклатуры обновлён.',
            'category_id' => 'Категория номенклатуры изменена.',
            'supplier_id' => 'Поставщик номенклатуры изменён.',
            default => 'Номенклатура обновлена.',
        };

        $this->dispatch($event, $status, $message);
    }

    public function render()
    {
        //$nomenclatures = $this->nomenclatures;
        return view('livewire.manager.manager-nomenclatures')->layout('layouts.app');
    }
}
