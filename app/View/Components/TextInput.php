<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class TextInput extends Component
{
    /**
     * Create the component instance.
     */
    public function __construct(
        public ?string $value = null,
        public bool $disabled = false
    ) {
    }

    /**
     * Get the view / contents that represents the component.
     */
    public function render(): View
    {
        return view('components.text-input');
    }
}
