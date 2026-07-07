<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Achievement extends Model
{
    protected $table = 'achievements';

    protected $fillable = [
        'athlete_id',
        'nama_kejuaraan',
        'tingkat',
        'lokasi',
        'tanggal',
        'hasil',
        'medali',
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];

    public function athlete(): BelongsTo
    {
        return $this->belongsTo(Athlete::class, 'athlete_id');
    }
}
