<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migration.
     */
    public function up(): void
    {
        Schema::create('activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->constrained()->onDelete('cascade');
            $table->foreignId('activity_type_id')->constrained('activity_types');
            $table->text('details')->nullable();
            $table->timestamp('activity_date');
            $table->timestamps();
            $table->foreignId('created_by')->constrained('users');
            
            $table->index('lead_id');
            $table->index(['lead_id', 'activity_date']);
            $table->index(['activity_type_id', 'activity_date']);
            $table->index('created_by');
        });
    }

    /**
     * Reverse the migration.
     */
    public function down(): void
    {
        Schema::dropIfExists('activities');
    }
};