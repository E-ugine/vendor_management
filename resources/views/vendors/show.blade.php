<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Vendor Details
            </h2>
            @if($vendor->canBeActedUpon() && $vendor->current_stage->value === 'with_vendor' && Auth::user()->current_role->value === 'vendor')
                <a href="{{ route('vendors.edit', $vendor) }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                    Edit & Submit
                </a>
            @endif
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <!-- Success Message -->
            @if (session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                    {{ session('success') }}
                </div>
            @endif

            <!-- Vendor Information -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <h3 class="text-2xl font-bold text-gray-800">{{ $vendor->name }}</h3>
                            <p class="text-sm text-gray-500">Created {{ $vendor->created_at->diffForHumans() }} by {{ $vendor->creator->name ?? 'System' }}</p>
                        </div>
                        <div class="text-right">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                                {{ $vendor->status->value === 'approved' ? 'bg-green-100 text-green-800' : '' }}
                                {{ $vendor->status->value === 'rejected' ? 'bg-red-100 text-red-800' : '' }}
                                {{ $vendor->status->value === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}">
                                {{ ucfirst($vendor->status->value) }}
                            </span>
                            <p class="text-xs text-gray-500 mt-1">{{ $vendor->current_stage->label() }}</p>
                        </div>
                    </div>

                    <dl class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-6">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Email</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $vendor->email }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Phone</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $vendor->phone }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Category</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $vendor->category }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Address</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $vendor->address }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            <!-- Next Action Indicator -->
            @if($vendor->nextActionRole())
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <p class="text-sm text-blue-800">
                        <span class="font-semibold">Next Action:</span> Waiting for {{ $vendor->nextActionRole() }} to act
                    </p>
                </div>
            @endif

            <!-- Documents -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h4 class="text-lg font-semibold mb-4">Documents</h4>
                    @if($vendor->documents->count() > 0)
                        <ul class="divide-y divide-gray-200">
                            @foreach($vendor->documents as $doc)
                                <li class="py-3 flex justify-between items-center">
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">{{ $doc->file_name }}</p>
                                        <p class="text-xs text-gray-500">Uploaded {{ $doc->created_at->diffForHumans() }}</p>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-sm text-gray-500">No documents uploaded yet.</p>
                    @endif
                </div>
            </div>

            <!-- History -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h4 class="text-lg font-semibold mb-4">History</h4>
                    @if($vendor->history->count() > 0)
                        <div class="flow-root">
                            <ul class="-mb-8">
                                @foreach($vendor->history as $index => $history)
                                    <li>
                                        <div class="relative pb-8">
                                            @if(!$loop->last)
                                                <span class="absolute left-4 top-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                                            @endif
                                            <div class="relative flex space-x-3">
                                                <div>
                                                    <span class="h-8 w-8 rounded-full flex items-center justify-center ring-8 ring-white
                                                        {{ $history->action === 'approved' ? 'bg-green-500' : '' }}
                                                        {{ $history->action === 'rejected' ? 'bg-red-500' : '' }}
                                                        {{ $history->action === 'submitted' || $history->action === 'created' ? 'bg-blue-500' : '' }}">
                                                        <svg class="h-5 w-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                                        </svg>
                                                    </span>
                                                </div>
                                                <div class="flex min-w-0 flex-1 justify-between space-x-4 pt-1.5">
                                                    <div>
                                                        <p class="text-sm text-gray-900">
                                                            <span class="font-medium capitalize">{{ $history->action }}</span> at {{ ucfirst(str_replace('_', ' ', $history->stage)) }}
                                                        </p>
                                                        @if($history->comment)
                                                            <p class="mt-1 text-sm text-gray-500">{{ $history->comment }}</p>
                                                        @endif
                                                        @if($history->actor)
                                                            <p class="mt-1 text-xs text-gray-400">by {{ $history->actor->name }}</p>
                                                        @endif
                                                    </div>
                                                    <div class="whitespace-nowrap text-right text-sm text-gray-500">
                                                        <time>{{ $history->acted_at->format('M d, Y H:i') }}</time>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @else
                        <p class="text-sm text-gray-500">No history available.</p>
                    @endif
                </div>
            </div>

            <!-- Back Button -->
            <div>
                <a href="{{ route('vendors.index') }}" class="text-sm text-indigo-600 hover:text-indigo-800">
                    ← Back to Vendors List
                </a>
            </div>
        </div>
    </div>
</x-app-layout>