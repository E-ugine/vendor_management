<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Enums\VendorStage;
use App\Enums\VendorStatus;

class Vendor extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email', 
        'phone',
        'address',
        'category',
        'current_stage',
        'status',
        'created_by',
    ];

    protected $casts = [
    'current_stage' => VendorStage::class,
    'status' => VendorStatus::class,
];

protected $attributes = [
    'current_stage' => 'new',
    'status' => 'pending',
];

    // Relationships
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function documents()
    {
        return $this->hasMany(VendorDocument::class);
    }

    public function history()
    {
        return $this->hasMany(VendorStageHistory::class)->orderBy('acted_at', 'desc');
    }

    // Core business logic: Stage transitions
    public function transitionTo(VendorStage $newStage, string $action, ?string $comment = null, ?int $actorId = null)
{
    DB::transaction(function () use ($newStage, $action, $comment, $actorId) {
        // Update vendor stage
        $this->current_stage = $newStage;
        
        // Update status based on action
        if ($action === 'rejected') {
            $this->status = VendorStatus::REJECTED;
        } elseif ($newStage === VendorStage::APPROVED) {
            $this->status = VendorStatus::APPROVED;
        } elseif ($action === 'submitted' && $this->status === VendorStatus::REJECTED) {
            // Reset to pending if resubmitting after rejection
            $this->status = VendorStatus::PENDING;
        }
        
        $this->save();

        // Log to history
        VendorStageHistory::create([
            'vendor_id' => $this->id,
            'stage' => $newStage->value,
            'action' => $action,
            'comment' => $comment,
            'actor_id' => $actorId,
            'acted_at' => now(),
        ]);
    });
}

    // Helper: Can this vendor be acted upon?
    public function canBeActedUpon(): bool
    {
        return $this->status === VendorStatus::PENDING;
    }

    // Helper: What's the next action label?
    public function nextActionRole(): ?string
    {
        if ($this->status !== VendorStatus::PENDING) {
            return null;
        }

        return match($this->current_stage) {
            VendorStage::NEW => 'Initiator',
            VendorStage::WITH_VENDOR => 'Vendor',
            VendorStage::CHECKER_REVIEW => 'Checker',
            VendorStage::PROCUREMENT_REVIEW => 'Procurement',
            VendorStage::LEGAL_REVIEW => 'Legal',
            VendorStage::FINANCE_REVIEW => 'Finance',
            VendorStage::DIRECTORS_REVIEW => 'Director',
            VendorStage::APPROVED => null,
        };
    }
}