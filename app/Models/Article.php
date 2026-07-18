<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    protected $table = 'articles';

    protected $fillable = [
        'title',
        'title_id',
        'category',
        'author',
        'summary',
        'summary_id',
        'content',
        'image',
        'image_url',
        'source',
        'url',
        'published_at',
        'country_id',
    ];

    protected $casts = [
        'published_at' => 'datetime',
    ];

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    /**
     * Accessor to guarantee fallback for image_url if image is set
     */
    public function getImageUrlAttribute($value)
    {
        return $value ?: $this->image;
    }

    /**
     * Get category-specific backup image
     */
    public function getCategoryBackupImageAttribute()
    {
        $backupImages = [
            'Ekonomi' => 'https://images.unsplash.com/photo-1611974789855-9c2a0a7236a3?w=800',
            'Politik' => 'https://images.unsplash.com/photo-1529107386315-e1a2ed48a620?w=800',
            'Perdagangan' => 'https://images.unsplash.com/photo-1454165804606-c3d57bc86b40?w=800',
            'Logistik' => 'https://images.unsplash.com/photo-1586528116311-ad8dd3c8310d?w=800',
            'Pelabuhan' => 'https://images.unsplash.com/photo-1494412574643-ff11b0a5c1c3?w=800',
            'Bencana' => 'https://images.unsplash.com/photo-1587406913117-893d5b0c9a6a?w=800',
            'Teknologi' => 'https://images.unsplash.com/photo-1518770660439-4636190af475?w=800',
            'Cuaca' => 'https://images.unsplash.com/photo-1592210454359-9043f067919b?w=800',
            'Transportasi' => 'https://images.unsplash.com/photo-1544620347-c4fd4a3d5957?w=800',
        ];

        return $backupImages[$this->category] ?? 'https://images.unsplash.com/photo-1504711434969-e33886168f5c?w=800';
    }
}