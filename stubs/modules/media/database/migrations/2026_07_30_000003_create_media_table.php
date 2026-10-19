<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('disk');                 // local | s3 | r2
            $table->string('path');                 // storage path relative to disk
            $table->string('thumbnail_path')->nullable();
            $table->string('original_name');
            $table->string('mime_type');
            $table->unsignedBigInteger('size');     // bytes
            $table->string('collection')->nullable(); // e.g. avatars, exports, documents
            $table->timestamps();

            $table->index(['user_id', 'collection']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media');
    }
};
