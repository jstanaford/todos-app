<?php

namespace App\Livewire\Todos;

use App\Models\Todo;
use App\Models\Category;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
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
    
    // New category modal properties
    public $showCategoryModal = false;
    public $new_category_title = '';
    
    protected function rules()
    {
        return [
            'todo_title' => ['required', 'string', 'max:255'],
            'details' => ['nullable', 'string'],
            'due_date' => ['required', 'date'],
            'recurring' => ['boolean'],
            'recurring_schedule' => $this->recurring ? ['required', 'string', 'in:daily,weekly,monthly,yearly,custom'] : ['nullable', 'string'],
            'category_id' => ['nullable', 'exists:categories,id'],
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
        if ($propertyName === 'new_category_title') {
            $this->validateOnly('new_category_title');
        } else {
            $this->validateOnly($propertyName);
        
            // If recurring is toggled off, reset the recurring_schedule
            if ($propertyName === 'recurring' && !$this->recurring) {
                $this->recurring_schedule = '';
            }
        }
    }
    
    public function openCategoryModal()
    {
        $this->showCategoryModal = true;
    }
    
    public function closeCategoryModal()
    {
        $this->showCategoryModal = false;
        $this->new_category_title = '';
        $this->resetValidation('new_category_title');
    }
    
    public function saveCategory()
    {
        $this->validate([
            'new_category_title' => 'required|string|min:3|max:255',
        ]);
        
        // Create new category
        $category = Category::create([
            'category_title' => $this->new_category_title,
        ]);
        
        // Set the newly created category as selected
        $this->category_id = $category->id;
        
        $this->closeCategoryModal();
        
        // Show success notification
        session()->flash('category_success', 'Category created successfully!');
    }
    
    public function update()
    {
        try {
            // Log the incoming data for debugging
            Log::info('Todo update attempt', [
                'todo_id' => $this->todo->id,
                'data' => [
                    'todo_title' => $this->todo_title,
                    'details' => $this->details,
                    'due_date' => $this->due_date,
                    'recurring' => $this->recurring,
                    'recurring_schedule' => $this->recurring_schedule,
                    'category_id' => $this->category_id,
                ]
            ]);

            // Explicitly validate each field
            $rules = $this->rules();
            $validated = $this->validate($rules);
            
            Log::info('Validation passed', ['validated_data' => $validated]);

            // Update the todo with validated data
            $updateData = [
                'todo_title' => $validated['todo_title'],
                'details' => $validated['details'],
                'due_date' => $validated['due_date'],
                'recurring' => $validated['recurring'],
                'recurring_schedule' => $validated['recurring'] ? $validated['recurring_schedule'] : null,
                'category_id' => $validated['category_id'] ?: null,
            ];

            Log::info('Attempting to update todo', ['update_data' => $updateData]);
            
            $updated = $this->todo->update($updateData);
            
            if (!$updated) {
                Log::error('Todo update failed - model update returned false');
                session()->flash('error', 'Failed to update todo. Please try again.');
                return;
            }

            Log::info('Todo updated successfully');
            session()->flash('success', 'Todo updated successfully.');
            
            return redirect()->route('todos.index');
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation failed', [
                'errors' => $e->errors(),
                'data' => $this->only(['todo_title', 'details', 'due_date', 'recurring', 'recurring_schedule', 'category_id'])
            ]);
            throw $e;
        } catch (\Exception $e) {
            Log::error('Todo update failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            session()->flash('error', 'Error updating todo: ' . $e->getMessage());
        }
    }
    
    public function render()
    {
        return view('livewire.todos.edit', [
            'categories' => Category::orderBy('category_title')->get(),
        ]);
    }
}
