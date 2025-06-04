<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
            <div class="mb-6">
                <h1 class="text-2xl font-semibold text-gray-800">Create Category</h1>
            </div>

            <form wire:submit="save">
                <div class="mb-4">
                    <label for="category_title" class="block text-sm font-medium text-gray-700 mb-2">Category Name</label>
                    <input type="text" wire:model="category_title" id="category_title" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                    @error('category_title') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div class="flex justify-between">
                    <a href="{{ route('categories.index') }}" class="px-4 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400">
                        Cancel
                    </a>
                    <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
                        Create Category
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
