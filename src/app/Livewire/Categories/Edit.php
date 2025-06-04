<?php

namespace App\Livewire\Categories;

use App\Models\Category;
use Livewire\Component;
use Livewire\Attributes\Rule;

class Edit extends Component
{
    public Category $category;
    
    #[Rule('required|min:3|max:255')]
    public string $category_title = '';

    public function mount(Category $category)
    {
        $this->category = $category;
        $this->category_title = $category->category_title;
    }

    public function save()
    {
        $this->validate();

        $this->category->update([
            'category_title' => $this->category_title,
        ]);

        session()->flash('message', 'Category updated successfully!');
        
        return $this->redirect(route('categories.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.categories.edit');
    }
}
