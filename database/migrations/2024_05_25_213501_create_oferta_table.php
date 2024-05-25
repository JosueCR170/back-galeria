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
        Schema::create('oferta', function (Blueprint $table) {
            $table->id();
            $table->foreignId('idArtista')
            ->constrained('artista')
            ->nullable()
            //quitarlo
            ->cascadeOnUpdate()
            ->cascadeOnDelete();

            $table->foreignId('idTaller')
            ->constrained('taller')
            ->nullable()
            //quitarlo
            ->cascadeOnUpdate()
            ->cascadeOnDelete();

            $table->date('fechaInicio');
            $table->date('fechaFinal');
            $table->float('costo');
            $table->string('ubicacion');
            $table->string('modalidad');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('oferta');
    }
};
