<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('procurement_packages', function (Blueprint $table) {
            $table->id();
            $table->string('package_reference', 64)->unique();
            $table->string('title');
            $table->string('owner_type', 16)->default('national');
            $table->unsignedBigInteger('owner_id')->nullable();
            $table->date('planned_date')->nullable();
            $table->date('actual_date')->nullable();
            $table->string('status', 32)->default('planned');
            $table->timestamps();

            $table->index(['owner_type', 'owner_id']);
        });

        Schema::create('awpb_activities', function (Blueprint $table) {
            $table->id();
            $table->string('activity_code', 32)->unique();
            $table->string('component', 64);
            $table->string('title');
            $table->string('owner_type', 16)->default('national');
            $table->unsignedBigInteger('owner_id')->nullable();
            $table->foreignId('indicator_id')->nullable()->constrained('indicators')->nullOnDelete();
            $table->decimal('budget_amount', 16, 2)->default(0);
            $table->decimal('expenditure_to_date', 16, 2)->default(0);
            $table->unsignedSmallInteger('year')->default(2025);
            $table->timestamps();

            $table->index(['owner_type', 'owner_id']);
        });

        Schema::create('impact_indicators', function (Blueprint $table) {
            $table->id();
            $table->string('dimension', 64);
            $table->string('round', 16); // baseline_2025, midline_2027, endline_2029
            $table->decimal('treatment_value', 16, 4)->nullable();
            $table->decimal('comparison_value', 16, 4)->nullable();
            $table->unsignedInteger('sample_size')->nullable();
            $table->string('confidence_interval', 64)->nullable();
            $table->timestamps();

            $table->unique(['dimension', 'round']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('impact_indicators');
        Schema::dropIfExists('awpb_activities');
        Schema::dropIfExists('procurement_packages');
    }
};
