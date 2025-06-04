<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-semibold text-gray-800">Categories</h1>
                <a href="{{ route('categories.create') }}" class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
                    Create Category
                </a>
            </div>

            @if (session('message'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    {{ session('message') }}
                </div>
            @endif

            <div class="overflow-x-auto">
                <table class="min-w-full bg-white">
                    <thead>
                        <tr>
                            <th class="px-6 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Category
                            </th>
                            <th class="px-6 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($categories as $category)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap border-b border-gray-200">
                                    <div class="text-sm font-medium text-gray-900">
                                        {{ $category->category_title }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap border-b border-gray-200 text-sm">
                                    <a href="{{ route('categories.show', $category) }}" class="text-indigo-600 hover:text-indigo-900 mr-3">
                                        View
                                    </a>
                                    <a href="{{ route('categories.edit', $category) }}" class="text-yellow-600 hover:text-yellow-900">
                                        Edit
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" class="px-6 py-4 whitespace-nowrap border-b border-gray-200 text-sm text-center text-gray-500">
                                    No categories found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $categories->links() }}
            </div>
        </div>
    </div>
</div>
