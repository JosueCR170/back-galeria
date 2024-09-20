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
        Schema::create('detalles_factura', function (Blueprint $table) {
            $table->id();

            $table->foreignId('idFactura')
            ->constrained('facturas')
            ->nullable()
            ->cascadeOnUpdate()
            ->cascadeOnDelete();

            $table->foreignId('idObra')
            ->constrained('obras')
            ->nullable()
            ->cascadeOnUpdate()
            ->cascadeOnDelete();

            $table->float('subtotal');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detalles_factura');
    }
};