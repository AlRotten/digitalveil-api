<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Usage extends Model
{
    protected $table = 'usage';
    protected $fillable = ['day','use_time','location','user_id','application_id'];
}