<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\VendorStage;
use App\Enums\VendorStatus;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vendors', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('phone');
            $table->text('address');
            $table->string('category'); // Could be supplier, contractor, etc.
            
            // Workflow state
            $table->string('current_stage')->default(VendorStage::NEW->value);
            $table->string('status')->default(VendorStatus::PENDING->value);
            
            // Audit: who created this vendor
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            
            $table->timestamps();
            
            $table->index('current_stage');
            $table->index('status');
            $table->index('category');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vendors');
    }
};