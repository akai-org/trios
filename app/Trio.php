<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class Trio extends Model
{

    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'sentence1',
        'sentence2',
        'sentence3',
        'explanation1',
        'explanation2',
        'explanation3',
        'answer'
    ];
}
