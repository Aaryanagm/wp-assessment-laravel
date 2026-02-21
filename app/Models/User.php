<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    protected $table = 'wp_users';
    protected $primaryKey = 'ID';
    public $timestamps = false;

    protected $fillable = [
        'user_login',
        'user_email',
        'user_pass',
    ];

    protected $hidden = [
        'user_pass',
    ];

    // JWT Required
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    public function meta()
    {
        return $this->hasMany(UserMeta::class, 'user_id', 'ID');
    }

    // public function getRoleAttribute()
    // {
    //     $roleMeta = \DB::table('wp_usermeta')
    //         ->where('user_id', $this->ID)
    //         ->where('meta_key', 'wp_capabilities')
    //         ->first();

    //     if (!$roleMeta) {
    //         return null;
    //     }

    //     $capabilities = unserialize($roleMeta->meta_value);
    //     $role = array_key_first($capabilities);

    //     // Normalize roles
    //     $allowedRoles = ['customer', 'silver', 'gold', 'administrator'];

    //     return in_array($role, $allowedRoles) ? $role : 'customer';
    // }

    public function orders()
    {
        return $this->hasManyThrough(
            WpPost::class,
            WpPostMeta::class,
            'meta_value', // foreign key on postmeta (customer id)
            'ID',         // foreign key on posts
            'ID',         // local key on users
            'post_id'     // local key on postmeta
        )->where('meta_key', '_customer_user')
        ->where('post_type', 'shop_order');
    }

    public function getRoleAttribute()
    {
        $meta = \DB::table('wp_usermeta')
            ->where('user_id', $this->ID)
            ->where('meta_key', 'wp_capabilities')
            ->first();

        if (!$meta) return null;

        $capabilities = unserialize($meta->meta_value);

        return array_key_first($capabilities);
    }
}