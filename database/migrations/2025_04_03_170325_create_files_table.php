<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('files', function (Blueprint $table) {
            $table->id();
            $table->string('resource_type');
            $table->timestamps();
            $table->unsignedBigInteger('listing_id');
            $table->foreign('listing_id')->references('id')->on('listings')->onDelete('cascade');        });
    }

    public function down() {
        Schema::dropIfExists('files');
    }
};
