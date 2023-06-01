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
            $table->string('role');
            $table->string('fName');
            $table->string('regno')->nullable();
            $table->string('department')->nullable();
            $table->string('address')->nullable();
            $table->string('email');
            $table->integer('phone')->nullable();
            $table->string('estName')->nullable();
            $table->string('estAddress')->nullable();
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
