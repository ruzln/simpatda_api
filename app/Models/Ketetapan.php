<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ketetapan extends Model
{   protected $connection = 'firebird';
    protected $table = 'KETETAPAN';
    public function detail()
    {
        return $this->hasMany(KetetapanDetail::class, 'ID_KETETAPAN');
    }
}
