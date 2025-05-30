<?php

namespace App\Livewire\Todos;

use App\Models\Todo;
use App\Models\Category;
use App\Models\TodoInstance;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class Create extends Component
{
    public $todo_title = '';
    public $details = '';
    public $due_date = '';
    public $recurring = false;
    public $recurring_schedule = '';
    public $category_id = '';
    
    protected function rules()
    {
        return [
            'todo_title' => 'required|string|max:255',
            'details' => 'nullable|string',
            'due_date' => 'required|date',
            'recurring' => 'boolean',
            'recurring_schedule' => $this->recurring ? 'required|string' : 'nullable|string',
            'category_id' => 'nullable|exists:categories,id',
        ];
    }
    
    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);
        
        // If recurring is toggled off, reset the recurring_schedule
        if ($propertyName === 'recurring' && !$this->recurring) {
            $this->recurring_schedule = '';
        }
    }
    
    public function mount()
    {
        // Set default due date to tomorrow
        $this->due_date = now()->addDay()->format('Y-m-d');
    }
    
    public function save()
    {
        $this->validate();
        
        try {
            // Create the todo
            $todo = Todo::create([
                'user_id' => Auth::id(),
                'todo_title' => $this->todo_title,
                'details' => $this->details,
                'due_date' => $this->due_date,
                'recurring' => $this->recurring,
                'recurring_schedule' => $this->recurring ? $this->recurring_schedule : null,
                'category_id' => $this->category_id ?: null,
                'complete' => false,
            ]);
            
            // If todo is recurring, create the first instance
            if ($todo->recurring && $todo->recurring_schedule) {
                TodoInstance::create([
                    'todo_id' => $todo->id,
                    'due_date' => $todo->due_date,
                    'complete' => false,
                ]);
            }
            
            session()->flash('success', 'Todo created successfully.');
            
            $this->redirectRoute('todos.index');
        } catch (\Exception $e) {
            session()->flash('error', 'Error creating todo: ' . $e->getMessage());
        }
    }
    
    public function render()
    {
        return view('livewire.todos.create', [
            'categories' => Category::all(),
        ]);
    }
}
