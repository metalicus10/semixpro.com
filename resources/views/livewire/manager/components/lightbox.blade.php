<div class="lightbox fixed inset-0 z-50 bg-black bg-opacity-75 flex items-center justify-center"
     x-data="{ lightboxOpen: false, imgSrc: '' }"
     x-show="lightboxOpen"
     x-transition
     @lightbox.window="lightboxOpen = true; imgSrc = $event.detail;"
     style="display: none;">

    <!-- Фон для закрытия -->
    <div class="absolute inset-0 bg-black opacity-75" @click="lightboxOpen = false"></div>

    <!-- Контейнер для изображения -->
    <div class="lightbox-container relative z-10" @click.stop>
        <!-- Полное изображение -->
        <img :src="imgSrc" class="object-contain max-w-full max-h-full">
    </div>
</div>
