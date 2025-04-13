<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration {
    public function up() {
        Schema::create('listings', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->text('name');
            $table->text('slug');
            $table->text('desc');
            $table->decimal('price', 10, 2);
            $table->decimal('highest_bid', 10, 2)->default(0.00);
            $table->integer('bids_count')->default(0);
            $table->timestamp('closing_date');
            $table->boolean('active')->default(true);
            $table->foreignId('auctioneer_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->foreignId('category_id')->nullable()->constrained('categories')->onDelete('set null');
        });
    }

    public function down() {
        Schema::dropIfExists('listings');
    }
};
