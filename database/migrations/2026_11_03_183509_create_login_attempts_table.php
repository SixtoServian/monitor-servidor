<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('login_attempts', function (Blueprint $table) {
            $table->id();
            $table->string('identifier')->nullable(); // Email o usuario
            $table->string('ip_address', 45); // IPv4 o IPv6
            $table->string('status', 20); // success, failed, blocked
            $table->string('user_agent', 255)->nullable();
            $table->timestamp('created_at')->useCurrent();
            
            // Índice para acelerar las búsquedas de seguridad
            $table->index(['ip_address', 'status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('login_attempts');
    }
};