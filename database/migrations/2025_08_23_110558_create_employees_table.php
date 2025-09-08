<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\{Department,User};
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
           
            $table->id();
            $table->string('first_name', 30);
            $table->string('last_name', 30);
            // FKs
            $table->foreignIdFor(Department::class)
                  ->constrained()
                  ->cascadeOnUpdate()
                  ->restrictOnDelete();

            $table->foreignIdFor(User::class)
                  ->constrained()
                  ->cascadeOnUpdate()
                  ->restrictOnDelete();

            $table->string('position', 50);

            // Unique code per employee like fingerprint
            $table->string('code', 30)->unique();

            $table->boolean('is_active')->default(true);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
