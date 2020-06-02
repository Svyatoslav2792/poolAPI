<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Rule extends Model
{
    protected $guarded = [];
    public function value() {
        return $this->hasMany('App\RuleValues', 'rule_id', 'id');
    }
}
