<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('indicator_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('code_prefix', 16)->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('indicator_subgroups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('indicator_categories')->cascadeOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['category_id', 'slug']);
        });

        Schema::create('indicators', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('indicator_categories')->cascadeOnDelete();
            $table->foreignId('subgroup_id')->nullable()->constrained('indicator_subgroups')->nullOnDelete();
            $table->string('code', 32)->unique();
            $table->string('list_number', 32)->nullable();
            $table->string('level', 32)->nullable(); // PDO, IR, Output, KPI
            $table->text('name');
            $table->string('unit', 64)->nullable();
            $table->string('value_type', 24)->default('number'); // number, percentage, yes_no, milestone, text
            $table->string('polarity', 16)->default('higher'); // higher, lower
            $table->boolean('is_cumulative')->default(false);
            $table->string('frequency', 32)->default('Quarterly');
            $table->string('source_system', 64)->nullable();
            $table->string('data_mode', 24)->default('Manual'); // Auto, Semi, Register, Manual
            $table->string('owner_role', 64)->nullable();
            $table->decimal('baseline_value', 16, 4)->nullable();
            $table->foreignId('parent_indicator_id')->nullable()->constrained('indicators')->nullOnDelete();
            $table->string('shared_group_key', 64)->nullable()->index();
            $table->json('disaggregation_config')->nullable();
            $table->text('reference_notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('indicator_targets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('indicator_id')->constrained('indicators')->cascadeOnDelete();
            $table->unsignedSmallInteger('year');
            $table->decimal('target_value', 16, 4)->nullable();
            $table->timestamps();

            $table->unique(['indicator_id', 'year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('indicator_targets');
        Schema::dropIfExists('indicators');
        Schema::dropIfExists('indicator_subgroups');
        Schema::dropIfExists('indicator_categories');
    }
};
