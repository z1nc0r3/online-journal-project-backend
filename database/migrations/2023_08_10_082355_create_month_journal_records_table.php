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
        Schema::create('month_journal_records', function (Blueprint $table) {
            $table->id();
            $table->int('trainee_id', 10);
            $table->int('supervisor_id', 10);
            $table->int('evaluator_id', 10);
            $table->string('records', 1000);
            $table->integer('number_of_leave', 2);
            $table->string('month', 2);
            $table->string('year', 4);
            $table->tinyInteger('approved')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('month_journal_records');
    }
};
