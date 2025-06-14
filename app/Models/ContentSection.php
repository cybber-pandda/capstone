<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContentSection extends Model
{
    use HasFactory;

    protected $table = 'content_sections';

    protected $fillable = [
        'frontmenu_id',
        'layout_type',
        'object_type',
        'isImage',
        'isIcon',
        'title',
        'content',
        'object_position'
    ];

    public function frontMenu()
    {
        return $this->belongsTo(FrontMenu::class, 'frontmenu_id');
    }

 
}
