<?php

namespace App\Livewire\Categories;

use App\Models\Category;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public function render()
    {
        return view('livewire.categories.index', [
            'categories' => Category::orderBy('category_title')->paginate(10),
        ]);
    }
}
