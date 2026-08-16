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
        Schema::table('threads', function (Blueprint $table) {
            $table->string('visibility')->default('team')->after('subject');
            $table->string('share_token', 26)->nullable()->unique()->after('visibility');

            $table->index(['board_id', 'visibility', 'bumped_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('threads', function (Blueprint $table) {
            $table->dropIndex(['board_id', 'visibility', 'bumped_at']);
            $table->dropColumn(['visibility', 'share_token']);
        });
    }
};
