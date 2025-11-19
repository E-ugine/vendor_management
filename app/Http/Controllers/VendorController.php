<?php

namespace App\Http\Controllers;

use App\Models\Vendor;
use App\Models\VendorDocument;
use App\Http\Requests\StoreVendorRequest;
use App\Http\Requests\UpdateVendorRequest;
use App\Enums\VendorStage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VendorController extends Controller
{
    // List all vendors (for different views based on stage)
    public function index(Request $request)
    {
        // We'll implement stage-specific lists later
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

        // Add documents if provided
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

        // Transition to "With Vendor" stage
        $vendor->transitionTo(
            VendorStage::WITH_VENDOR,
            'submitted',
            'Moved to vendor for completing details',
            Auth::id()
        );

        return redirect()->route('vendors.show', $vendor)
            ->with('success', 'Vendor created successfully! Awaiting vendor to complete details.');
    }

    // Show vendor details
    public function show(Vendor $vendor)
    {
        $vendor->load(['documents', 'history.actor', 'creator']);
        return view('vendors.show', compact('vendor'));
    }

    // Show edit form (Vendor role)
    public function edit(Vendor $vendor)
    {
        return view('vendors.edit', compact('vendor'));
    }

    // Update vendor (Vendor role)
    public function update(UpdateVendorRequest $request, Vendor $vendor)
    {
        $vendor->update($request->only(['name', 'email', 'phone', 'address', 'category']));

        // Add new documents if provided
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

        return redirect()->route('vendors.show', $vendor)
            ->with('success', 'Vendor details updated successfully!');
    }
}