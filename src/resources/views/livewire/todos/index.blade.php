<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('My Todos') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Date Picker Section -->
            <div class="mb-6 bg-gradient-to-r from-blue-500 to-teal-400 rounded-xl shadow-xl overflow-hidden">
                <div class="px-6 py-8 sm:p-10 sm:pb-6">
                    <div class="flex items-center justify-between flex-wrap">
                        <div class="w-full flex-1 flex flex-col items-center sm:items-start">
                            <h2 class="text-xl leading-8 font-extrabold text-white sm:text-2xl">
                                <span class="bg-white/20 px-3 py-1 rounded-md backdrop-blur-sm">{{ __('Tasks for') }} {{ date('F j, Y', strtotime($selectedDate)) }}</span>
                            </h2>
                            <p class="mt-2 text-base text-white sm:text-lg">
                                {{ __('Manage your tasks scheduled for today') }}
                            </p>
                        </div>
                        
                        <div x-data="{ open: false }" class="mt-4 sm:mt-0 relative">
                            <button @click="open = !open" type="button" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-teal-700 bg-white hover:bg-blue-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                {{ __('Change Date') }}
                            </button>
                            
                            <div x-show="open" @click.away="open = false" class="origin-top-right absolute right-0 mt-2 w-56 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 z-10" style="display: none;">
                                <div class="p-3">
                                    <label for="selectedDate" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Select date') }}</label>
                                    <input 
                                        type="date" 
                                        id="selectedDate" 
                                        wire:model.live="selectedDate" 
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                                        @change="open = false"
                                    >
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="px-6 pt-6 pb-8 bg-blue-50 sm:p-10 sm:pt-6">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-2">
                            <button wire:click="$set('selectedDate', '{{ date('Y-m-d', strtotime('-1 day', strtotime($selectedDate))) }}')" class="inline-flex items-center p-2 border border-transparent rounded-full shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                                </svg>
                            </button>
                            <span class="text-sm font-semibold text-blue-800 bg-blue-100 px-2 py-1 rounded-md">{{ date('M j', strtotime('-1 day', strtotime($selectedDate))) }}</span>
                        </div>
                        
                        <button wire:click="$set('selectedDate', '{{ date('Y-m-d') }}')" class="inline-flex items-center px-3 py-1 border border-transparent text-sm font-medium rounded-md text-teal-700 bg-teal-100 hover:bg-teal-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-500">
                            {{ __('Today') }}
                        </button>
                        
                        <div class="flex items-center space-x-2">
                            <span class="text-sm font-semibold text-blue-800 bg-blue-100 px-2 py-1 rounded-md">{{ date('M j', strtotime('+1 day', strtotime($selectedDate))) }}</span>
                            <button wire:click="$set('selectedDate', '{{ date('Y-m-d', strtotime('+1 day', strtotime($selectedDate))) }}')" class="inline-flex items-center p-2 border border-transparent rounded-full shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Content Section -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <!-- Guest Login Message -->
                    @if (Auth::user() && Auth::user()->email === 'guest@example.com')
                        <div class="mb-4 p-4 bg-blue-50 border-l-4 border-blue-500 text-blue-700 rounded-md flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <p>{{ __('Logged in as guest. Your changes will not be saved permanently.') }}</p>
                        </div>
                    @endif
                    
                    <!-- Success Message -->
                    @if (session()->has('success'))
                        <div class="mb-4 p-4 bg-green-100 border-l-4 border-green-500 text-green-700 rounded-md">
                            <p>{{ session('success') }}</p>
                        </div>
                    @endif
                    
                    <!-- Filters -->
                    <div class="mb-6 flex flex-col md:flex-row justify-between items-center gap-4">
                        <div class="flex flex-col md:flex-row gap-4 w-full md:w-auto">
                            <select wire:model.live="categoryId" class="rounded-md shadow-sm border-gray-300 focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                                <option value="">{{ __('All Categories') }}</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->category_title }}</option>
                                @endforeach
                            </select>
                            
                            <select wire:model.live="completed" class="rounded-md shadow-sm border-gray-300 focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                                <option value="">{{ __('All Status') }}</option>
                                <option value="false">{{ __('Incomplete') }}</option>
                                <option value="true">{{ __('Complete') }}</option>
                            </select>
                        </div>
                        
                        <a href="{{ route('todos.create') }}" class="inline-flex items-center px-4 py-2 bg-teal-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-teal-700 active:bg-teal-800 focus:outline-none focus:border-teal-800 focus:shadow-outline-gray disabled:opacity-25 transition ease-in-out duration-150">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
                            </svg>
                            {{ __('Add New Todo') }}
                        </a>
                    </div>
                    
                    <!-- Todos List -->
                    <div class="space-y-4">
                        @forelse($todos as $todo)
                            <div class="bg-white rounded-lg border p-4 flex items-center justify-between transition-all hover:shadow-md">
                                <div class="flex items-center space-x-4">
                                    <button wire:click="toggleComplete({{ $todo->id }})" class="flex-shrink-0">
                                        @if($todo->complete)
                                            <div class="w-8 h-8 rounded-full bg-teal-100 flex items-center justify-center text-teal-500">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                                </svg>
                                            </div>
                                        @else
                                            <div class="w-8 h-8 rounded-full border-2 border-gray-300 hover:border-blue-500"></div>
                                        @endif
                                    </button>
                                    
                                    <div>
                                        <a href="{{ route('todos.show', $todo) }}" class="text-lg font-medium text-gray-900 hover:text-blue-600 block">
                                            {{ $todo->todo_title }}
                                        </a>
                                        <div class="flex items-center mt-1 space-x-2">
                                            @if($todo->category)
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                    {{ $todo->category->category_title }}
                                                </span>
                                            @endif
                                            
                                            <span class="text-sm text-gray-500">
                                                {{ $todo->due_date->format('g:i A') }}
                                            </span>
                                            
                                            @if($todo->recurring)
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-teal-100 text-teal-800">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                                    </svg>
                                                    Recurring
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="flex items-center space-x-2">
                                    <a href="{{ route('todos.edit', $todo) }}" class="text-blue-600 hover:text-blue-900" title="{{ __('Edit') }}">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                            <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                                        </svg>
                                    </a>
                                    <button 
                                        x-data
                                        x-on:click="if (confirm('{{ __('Are you sure you want to delete this todo?') }}')) { $wire.delete({{ $todo->id }}) }"
                                        class="text-red-600 hover:text-red-900" 
                                        title="{{ __('Delete') }}"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-12">
                                <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                                </svg>
                                <h3 class="mt-2 text-sm font-medium text-gray-900">{{ __('No todos') }}</h3>
                                <p class="mt-1 text-sm text-gray-500">{{ __('Get started by creating a new todo.') }}</p>
                                <div class="mt-6">
                                    <a href="{{ route('todos.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-teal-600 hover:bg-teal-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-500">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="-ml-1 mr-2 h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
                                        </svg>
                                        {{ __('New Todo') }}
                                    </a>
                                </div>
                            </div>
                        @endforelse
                    </div>
                    
                    <!-- Pagination -->
                    <div class="mt-6">
                        {{ $todos->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
