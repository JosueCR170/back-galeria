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
        Schema::create('obras', function (Blueprint $table) {
            $table->id();
            $table->foreignId('idArtista')
            ->constrained('artista')
            ->nullable()
            //quitarlo
            ->cascadeOnUpdate()
            ->cascadeOnDelete();
            $table->string('tecnica');
            $table->string('nombre');
            $table->string('tamano');
            $table->float('precio');
            $table->boolean('disponibilidad');
            $table->string('categoria');
            $table->string('imagen');
            $table->date('fechaCreacion');
            $table->date('fechaRegistro');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('obras');
    }
};
