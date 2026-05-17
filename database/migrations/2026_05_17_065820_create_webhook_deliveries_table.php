<?php

use App\Models\ApiKey;
use App\Models\PdfCompression;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('webhook_deliveries', function (Blueprint $table): void {
            $table->id();
            $table->ulid('public_id')->unique();
            $table->foreignIdFor(ApiKey::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(PdfCompression::class)->nullable()->constrained()->nullOnDelete();
            $table->string('event');
            $table->string('url', 2048);
            $table->json('payload');
            $table->unsignedTinyInteger('attempt')->default(0);
            $table->string('status')->default('pending')->index();
            $table->unsignedSmallInteger('response_status')->nullable();
            $table->text('response_body')->nullable();
            $table->text('error')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('webhook_deliveries');
    }
};
