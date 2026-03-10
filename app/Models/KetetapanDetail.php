<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KetetapanDetail extends Model
{   protected $connection = 'firebird';
    protected $table = 'KETETAPAN_DETAIL';
    public function ketetapan()
    {
        return $this->belongsTo(Ketetapan::class, 'ID_KETETAPAN');
    }
}
