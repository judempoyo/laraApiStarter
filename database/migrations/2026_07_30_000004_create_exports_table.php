<?php

declare(strict_types=1);

use App\Enums\ExportFormat;
use App\Enums\ExportStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exports', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('resource');               // e.g. "user_preferences", "notifications"
            $table->string('format')->default(ExportFormat::CSV->value);                 // csv | json | xlsx
            $table->string('status')->default(ExportStatus::PENDING->value); // pending | processing | completed | failed
            $table->foreignId('media_id')->nullable()->constrained('media')->nullOnDelete();
            $table->json('filters')->nullable();      // dynamic scope filters applied at export time
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exports');
    }
};
