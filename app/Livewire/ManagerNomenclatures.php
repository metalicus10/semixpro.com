<?php

namespace App\Livewire;

use App\Models\ActionLog;
use App\Models\Brand;
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

class ManagerNomenclatures extends Component
{
    use WithFileUploads;

    public $nomenclatures = [], $archived_nomenclatures = [], $selectedNomenclatures = [];
    public $manager_id, $nn, $name, $category_id, $supplier_id, $image, $version, $categories, $brands, $suppliers;
    public $editingNomenclature = null, $idToDelete;
    public bool $showArchived, $managerUrlModalVisible = false;
    public $managerUrl, $selectedId, $managerSupplier;

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
        'url' => '',
        'category_id' => '',
        'supplier_id' => '',
        'brand_id' => '',
        'manager_id' => '',
        'image' => '',
    ];

    public function mount()
    {
        $this->categories = Category::where('manager_id', Auth::id())->get()->toArray();
        $this->suppliers = Supplier::where('manager_id', Auth::id())->get()->toArray();
        $this->brands = Brand::where('manager_id', Auth::id())->get()->toArray();
        $this->nomenclatures = Nomenclature::where('manager_id', Auth::id())
        ->with('category', 'suppliers', 'brands')->get()->toArray();

        $this->archived_nomenclatures = Nomenclature::where('is_archived', true)
            ->where('manager_id', $this->manager_id)
            ->with('category', 'suppliers')->get()->toArray();
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
        $validatedData = $this->validate([
            'newNomenclature.nn' => 'required|string|max:255',
            'newNomenclature.name' => 'required|string|max:255',
            'newNomenclature.category_id' => 'nullable|string|max:255',
            'newNomenclature.supplier_id' => 'nullable|string|max:255',
            'newNomenclature.brand_id' => 'nullable|string|max:255',
            'newNomenclature.image' => 'nullable|image|max:2048',
        ]);

        $validatedData['newNomenclature']['manager_id'] = Auth::id();

        if ($this->image) {
            //$tempPath = $this->image->getRealPath();
            //$tempImg = Storage::disk('public')->get($tempPath);

            $manager = new ImageManager(Driver::class);

            $processedImage = $manager->read($this->image)
                ->resize(1024, 'auto')
                ->toWebp(quality: 60);

            $imagePath = '/images/nomenclatures/' . Auth::id();
            // Генерируем уникальное имя для файла
            $fileName = $imagePath. '/' . uniqid() . '.webp';

            // Сохраняем закодированное изображение в local storage
            Storage::disk('public')->put($fileName, $processedImage);
            //$this->image = Storage::disk('public')->url($fileName);

            $validatedData['newNomenclature']['image'] = $fileName;
        }

        // Создаём запись в БД
        $nomenclature = Nomenclature::create($validatedData['newNomenclature']);

        // Добавляем в локальный массив для отображения
        $this->nomenclatures = Nomenclature::where('manager_id', Auth::id())->get()->toArray();
        $this->reset('newNomenclature');
        $this->dispatch('showNotification', 'success', 'New nomenclature created successfully');
        $this->WriteActionLog('add', 'nomenclature', $nomenclature->id, $nomenclature->name);
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

        $this->WriteActionLog('update', 'nomenclature', $nomenclature->id, $newName);
        $this->dispatch('showNotification', 'success', 'Название номенклатуры обновлено.');
    }

    public function archiveNomenclature($id)
    {
        $nomenclature = Nomenclature::findOrFail($id);
        $nomenclature->update([
            'is_archived' => true,
            'archived_at' => now(),
        ]);

        $this->dispatch('nomenclature-updated');
        $this->nomenclatures = Nomenclature::where('manager_id', Auth::id())->get()->toArray();
        $this->dispatch('showNotification', 'success', 'Номенклатура заархивирована.');

        $actionType = $nomenclature->is_archived ? 'archive' : 'restore';
        $this->WriteActionLog($actionType, 'nomenclature', $nomenclature->id, $nomenclature->name);
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

    public function WriteActionLog($actionType, $target_type, $target_id, $name)
    {
        ActionLog::create([
            'action_type' => $actionType,
            'target_type' => $target_type,
            'target_id' => $target_id,
            'description' => 'Category '.$actionType.': ' . $name,
            'user_id' => auth()->id(),
        ]);
    }

    public function render()
    {
        return view('livewire.manager.manager-nomenclatures')->layout('layouts.app');
    }
}
