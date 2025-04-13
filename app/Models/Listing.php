<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Listing extends Model
{
    use HasFactory;

    protected $fillable = [
        'auctioneer_id',
        'name',
        'slug',
        'desc',
        'category_id',
        'price',
        'highest_bid',
        'bids_count',
        'closing_date',
        'active',
    ];

    public function auctioneer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'auctioneer_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function bids(): HasMany
    {
        return $this->hasMany(Bid::class);
    }

    public function watchlists(): HasMany
    {
        return $this->hasMany(Watchlist::class);
    }

    public function images()
    {
        return $this->hasMany(File::class);
    }
}
