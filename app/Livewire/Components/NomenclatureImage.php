<?php

namespace App\Livewire\Components;

use App\Models\Nomenclature;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use Livewire\Component;
use Livewire\WithFileUploads;

class NomenclatureImage extends Component
{
    use WithFileUploads;

    public $nomenclature, $nomenclatureId, $imgUrl, $nomenclatureImage, $fullImage;
    public $showImageModal = false;

    public function openImageModal($nomenclatureId)
    {
        $this->nomenclatureId = $nomenclatureId;
        $this->showImageModal = true;
    }

    public function closeImageModal()
    {
        $this->reset(['showImageModal', 'nomenclatureImage']);
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

        $this->closeImageModal();

        $this->dispatch('showNotification', 'success', 'Nomenclature Image updated successfully!');
        $this->dispatch('image-updated', ['nomenclatureId' => $id]);

        // Сбрасываем состояние
        $this->reset('nomenclatureImage');
    }

    public function showImage($imageUrl)
    {
        $this->fullImage = $imageUrl;
    }

    public function closeImage()
    {
        $this->fullImage = null;
    }
    public function render()
    {
        return view('livewire.manager.components.nomenclature-image');
    }
}
