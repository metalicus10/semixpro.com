<?php

namespace App\Livewire\Manager;

use App\Models\Nomenclature;
use App\Models\Part;
use Livewire\Component;

class NomenclatureArchive extends Component
{
    public $archivedNomenclatures = [];
    public $hasArchived = false;
    public $selectedNomenclature = null;
    public $relatedParts = [];
    public $showArchiveModal = false;

    protected $listeners = ['nomenclature-updated' => 'loadArchivedNomenclatures'];

    public function mount()
    {
        $this->loadArchivedNomenclatures();
    }

    public function loadArchivedNomenclatures()
    {
        $this->archivedNomenclatures = Nomenclature::where('is_archived', true)->get()->toArray();
    }

    public function restoreNomenclature($id)
    {
        $nomenclature = Nomenclature::findOrFail($id);
        $nomenclature->update(['is_archived' => false, 'archived_at' => null]);

        // Получаем зависимые запчасти
        $this->relatedParts = Part::where('nomenclature_id', $id)->get();

        // Обновляем список архивных номенклатур
        $this->loadArchivedNomenclatures();

        $archivedNomenclatures = collect($this->archivedNomenclatures);

        // Если архив пуст, закрываем модальное окно
        if ($archivedNomenclatures->isEmpty()) {
            $this->showArchiveModal = false;
        }

        // Диспетчер события для обновления в родительском компоненте
        $this->dispatch('nomenclature-updated');
        $this->dispatch('nomenclature-restore');
    }

    public function openModal()
    {
        $this->showArchiveModal = true;
    }

    public function closeModal()
    {
        $this->showArchiveModal = false;
    }

    public function render()
    {
        return view('livewire.manager.nomenclature-archive');
    }
}
