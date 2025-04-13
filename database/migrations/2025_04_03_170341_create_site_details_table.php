<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('site_details', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->text('name');
            $table->text('email');
            $table->text('phone');
            $table->text('address');
            $table->text('fb');
            $table->text('tw');
            $table->text('wh');
            $table->text('ig');
        });
    }

    public function down() {
        Schema::dropIfExists('site_details');
    }
};

