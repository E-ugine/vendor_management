<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Vendor Details') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <!-- Info Banner -->
                    <div class="mb-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <p class="text-sm text-blue-800">
                            <span class="font-semibold">Current Stage:</span> {{ $vendor->current_stage->label() }}
                        </p>
                        <p class="text-sm text-blue-700 mt-1">
                            Complete any missing information and click "Submit for Review" when ready.
                        </p>
                    </div>

                    <form method="POST" action="{{ route('vendors.update', $vendor) }}">
                        @csrf
                        @method('PATCH')

                        <!-- Vendor Name -->
                        <div class="mb-4">
                            <label for="name" class="block text-sm font-medium text-gray-700">Vendor Name *</label>
                            <input type="text" name="name" id="name" value="{{ old('name', $vendor->name) }}" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @error('name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Email -->
                        <div class="mb-4">
                            <label for="email" class="block text-sm font-medium text-gray-700">Email *</label>
                            <input type="email" name="email" id="email" value="{{ old('email', $vendor->email) }}" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @error('email')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Phone -->
                        <div class="mb-4">
                            <label for="phone" class="block text-sm font-medium text-gray-700">Phone *</label>
                            <input type="text" name="phone" id="phone" value="{{ old('phone', $vendor->phone) }}" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @error('phone')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Address -->
                        <div class="mb-4">
                            <label for="address" class="block text-sm font-medium text-gray-700">Address *</label>
                            <textarea name="address" id="address" rows="3" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('address', $vendor->address) }}</textarea>
                            @error('address')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Category -->
                        <div class="mb-4">
                            <label for="category" class="block text-sm font-medium text-gray-700">Category *</label>
                            <select name="category" id="category" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">Select Category</option>
                                <option value="Supplier" {{ old('category', $vendor->category) == 'Supplier' ? 'selected' : '' }}>Supplier</option>
                                <option value="Service Provider" {{ old('category', $vendor->category) == 'Service Provider' ? 'selected' : '' }}>Service Provider</option>
                                <option value="Contractor" {{ old('category', $vendor->category) == 'Contractor' ? 'selected' : '' }}>Contractor</option>
                            </select>
                            @error('category')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Existing Documents -->
                        @if($vendor->documents->count() > 0)
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Existing Documents</label>
                                <ul class="text-sm text-gray-600 space-y-1">
                                    @foreach($vendor->documents as $doc)
                                        <li>✓ {{ $doc->file_name }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <!-- Add More Documents -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Add More Documents (Optional)</label>
                            <div id="documents-container">
                                <input type="text" name="documents[]" placeholder="Document name (e.g., Tax Certificate.pdf)"
                                    class="mb-2 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>
                            <button type="button" onclick="addDocumentField()" class="text-sm text-indigo-600 hover:text-indigo-800">
                                + Add Another Document
                            </button>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex items-center justify-end gap-4 mt-6">
                            <a href="{{ route('vendors.show', $vendor) }}" class="text-sm text-gray-600 hover:text-gray-800">Cancel</a>
                            <button type="submit" name="action" value="save" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                Save Changes
                            </button>
                            <button type="submit" name="action" value="submit" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                Submit for Review
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function addDocumentField() {
            const container = document.getElementById('documents-container');
            const input = document.createElement('input');
            input.type = 'text';
            input.name = 'documents[]';
            input.placeholder = 'Document name (e.g., Certificate.pdf)';
            input.className = 'mb-2 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500';
            container.appendChild(input);
        }
    </script>
</x-app-layout>