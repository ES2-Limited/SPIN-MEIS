<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('indicator_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('indicator_id')->constrained('indicators')->cascadeOnDelete();
            $table->foreignId('scheme_id')->nullable()->constrained('schemes')->nullOnDelete();
            $table->string('owner_type', 16); // national, rbda, state
            $table->unsignedBigInteger('owner_id')->nullable();
            $table->unsignedSmallInteger('year');
            $table->unsignedTinyInteger('quarter'); // 1..4
            $table->decimal('numerator', 16, 4)->nullable();
            $table->decimal('denominator', 16, 4)->nullable();
            $table->decimal('value', 16, 4)->nullable();
            $table->json('disaggregation')->nullable();
            $table->text('comment')->nullable();
            $table->string('evidence_path')->nullable();
            $table->string('milestone_stage', 64)->nullable();
            $table->text('narrative')->nullable();
            $table->string('channel', 16)->default('manual'); // manual, api, upload
            $table->string('source', 64)->default('manual_entry');
            $table->string('rule_version', 32)->default('v1');
            $table->string('client_generated_id', 64)->nullable()->unique();
            $table->string('status', 24)->default('draft'); // draft, submitted, verified, approved, reopen_requested
            $table->foreignId('submitted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('submitted_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('reopen_reason')->nullable();
            $table->foreignId('reopen_requested_by')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('has_conflict')->default(false);
            $table->timestamps();

            $table->index(['owner_type', 'owner_id']);
            $table->index(['indicator_id', 'year', 'quarter']);
        });

        Schema::create('register_entries', function (Blueprint $table) {
            $table->id();
            $table->string('register_type', 64); // communication_activity, media_sentiment, inclusion, drill, committee, case_study, community_dashboard, sensitisation, focal_person, training
            $table->string('owner_type', 16);
            $table->unsignedBigInteger('owner_id')->nullable();
            $table->date('activity_date');
            $table->string('title');
            $table->json('payload')->nullable();
            $table->string('evidence_path')->nullable();
            $table->boolean('is_bridge')->default(false);
            $table->date('bridge_sunset_at')->nullable();
            $table->string('client_generated_id', 64)->nullable()->unique();
            $table->string('status', 24)->default('draft');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['owner_type', 'owner_id']);
            $table->index(['register_type', 'activity_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('register_entries');
        Schema::dropIfExists('indicator_values');
    }
};
