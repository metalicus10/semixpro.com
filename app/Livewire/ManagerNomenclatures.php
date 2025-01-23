<?php

namespace App\Livewire;

use App\Models\ActionLog;
use App\Models\NomenclatureVersion;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use Livewire\Component;
use App\Models\Nomenclature;
use Illuminate\Support\Facades\Auth;

class ManagerNomenclatures extends Component
{
    public $nomenclatures = [], $archived_nomenclatures = [], $selectedNomenclatures = [];
    public $manager_id, $name, $category_id, $supplier_id, $image, $version, $categories, $brands, $suppliers;
    public $editingNomenclature = null, $idToDelete;
    public bool $showArchived = false;

    // Массив для добавления новой номенклатуры
    public $newNomenclature = [
        'sku' => '',
        'name' => '',
        'category' => '',
        'supplier' => '',
        'url' => ['url' => '', 'text' => ''],
        'manager_id' => '',
    ];

    public function mount()
    {
        $this->nomenclatures = Nomenclature::where('manager_id', Auth::id())
        ->with('category', 'supplier')->get()->toArray();

        $this->archived_nomenclatures = Nomenclature::where('is_archived', true)
            ->where('manager_id', $this->manager_id)
            ->with('category', 'supplier')->get()->toArray();
    }

    public function addNomenclature()
    {
        $validatedData = $this->validate([
            'newNomenclature.sku' => 'required|string|max:255|unique:nomenclatures,sku',
            'newNomenclature.name' => 'required|string|max:255',
            'newNomenclature.category' => 'nullable|string|max:255',
            'newNomenclature.supplier' => 'nullable|string|max:255',
            'newNomenclature.url.url' => 'nullable|url',
            'newNomenclature.url.text' => 'nullable|string|max:255',
            'newNomenclature.image' => 'nullable|image|max:2048',
        ]);

        $validatedData['newNomenclature']['manager_id'] = Auth::id();

        if ($this->image) {

            //$tempPath = $this->image->getRealPath();
            //$tempImg = Storage::disk('public')->get($tempPath);

            $manager = new ImageManager(Driver::class);

            $processedImage = $manager->read($this->image)
                ->resize(null, null)
                ->toWebp(quality: 60);

            $imagePath = 'nomenclatures/' . Auth::id();
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

    public function archiveNomenclature($id)
    {
        $nomenclature = Nomenclature::findOrFail($id);
        $nomenclature->is_archived = true;
        $nomenclature->archived_at = now();
        $nomenclature->save();

        $this->dispatch('showNotification', 'success', 'Номенклатура заархивирована.');

        $this->WriteActionLog('archive', 'nomenclature', $nomenclature->id, $nomenclature->name);
    }

    public function restoreNomenclature($id)
    {
        $nomenclature = Nomenclature::findOrFail($id);
        $nomenclature->is_archived = false;
        $nomenclature->archived_at = null;
        $nomenclature->save();

        $this->dispatch('showNotification', 'success', 'Номенклатура восстановлена успешно!');

        $this->WriteActionLog('restore', 'nomenclature', $nomenclature->id, $nomenclature->name);
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
            $this->dispatch('nomenclatureUpdated');
            $this->WriteActionLog('delete', 'nomenclature', $nomenclature->id, $nomenclature->name);
        }
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
        return view('livewire.manager-nomenclatures');
    }
}
