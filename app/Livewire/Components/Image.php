<?php

namespace App\Livewire\Components;

use App\Models\Part;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Imagick\Driver;
use Intervention\Image\ImageManager;
use Livewire\Component;
use Livewire\WithFileUploads;

class Image extends Component
{
    use WithFileUploads;

    public $part, $partId, $imgUrl, $newImage, $selectedPartId, $fullImage;
    public $showImageModal = false;

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

        // Получаем запчасть
        $part = Part::find($this->partId);

        if (!$part) {
            $this->dispatch('showNotification', 'error', 'Part not found');
            return;
        }

        // Удаляем старое изображение, если оно есть
        if ($part->image) {
            Storage::disk('public')->delete($part->image);
        }

        if ($this->newImage)
        {
            $manager = new ImageManager(Driver::class);

            $processedImage = $manager->read($this->newImage)
                ->resize(null, null)
                ->toWebp(quality: 60);

            $imagePath = '/images/parts/' . Auth::id();
            // Генерируем уникальное имя для файла
            $fileName = $imagePath. '/' . uniqid() . '.webp';

            // Сохраняем закодированное изображение в local storage
            Storage::disk('public')->put($fileName, $processedImage);
        }

        // Обновляем модель
        $part->update(['image' => $this->imgUrl]);
        $this->closeImageModal();

        $this->dispatch('showNotification', 'success', 'Image updated successfully!');
        $this->dispatch('imageUpdated', ['partId' => $this->partId]);

        // Сбрасываем состояние
        $this->reset('newImage');
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
        return view('livewire.manager.components.image');
    }
}
