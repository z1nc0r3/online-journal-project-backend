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
        Schema::create('journal_records', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('trainee_id');
            $table->unsignedBigInteger('supervisor_id');
            $table->unsignedBigInteger('evaluator_id');
            $table->string('description', 2500);
            $table->string('solutions', 2500);
            $table->string('week', 1);
            $table->string('month', 2);
            $table->string('year', 4);
            $table->tinyInteger('approved')->default(0);
            $table->timestamps();

            $table->foreign('trainee_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('journal_records');
    }
};
