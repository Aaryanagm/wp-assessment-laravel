<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WpTermRelationship extends Model
{
    protected $table = 'wp_term_relationships';
    public $timestamps = false;
    protected $primaryKey = null;
    public $incrementing = false;

    public function taxonomy()
    {
        return $this->belongsTo(WpTermTaxonomy::class, 'term_taxonomy_id');
    }

    public function post()
    {
        return $this->belongsTo(WpPost::class, 'object_id', 'ID');
    }
}