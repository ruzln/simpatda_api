<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pegawai extends Model

    {
        use HasFactory;
    
        protected $table = 'USER_APPLICATION';
       
        // Tambahkan atribut ini jika Anda menggunakan Firebird
        protected $connection = 'firebird';
    }

