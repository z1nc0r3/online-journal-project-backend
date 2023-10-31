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
        Schema::create('final_journal_records', function (Blueprint $table) {
            $table->id();
            $table->int('trainee_id', 10);
            $table->int('supervisor_id', 10);
            $table->int('evaluator_id', 10);
            $table->string('record', 1000);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('final_journal_records');
    }

    
};
