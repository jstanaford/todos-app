<?php

namespace App\Livewire\Categories;

use App\Models\Category;
use Livewire\Component;
use Livewire\Attributes\Rule;

class Create extends Component
{
    #[Rule('required|min:3|max:255')]
    public string $category_title = '';

    public function save()
    {
        $this->validate();

        Category::create([
            'category_title' => $this->category_title,
        ]);

        session()->flash('message', 'Category created successfully!');
        
        return $this->redirect(route('categories.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.categories.create');
    }
}
