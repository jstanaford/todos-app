<?php

namespace App\Livewire\Todos;

use App\Models\Todo;
use App\Models\Category;
use App\Models\TodoInstance;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Carbon\Carbon;

#[Layout('layouts.app')]
class Index extends Component
{
    use WithPagination;

    public $categoryId = '';
    public $completed = '';
    public $selectedDate;
    public $showDatePicker = false;
    
    protected $queryString = [
        'categoryId' => ['except' => ''],
        'completed' => ['except' => ''],
        'selectedDate' => ['except' => ''],
    ];
    
    public function mount()
    {
        // Set default selected date to today
        $this->selectedDate = $this->selectedDate ?: Carbon::today()->format('Y-m-d');
    }
    
    public function updatingCategoryId()
    {
        $this->resetPage();
    }
    
    public function updatingCompleted()
    {
        $this->resetPage();
    }
    
    public function updatingSelectedDate()
    {
        $this->resetPage();
    }
    
    public function toggleComplete(Todo $todo)
    {
        // Check if the todo belongs to the authenticated user
        if ($todo->user_id !== Auth::id()) {
            return;
        }
        
        $todo->update([
            'complete' => !$todo->complete,
        ]);
        
        session()->flash('success', $todo->complete ? 'Todo marked as complete.' : 'Todo marked as incomplete.');
    }
    
    public function delete(Todo $todo)
    {
        // Check if the todo belongs to the authenticated user
        if ($todo->user_id !== Auth::id()) {
            return;
        }
        
        $todo->delete();
        
        session()->flash('success', 'Todo deleted successfully.');
    }

    public function render()
    {
        // Ensure selectedDate is a Carbon instance
        $carbonDate = Carbon::parse($this->selectedDate);
        
        $query = Todo::query()->with('category')
            ->where('user_id', Auth::id())
            ->where(function ($query) use ($carbonDate) {
                // Get todos due on the selected date
                $query->whereDate('due_date', $carbonDate)
                    // Or get recurring todos with instances on the selected date
                    ->orWhereHas('instances', function ($q) use ($carbonDate) {
                        $q->whereDate('due_date', $carbonDate);
                    });
            });
        
        // Filter by category if provided
        if ($this->categoryId) {
            $query->where('category_id', $this->categoryId);
        }
        
        // Filter by completion status if provided
        if ($this->completed !== '') {
            $query->where('complete', $this->completed === 'true');
        }
        
        $todos = $query->latest()->paginate(10);
        $categories = Category::all();
        
        return view('livewire.todos.index', [
            'todos' => $todos,
            'categories' => $categories,
            'selectedDate' => $carbonDate,
        ]);
    }
}
