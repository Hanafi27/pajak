<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ObjekPajak extends Model
{
    use HasFactory;

    protected $table = 'objek_pajak';
    protected $primaryKey = 'id';

    protected $fillable = [
        'id_wp',
        'nop',
        'lokasi',
        'luas_tanah',
        'luas_bangunan',
    ];

    public function wajibPajak(): BelongsTo
    {
        return $this->belongsTo(WajibPajak::class, 'id_wp', 'id_wp');
    }

    public function pbb(): HasMany
    {
        return $this->hasMany(Pbb::class, 'id_objek', 'id');
    }
}
