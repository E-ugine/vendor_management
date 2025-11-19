<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vendor_stage_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained()->onDelete('cascade');
            $table->string('stage'); // Which stage this action occurred at
            $table->string('action'); // 'submitted', 'approved', 'rejected'
            $table->text('comment')->nullable();
            $table->foreignId('actor_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('acted_at');
            $table->timestamps();
            
            $table->index('vendor_id');
            $table->index(['vendor_id', 'acted_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vendor_stage_history');
    }
};