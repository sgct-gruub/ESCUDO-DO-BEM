<?php

namespace App\Models;


use  Illuminate\Database\Eloquent\Model;

class Snapshots extends Model
{
    protected $table = 'snapshots';
    protected $fillable = ['acolhido', 'imagem', 'status'];
    public $timestamps = false;
}
