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
        Schema::create('factura', function (Blueprint $table) {
            $table->id();
            $table->foreignId('idUsuario')
            ->constrained('users')
            ->nullable()
            //quitarlo
            ->cascadeOnUpdate()
            ->cascadeOnDelete();
            //-------------------------
            $table->date('fecha');
            $table->float('total');
            $table->float('subtotal');
            $table->float('descuento');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('factura');
    }
};
