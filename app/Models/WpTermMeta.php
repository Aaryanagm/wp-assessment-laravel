<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WpTermMeta extends Model
{
    protected $table = 'wp_termmeta';
    protected $primaryKey = 'meta_id';
    public $timestamps = false;

    protected $fillable = [
        'term_id',
        'meta_key',
        'meta_value'
    ];
}