<?php

namespace App\Livewire;

use Livewire\Component;

class TabsScrollLeft extends Component
{
    public $scrollPosition = 0;
    public $canScrollLeft = false;

    public function mount()
    {
        $this->updateScrollState();
    }

    public function updateScrollState()
    {
        $this->dispatch('check-scroll-visibility');
    }

    public function setScrollState($canScrollLeft)
    {
        $this->canScrollLeft = $canScrollLeft;
    }

    public function scrollLeft()
    {
        $this->scrollPosition -= 100;
        $this->dispatch('scroll-updated', $this->scrollPosition);
    }

    public function scrollToStart()
    {
        $this->scrollPosition = 0;
        $this->dispatch('scroll-updated', $this->scrollPosition);
    }

    public function render()
    {
        return view('livewire.tabs-scroll-left');
    }
}
