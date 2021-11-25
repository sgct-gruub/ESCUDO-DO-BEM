<?php

namespace App\Models;


use  Illuminate\Database\Eloquent\Model;

class Mensagens extends Model
{
    protected $table = 'mensagens';
    protected $fillable = ['remetente', 'destinatario', 'mensagem', 'data_leitura', 'status'];
    public $timestamps = true;
}
