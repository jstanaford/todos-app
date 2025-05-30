<?php

namespace App\Livewire\Todos;

use App\Models\Todo;
use App\Models\Category;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class Edit extends Component
{
    public $todo;
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
    
    public function mount(Todo $todo)
    {
        $this->todo = $todo;
        
        // Check if the todo belongs to the authenticated user
        if ($this->todo->user_id !== Auth::id()) {
            session()->flash('error', 'You are not authorized to edit this todo.');
            $this->redirectRoute('todos.index');
            return;
        }
        
        // Fill the form with todo data
        $this->todo_title = $this->todo->todo_title;
        $this->details = $this->todo->details;
        $this->due_date = $this->todo->due_date->format('Y-m-d');
        $this->recurring = $this->todo->recurring;
        $this->recurring_schedule = $this->todo->recurring_schedule;
        $this->category_id = $this->todo->category_id;
    }
    
    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);
        
        // If recurring is toggled off, reset the recurring_schedule
        if ($propertyName === 'recurring' && !$this->recurring) {
            $this->recurring_schedule = '';
        }
    }
    
    public function update()
    {
        $this->validate();
        
        try {
            // Update the todo
            $this->todo->update([
                'todo_title' => $this->todo_title,
                'details' => $this->details,
                'due_date' => $this->due_date,
                'recurring' => $this->recurring,
                'recurring_schedule' => $this->recurring ? $this->recurring_schedule : null,
                'category_id' => $this->category_id ?: null,
            ]);
            
            session()->flash('success', 'Todo updated successfully.');
            
            return $this->redirectRoute('todos.index');
        } catch (\Exception $e) {
            session()->flash('error', 'Error updating todo: ' . $e->getMessage());
        }
    }
    
    public function render()
    {
        return view('livewire.todos.edit', [
            'categories' => Category::all(),
        ]);
    }
}
