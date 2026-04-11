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
        Schema::create('boletines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plaza_id')->constrained('plazas')->onDelete('cascade');
            $table->date('fecha_plaza');
            $table->decimal('tipo_cambio_usd', 10, 2)->nullable();
            $table->string('archivo_pdf')->nullable();
            $table->timestamps();

            $table->unique(['plaza_id', 'fecha_plaza']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('boletines');
    }
};
