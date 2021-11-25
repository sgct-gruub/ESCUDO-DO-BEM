<?php

namespace App\Models\Doacoes;


use  Illuminate\Database\Eloquent\Model;

class Charges extends Model
{
    protected $table = 'doacoes_charges';
    protected $fillable = ['doador', 'amount', 'paymentTypes', 'chargeId', 'creditCardId', 'chargeStatus', 'paymentStatus'];
    public $timestamps = true;
}
