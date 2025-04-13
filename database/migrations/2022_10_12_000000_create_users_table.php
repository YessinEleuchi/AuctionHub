<?php

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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->unique();
            $table->string('password');
            $table->boolean('is_email_verified')->default(false);
            $table->boolean('is_superuser')->default(false);
            $table->boolean('is_staff')->default(false);
            $table->boolean('terms_agreement')->default(false);
            $table->text('access')->nullable();
            $table->text('refresh')->nullable();
            $table->foreignId('avatar_id')->nullable()->constrained('avatars')->onDelete('set null')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
