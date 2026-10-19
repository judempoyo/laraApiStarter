<?php

declare(strict_types=1);

use App\Enums\ImportStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('imports', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('resource');               // e.g. "users", "user_preferences"
            $table->string('status')->default(ImportStatus::PENDING->value); // pending | processing | completed | failed
            $table->foreignId('media_id')->nullable()->constrained('media')->nullOnDelete();
            $table->boolean('dry_run')->default(false);
            $table->integer('total_rows')->default(0);
            $table->integer('processed_rows')->default(0);
            $table->integer('successful_rows')->default(0);
            $table->integer('failed_rows')->default(0);
            $table->json('errors')->nullable();       // structured validation errors per row
            $table->text('error_message')->nullable(); // general system exception message if FAILED
            $table->timestamps();

            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('imports');
    }
};
