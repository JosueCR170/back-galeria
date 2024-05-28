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
        Schema::create('matriculas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('idUsuario')
            ->constrained('users')
            ->nullable()
            //quitarlo
            ->cascadeOnUpdate()
            ->cascadeOnDelete();

            $table->foreignId('idOferta')
            ->constrained('ofertas')
            ->nullable()
            //quitarlo
            ->cascadeOnUpdate()
            ->cascadeOnDelete();

            $table->date('fechaMatricula');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('matriculas');
    }
};
