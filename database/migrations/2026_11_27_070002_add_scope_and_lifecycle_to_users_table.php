<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('owner_type', 16)->default('national')->after('password'); // national, rbda, state
            $table->unsignedBigInteger('owner_id')->nullable()->after('owner_type');
            $table->string('status', 16)->default('active')->after('owner_id'); // invited, active, disabled
            $table->timestamp('invited_at')->nullable()->after('status');
            $table->timestamp('activated_at')->nullable()->after('invited_at');
            $table->timestamp('disabled_at')->nullable()->after('activated_at');
            $table->timestamp('last_login_at')->nullable()->after('disabled_at');
            $table->timestamp('last_access_review_at')->nullable()->after('last_login_at');

            $table->index(['owner_type', 'owner_id']);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['owner_type', 'owner_id']);
            $table->dropColumn([
                'owner_type',
                'owner_id',
                'status',
                'invited_at',
                'activated_at',
                'disabled_at',
                'last_login_at',
                'last_access_review_at',
            ]);
        });
    }
};
