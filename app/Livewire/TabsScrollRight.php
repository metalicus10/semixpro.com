<?php

namespace App\Livewire;

use Livewire\Component;

class TabsScrollRight extends Component
{
    public $scrollPosition = 0;
    public $canScrollRight = false;

    public function mount()
    {
        $this->updateScrollState();
    }

    public function updateScrollState()
    {
        $this->dispatch('check-scroll-visibility');
    }

    public function setScrollState($canScrollRight)
    {
        $this->canScrollRight = $canScrollRight;
    }

    public function scrollRight()
    {
        $this->scrollPosition += 100;
        $this->dispatch('scroll-updated', $this->scrollPosition);
    }

    public function scrollToEnd()
    {
        $this->scrollPosition = 9999;
        $this->dispatch('scroll-updated', $this->scrollPosition);
    }

    public function render()
    {
        return view('livewire.tabs-scroll-right');
    }
}
