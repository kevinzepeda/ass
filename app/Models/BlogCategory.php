<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Venturecraft\Revisionable\RevisionableTrait;

class BlogCategory extends Model
{
    use SoftDeletes,RevisionableTrait;

    protected $guarded = ['id'];
    protected $table = 'blog_categories';
    protected $dates = ['deleted_at'];

    public function blog()
    {
        return $this->hasMany(Blog::class);
    }
}
