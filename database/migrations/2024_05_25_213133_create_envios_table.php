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
        Schema::create('envios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('idFactura')
            ->constrained('factura')
            ->nullable()
            //quitarlo
            ->cascadeOnUpdate()
            ->cascadeOnDelete();
            $table->string('estado');
            $table->date('fechaEnviado');
            $table->date('fechaRecibido');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('envios');
    }
};
