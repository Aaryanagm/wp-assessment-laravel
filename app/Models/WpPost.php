<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WpPost extends Model
{
    protected $table = 'wp_posts';
    protected $primaryKey = 'ID';
    public $timestamps = false;

    protected $fillable = [
        'post_title',
        'post_content',
        'post_type',
        'post_status',
        'post_parent',
        'post_author'
    ];

    public function meta()
    {
        return $this->hasMany(WpPostMeta::class, 'post_id', 'ID');
    }

    public function variations()
    {
        return $this->hasMany(WpPost::class, 'post_parent', 'ID')
            ->where('post_type', 'product_variation');
    }

    public function termRelationships()
    {
        return $this->hasMany(WpTermRelationship::class, 'object_id', 'ID');
    }

    public function orders()
    {
        return $this->where('post_type', 'shop_order');
    }
}