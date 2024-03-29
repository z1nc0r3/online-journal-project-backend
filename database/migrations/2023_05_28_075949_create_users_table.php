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
            $table->string('role', 15);
            $table->string('fName', 40);
            $table->string('regno')->nullable();
            $table->string('department')->nullable();
            $table->string('address', 60)->nullable();
            $table->string('email', 50)->unique();
            $table->string('phone', 10)->nullable();
            $table->string('estName')->nullable();
            $table->string('estAddress', 60)->nullable();
            $table->date('startDate')->nullable();
            $table->integer('duration')->nullable();
            $table->string('password');
            $table->timestamps();
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
