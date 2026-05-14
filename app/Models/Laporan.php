<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class Laporan extends Model
{
    use HasFactory;

    protected $table = 'laporan';
    protected $primaryKey = 'id_laporan';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'periode',
        'total_penerimaan',
    ];

    public function getKeyName(): string
    {
        return Schema::hasColumn($this->getTable(), 'id_laporan') ? 'id_laporan' : 'id';
    }
}
