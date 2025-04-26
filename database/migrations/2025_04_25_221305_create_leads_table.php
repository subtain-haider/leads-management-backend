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
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->index();
            
            $table->foreignId('personal_phone_country_id')->nullable()->constrained('countries');
            $table->string('personal_phone')->nullable();
            
            $table->text('description')->nullable();
            $table->text('address')->nullable();
            
            $table->foreignId('business_phone_country_id')->nullable()->constrained('countries');
            $table->string('business_phone')->nullable();
            
            $table->foreignId('home_phone_country_id')->nullable()->constrained('countries');
            $table->string('home_phone')->nullable();
            
            $table->foreignId('nationality_id')->nullable()->constrained('countries');
            $table->foreignId('residence_country_id')->nullable()->constrained('countries');
            $table->date('dob')->nullable()->index();
            $table->enum('gender', ['male', 'female', 'other', 'prefer_not_to_say'])->nullable()->index();
            $table->foreignId('status_id')->constrained('lead_statuses')->index();
            $table->softDeletes(); 
            $table->timestamps(); 
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            
            $table->index(['first_name', 'last_name']);
            $table->index(['status_id', 'created_at']);
            $table->index(['nationality_id', 'residence_country_id']);
            $table->index('personal_phone');
            $table->index('created_by');
            $table->index('updated_by');
        });
    }

    /**
     * Reverse the migration.
     */
    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};