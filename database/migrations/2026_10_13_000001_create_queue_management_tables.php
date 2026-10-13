<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patients', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone')->index();
            $table->string('nic')->nullable();
            $table->integer('age')->nullable();
            $table->string('gender')->nullable(); // Male, Female, Other
            $table->string('address')->nullable();
            $table->timestamps();
        });

        Schema::create('clinic_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('doctor_id')->constrained('users')->onDelete('cascade');
            $table->date('session_date');
            $table->string('title')->default('General Consultation');
            $table->string('room_number')->nullable();
            $table->string('status')->default('active'); // scheduled, active, paused, completed
            $table->integer('max_tokens')->default(50);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->timestamps();
        });

        Schema::create('queue_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')->constrained('clinic_sessions')->onDelete('cascade');
            $table->foreignId('doctor_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('patient_id')->constrained('patients')->onDelete('cascade');
            $table->integer('token_number');
            $table->string('type')->default('walk_in'); // walk_in, online
            $table->string('status')->default('waiting'); // waiting, calling, serving, on_hold, completed, cancelled
            $table->text('notes')->nullable();
            $table->timestamp('called_at')->nullable();
            $table->timestamp('served_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['session_id', 'status']);
            $table->index(['session_id', 'token_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('queue_tokens');
        Schema::dropIfExists('clinic_sessions');
        Schema::dropIfExists('patients');
    }
};
