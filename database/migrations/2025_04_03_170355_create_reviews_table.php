<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->boolean('show')->default(false);
            $table->text('text');
            $table->foreignId('reviewer_id')->nullable()->constrained('users')->onDelete('cascade');
        });
    }

    public function down() {
        Schema::dropIfExists('reviews');
    }
};

