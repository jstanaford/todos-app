<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-semibold text-gray-800">Category: {{ $category->category_title }}</h1>
                <div>
                    <a href="{{ route('categories.edit', $category) }}" class="px-4 py-2 bg-yellow-500 text-white rounded hover:bg-yellow-600 mr-2">
                        Edit Category
                    </a>
                    <a href="{{ route('categories.index') }}" class="px-4 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400">
                        Back to Categories
                    </a>
                </div>
            </div>

            <div class="mb-6">
                <h2 class="text-xl font-semibold text-gray-700 mb-4">Todos in this Category</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full bg-white">
                        <thead>
                            <tr>
                                <th class="px-6 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    Title
                                </th>
                                <th class="px-6 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    Due Date
                                </th>
                                <th class="px-6 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    Status
                                </th>
                                <th class="px-6 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($todos as $todo)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap border-b border-gray-200">
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ $todo->todo_title }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap border-b border-gray-200">
                                        <div class="text-sm text-gray-900">
                                            {{ $todo->due_date ? $todo->due_date->format('M d, Y') : 'No due date' }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap border-b border-gray-200">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $todo->complete ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                            {{ $todo->complete ? 'Completed' : 'Pending' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap border-b border-gray-200 text-sm">
                                        <a href="{{ route('todos.show', $todo) }}" class="text-indigo-600 hover:text-indigo-900 mr-3">
                                            View
                                        </a>
                                        <a href="{{ route('todos.edit', $todo) }}" class="text-yellow-600 hover:text-yellow-900 mr-3">
                                            Edit
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-4 whitespace-nowrap border-b border-gray-200 text-sm text-center text-gray-500">
                                        No todos found in this category.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            
            <div class="mt-4">
                {{ $todos->links() }}
            </div>
        </div>
    </div>
</div>
