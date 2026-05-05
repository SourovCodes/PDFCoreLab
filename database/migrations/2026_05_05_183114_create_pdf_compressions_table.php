<?php

use App\Models\ApiKey;
use App\Models\User;
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
        Schema::create('pdf_compressions', function (Blueprint $table) {
            $table->id();
            $table->ulid('public_id')->unique();
            $table->foreignIdFor(ApiKey::class)->constrained();
            $table->foreignIdFor(User::class)->constrained();

            $table->string('original_filename');
            $table->string('original_mime_type');
            $table->unsignedBigInteger('original_size_bytes');
            $table->string('original_disk');
            $table->string('original_path');
            $table->string('compressed_disk')->nullable();
            $table->string('compressed_path')->nullable();
            $table->unsignedBigInteger('compressed_size_bytes')->nullable();
            $table->string('ghostscript_preset');
            $table->string('status')->index();
            $table->text('failure_message')->nullable();
            $table->timestamp('queued_at')->nullable();
            $table->timestamp('processing_started_at')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamp('failed_at')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pdf_compressions');
    }
};
