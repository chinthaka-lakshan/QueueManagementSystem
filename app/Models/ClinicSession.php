<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClinicSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'doctor_id',
        'session_date',
        'title',
        'room_number',
        'status',
        'max_tokens',
        'started_at',
        'ended_at',
    ];

    protected $casts = [
        'session_date' => 'date',
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'max_tokens' => 'integer',
    ];

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    public function tokens(): HasMany
    {
        return $this->hasMany(QueueToken::class, 'session_id')->orderBy('token_number');
    }

    public function currentServingToken()
    {
        return $this->hasOne(QueueToken::class, 'session_id')
            ->whereIn('status', ['calling', 'serving'])
            ->latest('called_at');
    }
}
