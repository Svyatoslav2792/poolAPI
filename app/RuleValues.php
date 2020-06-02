<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RuleValues extends Model
{
    protected $guarded = [];
    public function postback() {
        return $this->hasMany('App\Postback','rule_values_id','id');
    }
}
