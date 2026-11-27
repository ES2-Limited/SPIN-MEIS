<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('states', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code', 16)->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('rbdas', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code', 16)->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('river_basins', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code', 16)->unique();
            $table->foreignId('rbda_id')->nullable()->constrained('rbdas')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('schemes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code', 32)->unique();
            $table->foreignId('state_id')->constrained('states')->cascadeOnDelete();
            $table->foreignId('rbda_id')->constrained('rbdas')->cascadeOnDelete();
            $table->foreignId('river_basin_id')->nullable()->constrained('river_basins')->nullOnDelete();
            $table->string('delivery_model', 16); // model_1 or model_2
            $table->string('owner_type', 16); // rbda or state (computed from delivery_model)
            $table->unsignedBigInteger('owner_id');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['owner_type', 'owner_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('schemes');
        Schema::dropIfExists('river_basins');
        Schema::dropIfExists('rbdas');
        Schema::dropIfExists('states');
    }
};
