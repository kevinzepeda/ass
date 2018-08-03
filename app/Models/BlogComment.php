<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Venturecraft\Revisionable\RevisionableTrait;

class BlogComment extends Model
{
    use SoftDeletes,RevisionableTrait;

    protected $guarded = ['id'];
    protected $table = 'blog_comments';
    protected $dates = ['deleted_at'];
}
