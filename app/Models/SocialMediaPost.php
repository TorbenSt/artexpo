<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SocialMediaPost extends Model
{
    protected $fillable = [
        'image_id', 
        'network', 
        'content', 
        'status', 
        'published_at'];

    public function image()
    {
        return $this->belongsTo(Image::class);
    }
}