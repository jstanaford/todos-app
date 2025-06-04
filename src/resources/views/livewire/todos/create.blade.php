<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Create Todo') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <!-- Category Success Message -->
                    @if (session('category_success'))
                        <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                            {{ session('category_success') }}
                        </div>
                    @endif
                
                    <form wire:submit.prevent="save">
                        <!-- Title -->
                        <div class="mb-4">
                            <x-input-label for="todo_title" :value="__('Title')" />
                            <x-text-input wire:model="todo_title" id="todo_title" class="block mt-1 w-full" type="text" name="todo_title" required autofocus />
                            <x-input-error :messages="$errors->get('todo_title')" class="mt-2" />
                        </div>
                        
                        <!-- Details -->
                        <div class="mb-4">
                            <x-input-label for="details" :value="__('Details')" />
                            <textarea wire:model="details" id="details" name="details" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" rows="3"></textarea>
                            <x-input-error :messages="$errors->get('details')" class="mt-2" />
                        </div>
                        
                        <!-- Due Date -->
                        <div class="mb-4">
                            <x-input-label for="due_date" :value="__('Due Date')" />
                            <x-text-input wire:model="due_date" id="due_date" class="block mt-1 w-full" type="date" name="due_date" required />
                            <x-input-error :messages="$errors->get('due_date')" class="mt-2" />
                        </div>
                        
                        <!-- Recurring -->
                        <div class="mb-4">
                            <div class="flex items-center">
                                <input wire:model.live="recurring" id="recurring" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                <x-input-label for="recurring" :value="__('Recurring')" class="ml-2" />
                            </div>
                            <x-input-error :messages="$errors->get('recurring')" class="mt-2" />
                        </div>
                        
                        <!-- Recurring Schedule -->
                        @if($recurring)
                        <div class="mb-4">
                            <x-input-label for="recurring_schedule" :value="__('Recurring Schedule')" />
                            <select wire:model="recurring_schedule" id="recurring_schedule" name="recurring_schedule" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                <option value="">Select Schedule</option>
                                <option value="daily">Daily</option>
                                <option value="weekly">Weekly</option>
                                <option value="monthly">Monthly</option>
                                <option value="yearly">Yearly</option>
                                <option value="custom">Custom</option>
                            </select>
                            <x-input-error :messages="$errors->get('recurring_schedule')" class="mt-2" />
                        </div>
                        @endif
                        
                        <!-- Category with Create New Button -->
                        <div class="mb-4">
                            <div class="flex justify-between items-center">
                                <x-input-label for="category_id" :value="__('Category')" />
                                <button type="button" wire:click="openCategoryModal" class="inline-flex items-center px-2 py-1 bg-green-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-600 active:bg-green-700 focus:outline-none focus:border-green-700 focus:shadow-outline-green disabled:opacity-25 transition ease-in-out duration-150">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                    </svg>
                                    New Category
                                </button>
                            </div>
                            <select wire:model="category_id" id="category_id" name="category_id" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                <option value="">Select Category</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->category_title }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('category_id')" class="mt-2" />
                        </div>
                        
                        <div class="flex items-center justify-end mt-4">
                            <a href="{{ route('todos.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300 active:bg-gray-400 focus:outline-none focus:border-gray-500 focus:shadow-outline-gray disabled:opacity-25 transition ease-in-out duration-150 mr-2">
                                Cancel
                            </a>
                            
                            <x-primary-button type="submit">
                                {{ __('Create Todo') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Create Category Modal -->
    <div x-data="{ show: false }" 
         x-init="$watch('$wire.showCategoryModal', value => show = value)"
         x-show="show"
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        
        <!-- Modal Backdrop -->
        <div class="fixed inset-0 transform transition-all">
            <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
        </div>
        
        <!-- Modal Content -->
        <div class="relative bg-white rounded-lg mx-auto mt-20 max-w-md p-6 shadow-xl transform transition-all sm:w-full sm:max-w-md">
            <div class="absolute top-0 right-0 pt-4 pr-4">
                <button type="button" wire:click="closeCategoryModal" class="text-gray-400 hover:text-gray-500">
                    <span class="sr-only">Close</span>
                    <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            
            <h3 class="text-lg font-medium text-gray-900 mb-4">Create New Category</h3>
            
            <form wire:submit.prevent="saveCategory">
                <div class="mb-4">
                    <x-input-label for="new_category_title" :value="__('Category Name')" />
                    <x-text-input wire:model.live="new_category_title" id="new_category_title" class="block mt-1 w-full" type="text" name="new_category_title" required autofocus />
                    <x-input-error :messages="$errors->get('new_category_title')" class="mt-2" />
                </div>
                
                <div class="flex justify-end mt-4">
                    <button type="button" wire:click="closeCategoryModal" class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300 active:bg-gray-400 focus:outline-none focus:border-gray-500 focus:shadow-outline-gray disabled:opacity-25 transition ease-in-out duration-150 mr-2">
                        Cancel
                    </button>
                    
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-600 active:bg-blue-700 focus:outline-none focus:border-blue-700 focus:shadow-outline-blue disabled:opacity-25 transition ease-in-out duration-150">
                        Create Category
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <style>
        [x-cloak] { display: none !important; }
    </style>
</div>
