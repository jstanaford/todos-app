<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Todo;
use App\Models\TodoInstance;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class GenerateRecurringTodoInstances extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'todos:generate-instances {--days=365 : Number of days to generate instances for}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate instances for recurring todos up to a year in advance';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $daysToGenerate = (int) $this->option('days');
        $endDate = Carbon::now()->addDays($daysToGenerate);
        
        $this->info("Generating recurring todo instances for the next {$daysToGenerate} days");
        Log::info("Starting generation of recurring todo instances for the next {$daysToGenerate} days");
        
        // Get all recurring todos
        $recurringTodos = Todo::where('recurring', true)->get();
        $instancesCreated = 0;
        
        foreach ($recurringTodos as $todo) {
            $instancesCreated += $this->generateInstancesForTodo($todo, $endDate);
        }
        
        $this->info("Created {$instancesCreated} todo instances successfully");
        Log::info("Recurring todo instances generated: {$instancesCreated}");
    }
    
    /**
     * Generate instances for a specific todo up to the end date
     *
     * @param Todo $todo
     * @param Carbon $endDate
     * @return int Number of instances created
     */
    private function generateInstancesForTodo(Todo $todo, Carbon $endDate): int
    {
        // Get the last instance date or use the todo's due date if no instances exist
        $lastInstance = $todo->instances()->latest('due_date')->first();
        $lastDate = $lastInstance ? Carbon::parse($lastInstance->due_date) : Carbon::parse($todo->due_date);
        
        // Get the current date
        $currentDate = Carbon::now();
        
        // Initialize counter
        $instancesCreated = 0;
        
        // If the last date is in the future but less than our end date, we start from there
        // Otherwise, we start from today
        $startDate = $lastDate->gt($currentDate) ? $lastDate : $currentDate;
        
        // Parse recurring schedule
        switch ($todo->recurring_schedule) {
            case 'daily':
                return $this->generateDailyInstances($todo, $startDate, $endDate);
            
            case 'weekly':
                return $this->generateWeeklyInstances($todo, $startDate, $endDate);
            
            case 'monthly':
                return $this->generateMonthlyInstances($todo, $startDate, $endDate);
            
            case 'yearly':
                return $this->generateYearlyInstances($todo, $startDate, $endDate);
                
            default:
                // For any custom schedule, we'll interpret it as days
                if (is_numeric($todo->recurring_schedule)) {
                    $days = (int) $todo->recurring_schedule;
                    return $this->generateCustomInstances($todo, $startDate, $endDate, $days);
                }
                
                return 0;
        }
    }
    
    /**
     * Generate daily instances
     */
    private function generateDailyInstances(Todo $todo, Carbon $startDate, Carbon $endDate): int
    {
        $count = 0;
        $currentDate = $startDate->copy();
        
        while ($currentDate->lt($endDate)) {
            $currentDate = $currentDate->addDay();
            
            // Create instance if it doesn't exist for this date
            if (!$this->instanceExistsForDate($todo, $currentDate)) {
                $this->createInstance($todo, $currentDate);
                $count++;
            }
        }
        
        return $count;
    }
    
    /**
     * Generate weekly instances
     */
    private function generateWeeklyInstances(Todo $todo, Carbon $startDate, Carbon $endDate): int
    {
        $count = 0;
        $currentDate = $startDate->copy();
        
        while ($currentDate->lt($endDate)) {
            $currentDate = $currentDate->addWeek();
            
            // Create instance if it doesn't exist for this date
            if (!$this->instanceExistsForDate($todo, $currentDate)) {
                $this->createInstance($todo, $currentDate);
                $count++;
            }
        }
        
        return $count;
    }
    
    /**
     * Generate monthly instances
     */
    private function generateMonthlyInstances(Todo $todo, Carbon $startDate, Carbon $endDate): int
    {
        $count = 0;
        $currentDate = $startDate->copy();
        
        while ($currentDate->lt($endDate)) {
            $currentDate = $currentDate->addMonth();
            
            // Create instance if it doesn't exist for this date
            if (!$this->instanceExistsForDate($todo, $currentDate)) {
                $this->createInstance($todo, $currentDate);
                $count++;
            }
        }
        
        return $count;
    }
    
    /**
     * Generate yearly instances
     */
    private function generateYearlyInstances(Todo $todo, Carbon $startDate, Carbon $endDate): int
    {
        $count = 0;
        $currentDate = $startDate->copy();
        
        while ($currentDate->lt($endDate)) {
            $currentDate = $currentDate->addYear();
            
            // Create instance if it doesn't exist for this date
            if (!$this->instanceExistsForDate($todo, $currentDate)) {
                $this->createInstance($todo, $currentDate);
                $count++;
            }
        }
        
        return $count;
    }
    
    /**
     * Generate custom interval instances
     */
    private function generateCustomInstances(Todo $todo, Carbon $startDate, Carbon $endDate, int $days): int
    {
        $count = 0;
        $currentDate = $startDate->copy();
        
        while ($currentDate->lt($endDate)) {
            $currentDate = $currentDate->addDays($days);
            
            // Create instance if it doesn't exist for this date
            if (!$this->instanceExistsForDate($todo, $currentDate)) {
                $this->createInstance($todo, $currentDate);
                $count++;
            }
        }
        
        return $count;
    }
    
    /**
     * Check if an instance already exists for a specific date
     */
    private function instanceExistsForDate(Todo $todo, Carbon $date): bool
    {
        return $todo->instances()
            ->whereDate('due_date', $date->toDateString())
            ->exists();
    }
    
    /**
     * Create a new instance for a todo
     */
    private function createInstance(Todo $todo, Carbon $dueDate): void
    {
        TodoInstance::create([
            'todo_id' => $todo->id,
            'due_date' => $dueDate,
            'complete' => false,
        ]);
    }
}
