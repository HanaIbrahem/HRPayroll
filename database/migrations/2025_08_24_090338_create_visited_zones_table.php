<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('visited_zones', function (Blueprint $table) {
             $table->id();
            $table->foreignId('checklist_id')
                ->constrained('checklists')
                ->cascadeOnDelete();

      
            $table->foreignId('zone_id')
                ->constrained('zones')
                ->restrictOnDelete();


            $table->unsignedInteger('zone_count')->default(1);   
            $table->unsignedInteger('repeat_count')->default(1); 
            $table->unsignedInteger('km')->nullable();                        
            $table->unsignedBigInteger('calculated_cost')->default(0); 

            $table->timestamps();

            $table->index(['checklist_id', 'zone_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visited_zones');
    }
};
