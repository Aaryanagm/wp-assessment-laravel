<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WpTermTaxonomy extends Model
{
    protected $table = 'wp_term_taxonomy';
    protected $primaryKey = 'term_taxonomy_id';
    public $timestamps = false;

    public function term()
    {
        return $this->belongsTo(WpTerm::class, 'term_id', 'term_id');
    }

    public function relationships()
    {
        return $this->hasMany(WpTermRelationship::class, 'term_taxonomy_id');
    }
}