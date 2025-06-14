<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FrontMenu extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'frontmenu';

    protected $fillable = [
        'name',
        'url',
        'banner_title',
        'banner_sub_title',
        'banner_photo'
    ];


    public function contentSections()
    {
        return $this->hasMany(ContentSection::class, 'frontmenu_id');
    }
}
