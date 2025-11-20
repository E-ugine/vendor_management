<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Vendor Details
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                    {{ session('success') }}
                </div>
            @endif

            <!-- Action Buttons at Top -->
            @if($vendor->current_stage->value === 'with_vendor' && Auth::user()->current_role->value === 'vendor')
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 flex justify-between items-center">
                    <p class="text-sm text-blue-800">
                        <span class="font-semibold">Action Required:</span> Complete vendor details and submit for review
                    </p>
                    <a href="{{ route('vendors.edit', $vendor) }}" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150">
    Edit & Submit
</a>
                </div>
            @endif

            <!-- Review Actions (for Checker, Procurement, Legal, Finance, Directors) -->
@php
    $reviewerRoles = ['checker', 'procurement', 'legal', 'finance', 'director'];
    $currentUserRole = Auth::user()->current_role->value;
    $vendorStage = $vendor->current_stage->value;
    
    // Map stages to roles
    $stageRoleMap = [
        'checker_review' => 'checker',
        'procurement_review' => 'procurement',
        'legal_review' => 'legal',
        'finance_review' => 'finance',
        'directors_review' => 'director',
    ];
    
    $canReview = isset($stageRoleMap[$vendorStage]) 
        && $stageRoleMap[$vendorStage] === $currentUserRole
        && $vendor->canBeActedUpon();
@endphp

@if($canReview)
    <div class="bg-orange-50 border border-orange-200 rounded-lg p-4">
        <p class="text-sm text-orange-800 font-semibold mb-4">
            Review Required: You need to approve or reject this vendor
        </p>
        
        <form method="POST" action="{{ route('vendors.review', $vendor) }}" class="space-y-4" id="reviewForm">
            @csrf
            
            <!-- Comment -->
            <div>
                <label for="comment" class="block text-sm font-medium text-gray-700">Comment (required for rejection)</label>
                <textarea name="comment" id="comment" rows="3" 
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
            </div>
            
            <!-- Action Buttons -->
            <div class="flex gap-3">
                <button type="submit" name="action" value="approve" 
                    class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700">
                    ✓ Approve
                </button>
                <button type="submit" name="action" value="reject" 
                    onclick="return validateReject()"
                    class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700">
                    ✗ Reject
                </button>
            </div>
        </form>
    </div>
    
    <script>
        function validateReject() {
            const comment = document.getElementById('comment').value.trim();
            if (!comment) {
                alert('Please provide a comment explaining why you are rejecting this vendor.');
                return false;
            }
            return confirm('Are you sure you want to reject this vendor? They will need to resubmit.');
        }
    </script>
@endif

            <!-- Vendor Information -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <h3 class="text-2xl font-bold text-gray-800">{{ $vendor->name }}</h3>
                            <p class="text-sm text-gray-500">Created {{ $vendor->created_at->diffForHumans() }}</p>
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
                                <li class="py-3">
                                    <p class="text-sm font-medium text-gray-900">{{ $doc->file_name }}</p>
                                    <p class="text-xs text-gray-500">Uploaded {{ $doc->created_at->diffForHumans() }}</p>
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
                        <div class="space-y-4">
                            @foreach($vendor->history as $history)
                                <div class="border-l-4 border-blue-500 pl-4">
                                    <p class="text-sm font-medium text-gray-900">
                                        <span class="capitalize">{{ $history->action }}</span> at {{ ucfirst(str_replace('_', ' ', $history->stage)) }}
                                    </p>
                                    @if($history->comment)
                                        <p class="text-sm text-gray-600 mt-1">{{ $history->comment }}</p>
                                    @endif
                                    @if($history->actor)
                                        <p class="text-xs text-gray-400 mt-1">by {{ $history->actor->name }}</p>
                                    @endif
                                    <p class="text-xs text-gray-400">{{ $history->acted_at->format('M d, Y H:i') }}</p>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-sm text-gray-500">No history available.</p>
                    @endif
                </div>
            </div>

            <div>
                <a href="{{ route('vendors.index') }}" class="text-sm text-indigo-600 hover:text-indigo-800">
                    ← Back to Vendors List
                </a>
            </div>
        </div>
    </div>
</x-app-layout>