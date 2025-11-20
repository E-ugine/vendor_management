<?php

namespace App\Http\Controllers;

use App\Models\Vendor;
use App\Models\VendorDocument;
use App\Http\Requests\StoreVendorRequest;
use App\Http\Requests\UpdateVendorRequest;
use App\Enums\VendorStage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Enums\VendorStatus;

class VendorController extends Controller
{
    // List all vendors (for different views based on stage)
    public function index(Request $request)
    {
        $vendors = Vendor::with('creator')->latest()->paginate(15);
        return view('vendors.index', compact('vendors'));
    }

    // Show create form (Initiator)
    public function create()
    {
        return view('vendors.create');
    }

    // Store new vendor (Initiator)
    public function store(StoreVendorRequest $request)
    {
        $vendor = Vendor::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
            'category' => $request->category,
            'created_by' => auth()->id(),
        ]);

        if ($request->has('documents')) {
            foreach ($request->documents as $doc) {
                if (!empty($doc)) {
                    VendorDocument::create([
                        'vendor_id' => $vendor->id,
                        'file_name' => $doc,
                        'uploaded_by' => auth()->id(),
                    ]);
                }
            }
        }

        // Log initial creation to history
        $vendor->history()->create([
            'stage' => VendorStage::NEW->value,
            'action' => 'created',
            'comment' => 'Vendor created by initiator',
            'actor_id' => auth()->id(),
            'acted_at' => now(),
        ]);

        $vendor->transitionTo(
            VendorStage::WITH_VENDOR,
            'submitted',
            'Moved to vendor for completing details',
            Auth::id()
        );

        return redirect()->route('vendors.show', $vendor)
            ->with('success', 'Vendor created successfully! Awaiting vendor to complete details.');
    }

    public function show(Vendor $vendor)
    {
        $vendor->load(['documents', 'history.actor', 'creator']);
        return view('vendors.show', compact('vendor'));
    }

    public function edit(Vendor $vendor)
    {
        return view('vendors.edit', compact('vendor'));
    }

    public function update(UpdateVendorRequest $request, Vendor $vendor)
{
    $vendor->update($request->only(['name', 'email', 'phone', 'address', 'category']));

    if ($request->has('documents')) {
        foreach ($request->documents as $doc) {
            if (!empty($doc)) {
                VendorDocument::create([
                    'vendor_id' => $vendor->id,
                    'file_name' => $doc,
                    'uploaded_by' => auth()->id(),
                ]);
            }
        }
    }

    // Check if vendor is submitting for review
    if ($request->input('action') === 'submit') {
        // Transition to Checker Review
        $vendor->transitionTo(
            VendorStage::CHECKER_REVIEW,
            'submitted',
            'Vendor submitted for checker review',
            auth()->id()
        );

        return redirect()->route('vendors.show', $vendor)
            ->with('success', 'Vendor submitted for review! Waiting for Checker to act.');
    }

    return redirect()->route('vendors.show', $vendor)
        ->with('success', 'Vendor details updated successfully!');
}

public function review(Request $request, Vendor $vendor)
{
    $request->validate([
        'action' => ['required', 'in:approve,reject'],
        'comment' => ['required_if:action,reject', 'nullable', 'string', 'max:500'],
    ]);

    $action = $request->input('action');
    $comment = $request->input('comment');

    if ($action === 'approve') {
        $nextStage = $vendor->current_stage->nextStage();
        
        if ($nextStage) {
            $vendor->transitionTo(
                $nextStage,
                'approved',
                $comment ?? 'Approved by ' . Auth::user()->current_role->label(),
                Auth::id()
            );
            
            $message = $nextStage === VendorStage::APPROVED 
                ? 'Vendor approved! Moved to Approved Vendors list.' 
                : 'Vendor approved! Moved to next stage.';
        } else {
            $message = 'Vendor is already at final stage.';
        }
    } else {
        $vendor->transitionTo(
            VendorStage::WITH_VENDOR,
            'rejected',
            $comment,
            Auth::id()
        );
        
        $message = 'Vendor rejected. Sent back to vendor for corrections.';
    }

    return redirect()->route('vendors.show', $vendor)->with('success', $message);
}

// Approved vendors list (masterlist)
public function approved(Request $request)
{
    $query = Vendor::where('status', VendorStatus::APPROVED)
        ->with('creator');
    
    if ($request->filled('search')) {
        $query->where('name', 'LIKE', '%' . $request->search . '%');
    }
    
    if ($request->filled('category')) {
        $query->where('category', $request->category);
    }
    
    $vendors = $query->latest('updated_at')->paginate(15);
    
    return view('vendors.approved', compact('vendors'));
}
}