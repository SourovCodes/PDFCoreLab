<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('api_keys', function (Blueprint $table): void {
            $table->string('webhook_url', 2048)->nullable()->after('is_active');
            $table->text('webhook_secret')->nullable()->after('webhook_url');
        });
    }

    public function down(): void
    {
        Schema::table('api_keys', function (Blueprint $table): void {
            $table->dropColumn(['webhook_url', 'webhook_secret']);
        });
    }
};
