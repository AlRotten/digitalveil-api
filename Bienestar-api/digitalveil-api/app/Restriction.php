<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Restriction extends Model
{
    protected $table = 'restrictions';
    protected $fillable = ['max_time','start_hour_restriction','finish_hour_restriction','user_id','application_id'];
}