<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WajibPajak extends Model
{
    use HasFactory;

    protected $table = 'wajib_pajak';
    protected $primaryKey = 'id_wp';

    protected $fillable = [
        'nama_wp',
        'alamat',
        'no_ktp',
    ];

    public function objekPajak(): HasMany
    {
        return $this->hasMany(ObjekPajak::class, 'id_wp', 'id_wp');
    }
}
