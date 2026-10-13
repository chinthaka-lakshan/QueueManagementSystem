<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QueueToken extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'doctor_id',
        'patient_id',
        'token_number',
        'type', // 'walk_in', 'online'
        'status', // 'waiting', 'calling', 'serving', 'on_hold', 'completed', 'cancelled'
        'notes',
        'called_at',
        'served_at',
        'completed_at',
    ];

    protected $casts = [
        'token_number' => 'integer',
        'called_at' => 'datetime',
        'served_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function session(): BelongsTo
    {
        return $this->belongsTo(ClinicSession::class, 'session_id');
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }
}
