<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('report_type', 24); // quarterly, annual, category
            $table->foreignId('category_id')->nullable()->constrained('indicator_categories')->nullOnDelete();
            $table->unsignedSmallInteger('year');
            $table->unsignedTinyInteger('quarter')->nullable();
            $table->string('owner_type', 16)->default('national');
            $table->unsignedBigInteger('owner_id')->nullable();
            $table->boolean('has_draft_watermark')->default(false);
            $table->string('status', 24)->default('draft');
            $table->json('narrative_sections')->nullable();
            $table->json('snapshot_data')->nullable();
            $table->foreignId('generated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            $table->index(['owner_type', 'owner_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
