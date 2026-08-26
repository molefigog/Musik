<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('service_type', ['beat', 'recording', 'artwork']);
            $table->string('title');
            $table->text('details')->nullable();
            $table->boolean('status')->default(false); // false = processing, true = completed
            $table->string('file_path')->nullable();    // final deliverable
            $table->string('preview_path')->nullable(); // optional preview
            $table->timestamps();

            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
