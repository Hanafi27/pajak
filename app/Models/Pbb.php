<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pbb extends Model
{
    use HasFactory;

    protected $table = 'pbb';
    protected $primaryKey = 'id';

    protected $fillable = [
        'id_objek',
        'njop',
        'tarif',
        'total_pajak',
        'tahun',
    ];

    public function objekPajak(): BelongsTo
    {
        return $this->belongsTo(ObjekPajak::class, 'id_objek', 'id');
    }
}
