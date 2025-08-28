<?php

namespace App\Livewire;

use Livewire\Component;

class TiptapTest extends Component
{
    public string $content = '<h1>Welcome to TipTap!</h1><p>This is a <strong>rich text editor</strong> built with TipTap and integrated with <em>Livewire</em>.</p><ul><li>Try the formatting buttons</li><li>Create lists like this one</li><li>Add headings and quotes</li></ul><blockquote><p>This is a quote block. Perfect for campaign lore!</p></blockquote>';

    public function save()
    {
        $this->dispatch('content-saved', content: $this->content);
    }

    public function render()
    {
        return view('livewire.tiptap-test')
            ->layout('components.layout.default');
    }
}
