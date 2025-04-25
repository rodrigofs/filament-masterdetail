<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('share_sharer', function (Blueprint $table) {
            $table->foreignId('sharer_id')->constrained()->onDelete('cascade');
            $table->foreignId('share_id')->constrained()->onDelete('cascade');
            $table->integer('likes')->default(0);
            $table->timestamps();

            $table->primary(['sharer_id', 'share_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('share_sharer');
    }
};
