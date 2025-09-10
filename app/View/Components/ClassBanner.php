<?php

namespace App\View\Components;

use Closure;
use Domain\Character\Enums\ClassEnum;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class ClassBanner extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct(
        public string $className = '',
        public string $size = 'sm',
    ) {
        //
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        $class_data = ClassEnum::from($this->className);
        $icons = $class_data->getDomains();
        $top_color = $icons[0]->getColor();
        $bottom_color = $icons[1]->getColor();

        return view('components.class-banner', [
            'top_icon' => $icons[0]->value,
            'bottom_icon' => $icons[1]->value,
            'top_color' => $top_color,
            'bottom_color' => $bottom_color,
            'size' => $this->size,
            'name' => $class_data->value,
        ]);
    }
}
